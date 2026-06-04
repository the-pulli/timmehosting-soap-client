<?php

use Pulli\TimmeSoapClient\Client;
use Pulli\TimmeSoapClient\Enums\BackupAction;
use Pulli\TimmeSoapClient\Enums\CronType;
use Pulli\TimmeSoapClient\Enums\CrudOp;
use Pulli\TimmeSoapClient\Enums\DnsRecordType;
use Pulli\TimmeSoapClient\Enums\Params\ChrootMode;
use Pulli\TimmeSoapClient\Enums\Params\DatabaseType;
use Pulli\TimmeSoapClient\Enums\Params\DnsZoneType;
use Pulli\TimmeSoapClient\Enums\Params\IpType;
use Pulli\TimmeSoapClient\Enums\Params\PhpHandler;
use Pulli\TimmeSoapClient\Enums\Params\SslAction;
use Pulli\TimmeSoapClient\Enums\Params\VhostType;
use Pulli\TimmeSoapClient\Enums\Params\WebSubdomain;
use Pulli\TimmeSoapClient\Enums\Status;
use Pulli\TimmeSoapClient\Enums\Toggle;
use Pulli\TimmeSoapClient\Exception;

/**
 * SOAP test-double: every called method is intercepted by __call and
 * recorded. login() returns a fixed session id so the under-test Client
 * goes past the empty-session guard without an actual network round trip.
 */
function makeSoap(array &$calls, mixed $login = 'sid-fake'): SoapClient
{
    return new class($calls, $login) extends SoapClient
    {
        public function __construct(public array &$calls, public mixed $loginReturn)
        {
            // intentionally not calling parent::__construct() — we never make a real SOAP call
        }

        public function login($user, $password): mixed
        {
            $this->calls[] = ['login', [$user, $password]];

            return $this->loginReturn;
        }

        public function logout($session): mixed
        {
            $this->calls[] = ['logout', [$session]];

            return true;
        }

        public function __call($name, $args): mixed
        {
            $this->calls[] = [$name, $args];

            // Default return shape per function — extend as tests grow
            return match ($name) {
                'server_restart_php' => true,
                'server_get_php_versions' => [['server_php_id' => 11, 'name' => 'PHP 8.5']],
                'sites_web_domain_get' => ['domain_id' => 5, 'domain' => 'example.com', 'web_folder' => 'old/public'],
                'sites_web_domain_update' => true,
                'sites_cron_get' => [['cron_id' => 1, 'command' => '/bin/true']],
                'sites_cron_add' => 42,
                'sites_supervisor_get' => [['job_id' => 9]],
                'sites_supervisor_restart' => true,
                'client_get' => ['client_id' => 2, 'username' => 'pulli_main'],
                'client_get_groupid' => 3,
                'client_get_by_username' => ['client_id' => 2, 'username' => 'pulli_main'],
                default => null,
            };
        }
    };
}

function makeClient(SoapClient $soap): Client
{
    return new Client(
        panel: 's00q00',
        apiUser: 'remote',
        apiKey: 'secret',
        verifyTls: true,
        soap: $soap,
    );
}

it('logs in lazily on the first SOAP call and reuses the session', function () {
    $calls = [];
    $soap = makeSoap($calls);
    $client = makeClient($soap);

    $client->php->versions(1);
    $client->clients->find(2);

    expect($calls[0][0])->toBe('login')
        ->and(array_column($calls, 0))->toBe(['login', 'server_get_php_versions', 'client_get']);
});

it('throws when login returns an empty session id', function () {
    $calls = [];
    $soap = makeSoap($calls, login: '');
    $client = makeClient($soap);

    expect(fn () => $client->clients->find(2))
        ->toThrow(Exception::class, 'empty session id');
});

it('restartPhp throws when the server returns falsy', function () {
    $calls = [];
    $soap = new class($calls) extends SoapClient
    {
        public function __construct(public array &$calls)
        {
            // no parent::__construct — local-only stub
        }

        public function login($u, $p): string
        {
            return 'sid';
        }

        public function logout($s): bool
        {
            return true;
        }

        public function __call($name, $args): mixed
        {
            $this->calls[] = [$name, $args];

            return false;
        }
    };
    $client = makeClient($soap);

    expect(fn () => $client->php->restart(1, 11))
        ->toThrow(Exception::class, 'server_restart_php returned falsy');
});

it('setWebFolder is idempotent — returns false when value matches', function () {
    $calls = [];
    $soap = new class($calls) extends SoapClient
    {
        public function __construct(public array &$calls)
        {
            // no parent::__construct
        }

        public function login($u, $p): string
        {
            return 'sid';
        }

        public function logout($s): bool
        {
            return true;
        }

        public function __call($name, $args): mixed
        {
            $this->calls[] = [$name, $args];

            return match ($name) {
                'sites_web_domain_get' => ['domain_id' => 5, 'web_folder' => 'myapp/current/public'],
                default => null,
            };
        }
    };
    $client = makeClient($soap);

    $changed = $client->sites->setWebFolder(2, 5, 'myapp/current/public');

    expect($changed)->toBeFalse()
        ->and(array_column($calls, 0))->not->toContain('sites_web_domain_update');
});

