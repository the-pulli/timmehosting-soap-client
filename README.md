# ISPConfig SOAP Remote API SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pulli/timmehosting-soap-client.svg?style=flat-square)](https://packagist.org/packages/pulli/timmehosting-soap-client)
[![Tests](https://github.com/the-pulli/timmehosting-soap-client/actions/workflows/run-tests.yml/badge.svg)](https://github.com/the-pulli/timmehosting-soap-client/actions/workflows/run-tests.yml)

Thin, typed PHP SDK for the ISPConfig Remote API (SOAP). Covers every
endpoint [Timme Hosting documents](https://timmehosting.de/ispconfig-api-schnittstelle-zur-automatisierung)
— 236 SOAP functions across 14 resource groups — with a generic escape
hatch for anything beyond.

- One SOAP session per `Client`, login lazily on first call, logout on
  destruct — no session juggling in user code
- Functions grouped by resource: `$client->sites`, `$client->dns`,
  `$client->mail`, `$client->php`, `$client->cron`, `$client->server`, …
- Backed enums for ISPConfig's magic-string fields (`y`/`n`, `active`/
  `inactive`, cron type, DNS record type, backup actions)
- Generic `$client->call('soap_function_name', …)` for anything not yet
  wrapped — session id is injected automatically
- TLS verification on by default; flip per-instance for self-signed panels
- `\SoapClient` is injectable for tests — see `tests/ClientTest.php`
- Reference snapshot of every Timme example script archived in
  [`reference/timme-ispconfig-api/`](reference/timme-ispconfig-api/) so
  the canonical parameter shapes survive even if Timme reorganises

## Installation

```bash
composer require pulli/timmehosting-soap-client
```

Requires PHP 8.4+ and `ext-soap`.

## Usage

```php
use Pulli\TimmeSoapClient\Client;
use Pulli\TimmeSoapClient\Enums\{
    Toggle, Status, CronType, DnsRecordType, CrudOp, BackupAction,
};

// Construct explicitly:
$client = new Client(
    panel:     'sXXqXX',          // panel subdomain: sXXqXX.meinserver.io
    apiUser:   'pulli_server',    // ISPConfig System → Remote Users
    apiKey:    $remoteUserSecret,
    verifyTls: true,
);

// Or from env vars (TIMME_SOAP_PANEL, TIMME_SOAP_USER, TIMME_SOAP_KEY,
// optional TIMME_SOAP_VERIFY_TLS=0):
$client = Client::fromEnv();

// PHP / FPM
$client->php->restart(serverId: 1, serverPhpId: 11);
$client->php->versions(serverId: 1);

// Sites — vhosts
$site = $client->sites->findByDomain('example.com');
$client->sites->setWebFolder($clientId, $domainId, 'myapp/current/public');
$client->sites->setNginxDirectives($clientId, $domainId, $directives);
$client->sites->setStatus($domainId, Status::Inactive);
$client->sites->recreateVhost($domainId);

// Sites — backups
$backups = $client->sites->backupList($domainId);
$url     = $client->sites->backupDownloadLink($backups[0]['backup_id']);
$client->sites->instantBackup(serverId: 1, domainId: $domainId);

// Clients
$client->clients->findByUsername('pulli_main');
$gid = $client->clients->groupId($clientId);
$client->clients->changePassword($clientId, $newSecret);

// Cron — type-safe enums
$client->cron->add(
    clientId: 2,
    domainId: 5,
    command:  '/opt/php-8.5/bin/php /var/www/.../artisan schedule:run',
    type:     CronType::Full,     // Url | Full | Chrooted
    active:   Toggle::Yes,
);

// Supervisor (jobs must be created in the panel first; this restarts them)
$client->supervisor->restart($jobId);

// DNS — common record types typed; long-tail via record(type, op, …)
$client->dns->addA($clientId, [...]);
$client->dns->record(DnsRecordType::Srv, CrudOp::Add, $clientId, [...]);
$client->dns->setZoneStatus($zoneId, Status::Active);

// Mail
$client->mail->addDomain($clientId, [...]);
$client->mail->setDomainStatus($domainId, Status::Active);
$client->mail->addUserFilter($clientId, [...]);
$client->mail->userBackupDownloadLink($backupId);

// Anything not yet wrapped — session id is injected automatically:
$client->call('sites_web_aliasdomain_add', $clientId, $params);
```

### Known ISPConfig quirks worth knowing

- DB names are auto-prefixed with `c<client_id>` by ISPConfig — pass the
  suffix you want and read the returned record to learn the final
  full name.
- `sites_redis_add` has the unusual signature `($session, $params)` — no
  `$client_id` argument. The SDK's `$client->sites->addRedis($params)`
  honours this.
- `fastcgi_php_version` is a four-token colon-joined string:
  `<name>:<fpm_init>:<fpm_ini>:<fpm_pool>`.
- `client_update` can only be called by a reseller API user, not a plain
  Remote User.
- `sites_supervisor_add` is **not** exposed via the Remote API —
  supervisor jobs must be created in the panel UI; the SDK restarts
  existing ones.
- `server_f2b_blacklist`/`_whitelist` only expose `add`, `delete`, and
  `_get_all` — no single-record `get` and no `update`.

## Backed enums for the ISPConfig magic-string fields

| Enum | Values | Used by |
|---|---|---|
| `Toggle` | `Yes='y'`, `No='n'` (+ `fromBool()`) | Cron `active` |
| `Status` | `Active='active'`, `Inactive='inactive'` (+ `fromBool()`) | `*_set_status` |
| `CronType` | `Url`, `Full`, `Chrooted` | Cron `type` |
| `DnsRecordType` | `A`, `Aaaa`, `Alias`, `Cname`, `Hinfo`, `Mx`, `Ns`, `Ptr`, `Rp`, `Soa`, `Srv`, `Txt`, `Ds`, `Dnskey`, `Caa`, `Tlsa`, `Sshfp` | `Dns::record(...)` |
| `CrudOp` | `Get`, `Add`, `Update`, `Delete` | `Dns::record(...)` |
| `BackupAction` | `BackupDownload`, `BackupDownloadLink`, `BackupRestore` | `Sites::backup()`, `Mail::userBackup()` |

## Resource coverage

See [CHANGELOG.md](CHANGELOG.md) for the per-resource endpoint list.
Every SOAP function on
<https://timmehosting.de/ispconfig-api-schnittstelle-zur-automatisierung>
has a typed accessor.

## Testing

```bash
composer test
```

The pre-commit hook (`.githooks/pre-commit`, auto-activated by
`composer install`) runs Pint + Pest before every commit.

## License

MIT — see [LICENSE.md](LICENSE.md).
