<?php

namespace Pulli\TimmeSoapClient;

use Pulli\TimmeSoapClient\Resources\Clients;
use Pulli\TimmeSoapClient\Resources\Cron;
use Pulli\TimmeSoapClient\Resources\Database;
use Pulli\TimmeSoapClient\Resources\Dns;
use Pulli\TimmeSoapClient\Resources\Domains;
use Pulli\TimmeSoapClient\Resources\FtpUser;
use Pulli\TimmeSoapClient\Resources\Mail;
use Pulli\TimmeSoapClient\Resources\Monitor;
use Pulli\TimmeSoapClient\Resources\Php;
use Pulli\TimmeSoapClient\Resources\Server;
use Pulli\TimmeSoapClient\Resources\ShellUser;
use Pulli\TimmeSoapClient\Resources\Sites;
use Pulli\TimmeSoapClient\Resources\Supervisor;
use Pulli\TimmeSoapClient\Resources\Utility;
use SensitiveParameter;
use SoapClient;
use SoapFault;

/**
 * SDK client for the ISPConfig SOAP Remote API.
 *
 * Each instance holds one SOAP session — login on first call, logout on
 * destruct. Methods throw on protocol failure (`\SoapFault`) and on
 * semantic failure (`Pulli\TimmeSoapClient\Exception`). TLS verification
 * is ON by default; disable per-instance with $verifyTls=false only when
 * the panel uses a self-signed cert.
 *
 * Construct directly with explicit credentials, or use Client::fromEnv()
 * to read TIMME_SOAP_* env vars.
 *
 * Functions are exposed in two ways:
 *   1. Typed accessors grouped by resource:
 *        $client->sites->find(5);
 *        $client->cron->add(2, 5, '/opt/php-8.5/bin/php …/artisan schedule:run');
 *        $client->php->restart(1, 11);
 *   2. Generic invocation for anything not yet wrapped:
 *        $client->call('dns_zone_add', $clientId, $params);
 *
 * The SoapClient itself is injectable (last constructor arg) so tests
 * can pass a stub.
 *
 * Known ISPConfig API quirks worth knowing when calling functions:
 *   - DB names are auto-prefixed with c<client_id> by ISPConfig
 *   - sites_redis_add signature is ($session, $params) — no $client_id
 *   - fastcgi_php_version format is <name>:<fpm_init>:<fpm_ini>:<fpm_pool>
 *   - client_update can't be called by non-reseller API users
 *   - sites_supervisor_add isn't exposed via the Remote API
 */
class Client
{
    public readonly Sites $sites;

    public readonly Clients $clients;

    public readonly Cron $cron;

    public readonly Database $databases;

    public readonly Dns $dns;

    public readonly Domains $domains;

    public readonly FtpUser $ftpUsers;

    public readonly Mail $mail;

    public readonly Monitor $monitor;

    public readonly Php $php;

    public readonly Server $server;

    public readonly ShellUser $shellUsers;

    public readonly Supervisor $supervisor;

    public readonly Utility $utility;

    private ?string $sessionId = null;

    private readonly SoapClient $soap;

    public function __construct(
        public readonly string $panel,
        public readonly string $apiUser,
        #[SensitiveParameter] public readonly string $apiKey,
        public readonly bool $verifyTls = true,
        ?SoapClient $soap = null,
    ) {
        $this->soap = $soap ?? $this->makeSoapClient();

        $this->sites = new Sites($this);
        $this->clients = new Clients($this);
        $this->cron = new Cron($this);
        $this->databases = new Database($this);
        $this->dns = new Dns($this);
        $this->domains = new Domains($this);
        $this->ftpUsers = new FtpUser($this);
        $this->mail = new Mail($this);
        $this->monitor = new Monitor($this);
        $this->php = new Php($this);
        $this->server = new Server($this);
        $this->shellUsers = new ShellUser($this);
        $this->supervisor = new Supervisor($this);
        $this->utility = new Utility($this);
    }

    /**
     * Build a Client from the surrounding env vars:
     *   TIMME_SOAP_PANEL       sXXqXX (panel subdomain, required)
     *   TIMME_SOAP_USER        SOAP remote user (required)
     *   TIMME_SOAP_KEY         SOAP remote password (required)
     *   TIMME_SOAP_VERIFY_TLS  "0"/"false" to skip TLS verification
     *
     * For Deployer-driven usage (reading set('timme_*') values), define
     * your own factory in your project — keeping this package free of
     * Deployer as a runtime dependency.
     */
    public static function fromEnv(): self
    {
        $panel = getenv('TIMME_SOAP_PANEL') ?: '';
        $user = getenv('TIMME_SOAP_USER') ?: '';
        $key = getenv('TIMME_SOAP_KEY') ?: '';
        if ($panel === '' || $user === '' || $key === '') {
            throw new Exception('TIMME_SOAP_PANEL, TIMME_SOAP_USER, and TIMME_SOAP_KEY env vars are all required.');
        }
        $verify = getenv('TIMME_SOAP_VERIFY_TLS');
        $verifyTls = ! in_array(strtolower((string) $verify), ['0', 'false', 'off', 'no'], true);

        return new self($panel, $user, $key, $verifyTls);
    }

    private function makeSoapClient(): SoapClient
    {
        $sslOpts = $this->verifyTls
            ? []  // PHP defaults — verify peer + name
            : ['verify_peer' => false, 'verify_peer_name' => false];

        return new SoapClient(null, [
            'location' => "https://{$this->panel}.meinserver.io:8080/remote/index.php",
            'uri' => "https://{$this->panel}.meinserver.io:8080/remote/",
            'trace' => 1,
            'exceptions' => 1,
            'stream_context' => stream_context_create(['ssl' => $sslOpts]),
        ]);
    }

    /**
     * Invoke an ISPConfig SOAP function. Session id is injected as the
     * first argument automatically — pass only the remaining args.
     *
     * Use the resource accessors (`$client->sites->find(...)`) when a
     * typed wrapper exists; reach for `call()` directly for the functions
     * this SDK hasn't yet surfaced.
     */
    public function call(string $function, mixed ...$args): mixed
    {
        return $this->soap->{$function}($this->session(), ...$args);
    }

    /**
     * Login on first call, reuse the session id afterwards. Resources
     * never see this — Client::call() handles it.
     */
    private function session(): string
    {
        if ($this->sessionId === null) {
            $sid = $this->soap->login($this->apiUser, $this->apiKey);
            if (empty($sid)) {
                throw new Exception('ISPConfig SOAP login returned an empty session id.');
            }
            $this->sessionId = $sid;
        }

        return $this->sessionId;
    }

    public function __destruct()
    {
        if ($this->sessionId !== null) {
            try {
                $this->soap->logout($this->sessionId);
            } catch (SoapFault) {
                // best-effort cleanup; the session expires on its own anyway
            }
        }
    }
}