it('setWebFolder writes when value differs and strips sys_* fields', function () {
    $calls = [];
    $soap = new class($calls) extends SoapClient
    {
        public function __construct(public array &$calls)
        {
            // no parent::__construct
        }

        public function login($u, $p): string
        {
            return 'sid';
        }

        public function logout($s): bool
        {
            return true;
        }

        public function __call($name, $args): mixed
        {
            $this->calls[] = [$name, $args];

            return match ($name) {
                'sites_web_domain_get' => [
                    'domain_id' => 5,
                    'web_folder' => 'old/public',
                    'sys_userid' => 1,
                    'sys_groupid' => 1,
                    'sys_perm_user' => 'riud',
                    'sys_perm_group' => 'riud',
                    'sys_perm_other' => '',
                ],
                'client_get_groupid' => 3,
                'sites_web_domain_update' => true,
                default => null,
            };
        }
    };
    $client = makeClient($soap);

    $changed = $client->sites->setWebFolder(2, 5, 'myapp/current/public');

    $update = null;
    foreach ($calls as $call) {
        if ($call[0] === 'sites_web_domain_update') {
            $update = $call;
            break;
        }
    }
    $sentSite = $update[1][3]; // session, clientId, domainId, $params

    expect($changed)->toBeTrue()
        ->and($sentSite)->toHaveKey('web_folder', 'myapp/current/public')
        ->and($sentSite)->toHaveKey('client_group_id', 3)
        ->and($sentSite)->not->toHaveKey('sys_userid')
        ->and($sentSite)->not->toHaveKey('sys_perm_user');
});

it('generic call() forwards to any SOAP function with session injected', function () {
    $calls = [];
    $soap = makeSoap($calls);
    $client = makeClient($soap);

    $client->call('dns_zone_add', 7, ['origin' => 'example.com.']);

    [$name, $args] = end($calls);
    expect($name)->toBe('dns_zone_add')
        ->and($args)->toBe(['sid-fake', 7, ['origin' => 'example.com.']]);
});

it('cron->add passes the CronType + Toggle enum values to ISPConfig', function () {
    $calls = [];
    $soap = makeSoap($calls);
    $client = makeClient($soap);

    $client->cron->add(
        clientId: 2,
        domainId: 5,
        command: '/bin/true',
        type: CronType::Chrooted,
        active: Toggle::No,
    );

    [$name, $args] = end($calls);
    expect($name)->toBe('sites_cron_add')
        ->and($args[2]['type'])->toBe('chrooted')
        ->and($args[2]['active'])->toBe('n');
});

it('dns->record(type, op, ...) composes the correct SOAP function name', function () {
    $calls = [];
    $soap = makeSoap($calls);
    $client = makeClient($soap);

    $client->dns->record(DnsRecordType::Srv, CrudOp::Add, 7, ['name' => '_imaps._tcp']);

    [$name, $args] = end($calls);
    expect($name)->toBe('dns_srv_add')
        ->and($args)->toBe(['sid-fake', 7, ['name' => '_imaps._tcp']]);
});

it('Toggle::fromBool maps true/false to y/n', function () {
    expect(Toggle::fromBool(true))->toBe(Toggle::Yes)
        ->and(Toggle::fromBool(false))->toBe(Toggle::No)
        ->and(Toggle::Yes->value)->toBe('y')
        ->and(Toggle::No->value)->toBe('n');
});

it('Status::fromBool maps true/false to active/inactive', function () {
    expect(Status::fromBool(true))->toBe(Status::Active)
        ->and(Status::fromBool(false))->toBe(Status::Inactive)
        ->and(Status::Active->value)->toBe('active')
        ->and(Status::Inactive->value)->toBe('inactive');
});

it('sites->setStatus passes the Status enum value to ISPConfig', function () {
    $calls = [];
    $soap = makeSoap($calls);
    $client = makeClient($soap);

    $client->sites->setStatus(5, Status::Inactive);

    [$name, $args] = end($calls);
    expect($name)->toBe('sites_web_domain_set_status')
        ->and($args[1])->toBe(5)
        ->and($args[2])->toBe('inactive');
});

it('sites->backup passes the BackupAction enum value to ISPConfig', function () {
    $calls = [];
    $soap = makeSoap($calls);
    $client = makeClient($soap);

    $client->sites->backup(99, BackupAction::BackupRestore);

    [$name, $args] = end($calls);
    expect($name)->toBe('sites_web_domain_backup')
        ->and($args[1])->toBe(99)
        ->and($args[2])->toBe('backup_restore');
});

it('sites->backupDownloadLink is a shortcut that calls sites_web_domain_backup with the right action', function () {
    $calls = [];
    $soap = makeSoap($calls);
    $client = makeClient($soap);

    $client->sites->backupDownloadLink(42);

    [$name, $args] = end($calls);
    expect($name)->toBe('sites_web_domain_backup')
        ->and($args[2])->toBe('backup_download_link');
});

it('params enums expose the literal ISPConfig magic-string values', function () {
    expect(IpType::IPv4->value)->toBe('IPv4')
        ->and(IpType::IPv6->value)->toBe('IPv6')
        ->and(VhostType::Vhost->value)->toBe('vhost')
        ->and(VhostType::VhostSubdomain->value)->toBe('vhostsubdomain')
        ->and(WebSubdomain::None->value)->toBe('none')
        ->and(WebSubdomain::Wildcard->value)->toBe('*')
        ->and(PhpHandler::PhpFpm->value)->toBe('php-fpm')
        ->and(PhpHandler::Disabled->value)->toBe('no')
        ->and(DatabaseType::Mysql->value)->toBe('mysql')
        ->and(DatabaseType::Postgresql->value)->toBe('postgresql')
        ->and(ChrootMode::Jailkit->value)->toBe('jailkit')
        ->and(ChrootMode::None->value)->toBe('no')
        ->and(DnsZoneType::Master->value)->toBe('MASTER')
        ->and(DnsZoneType::Slave->value)->toBe('SLAVE')
        ->and(SslAction::Create->value)->toBe('create')
        ->and(SslAction::Delete->value)->toBe('del');
});
