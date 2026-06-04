# Usage

Detailed examples of every typed function in `pulli/timmehosting-soap-client`. For the high-level overview see [README.md](README.md); for the canonical SOAP signatures see the [Timme example scripts](reference/timme-ispconfig-api/) archived in this repo.

## Table of contents

- [Initialisation](#initialisation)
- [Generic escape hatch — `$client->call()`](#generic-escape-hatch--client-call)
- [Resources](#resources)
  - [`$client->sites`](#-clientsites)
  - [`$client->clients`](#-clientclients)
  - [`$client->php`](#-clientphp)
  - [`$client->cron`](#-clientcron)
  - [`$client->supervisor`](#-clientsupervisor)
  - [`$client->databases`](#-clientdatabases)
  - [`$client->shellUsers`](#-clientshellusers)
  - [`$client->ftpUsers`](#-clientftpusers)
  - [`$client->dns`](#-clientdns)
  - [`$client->mail`](#-clientmail)
  - [`$client->server`](#-clientserver)
  - [`$client->monitor`](#-clientmonitor)
  - [`$client->domains`](#-clientdomains)
  - [`$client->utility`](#-clientutility)
- [Enums](#enums)

---

## Initialisation

### Explicit construction

```php
use Pulli\TimmeSoapClient\Client;

$client = new Client(
    panel:     'sXXqXX',           // panel subdomain → sXXqXX.meinserver.io
    apiUser:   'pulli_server',     // ISPConfig System → Remote Users
    apiKey:    $remoteUserSecret,
    verifyTls: true,               // default; flip to false for self-signed panels
);
```

### From environment variables

```php
// Reads:
//   TIMME_SOAP_PANEL       (required) — e.g. "s00q00"
//   TIMME_SOAP_USER        (required) — Remote API username
//   TIMME_SOAP_KEY         (required) — Remote API password
//   TIMME_SOAP_VERIFY_TLS  (optional) — set to "0"/"false"/"off"/"no" to disable TLS verification
$client = Client::fromEnv();
```

### With an injected SoapClient (tests)

```php
$soap = new MyFakeSoapClient();   // your test double
$client = new Client('s00q00', 'remote', 'secret', verifyTls: true, soap: $soap);
```

See [`tests/ClientTest.php`](tests/ClientTest.php) for a worked test-double pattern.

### Lifecycle

A `Client` instance owns one SOAP session: it logs in lazily on the first SOAP call (any resource method or `$client->call()`) and logs out automatically on destruction. You do not manage session ids yourself.

---

## Generic escape hatch — `$client->call()`

For any ISPConfig SOAP function that doesn't yet have a typed resource method, call it directly:

```php
// Session id is injected automatically — pass only the remaining args.
$result = $client->call('any_undocumented_soap_function', $clientId, $params);

// Equivalent to: $soap->any_undocumented_soap_function($session, $clientId, $params)
```

Use this for ISPConfig version-specific additions, fork-specific endpoints, or anything beyond Timme's documented surface.

---

## Resources

### `$client->sites`

`sites_web_domain_*` — web vhosts, plus the per-site sub-entities (aliasdomain, subdomain, folder, redis, …).

#### Vhost CRUD

```php
// Find by primary id
$site = $client->sites->find(domainId: 5);

// Find by domain string (returns null if not found)
$site = $client->sites->findByDomain('example.com');

// Create
$newId = $client->sites->add(
    clientId: 2,
    params: [
        'server_id'        => 1,
        'ip_address'       => '*',
        'domain'           => 'example.com',
        'type'             => 'vhost',
        'parent_domain_id' => 0,
        'active'           => 'y',
    ],
);

// Update — ISPConfig regenerates nginx config asynchronously (~30-60s)
$client->sites->update(
    clientId: 2,
    domainId: 5,
    params:   ['php' => 'php-fpm'],
);

// Delete
$client->sites->delete(domainId: 5);
```

#### Idempotent helpers

```php
use Pulli\TimmeSoapClient\Enums\Status;

// Returns false when value already matches, true on update — safe to call every deploy
$changed = $client->sites->setWebFolder(
    clientId: 2,
    domainId: 5,
    webFolder: 'myapp/current/public',
);

$changed = $client->sites->setNginxDirectives(
    clientId: 2,
    domainId: 5,
    directives: <<<NGINX
    location ^~ /.well-known/acme-challenge {
        alias /home/forge/.letsencrypt;
    }
    NGINX,
);

// Toggle vhost active/inactive
$client->sites->setStatus(domainId: 5, status: Status::Inactive);

// Force ISPConfig to regenerate the on-disk vhost from the current record
$client->sites->recreateVhost(domainId: 5);
```

#### Vhost backups

```php
use Pulli\TimmeSoapClient\Enums\BackupAction;

// List existing backups for a site
$backups = $client->sites->backupList(domainId: 5);

// Generic backup action — pick what to do with an existing backup record
$client->sites->backup(backupId: 99, action: BackupAction::BackupRestore);

// Convenience: get a signed download URL (shortcut for ::BackupDownloadLink)
$url = $client->sites->backupDownloadLink(backupId: 99);

// Toggle the `backup_active` field on a site (enables/disables future scheduled backups)
$client->sites->backupActive(serverId: 1, domainId: 5);

// Trigger an immediate backup (out of schedule). Defaults back up both website + db.
$client->sites->instantBackup(
    serverId: 1,
    domainId: 5,
    backupWebsite: true,
    backupDatabase: true,
);
```

#### Alias domains (`sites_web_aliasdomain_*`)

```php
$alias = $client->sites->findAliasdomain(id: 12);
$newId = $client->sites->addAliasdomain(clientId: 2, params: [...]);
$client->sites->updateAliasdomain(clientId: 2, id: 12, params: [...]);
$client->sites->deleteAliasdomain(id: 12);
```

#### Subdomains (`sites_web_subdomain_*`)

```php
$sub = $client->sites->findSubdomain(id: 7);
$newId = $client->sites->addSubdomain(clientId: 2, params: [...]);
$client->sites->updateSubdomain(clientId: 2, id: 7, params: [...]);
$client->sites->deleteSubdomain(id: 7);
```

#### VHost alias domains + VHost subdomains

```php
// sites_web_vhost_aliasdomain_*
$client->sites->findVhostAliasdomain(id: 3);
$client->sites->addVhostAliasdomain(clientId: 2, params: [...]);
$client->sites->updateVhostAliasdomain(clientId: 2, id: 3, params: [...]);
$client->sites->deleteVhostAliasdomain(id: 3);

// sites_web_vhost_subdomain_*
$client->sites->findVhostSubdomain(id: 4);
$client->sites->addVhostSubdomain(clientId: 2, params: [...]);
$client->sites->updateVhostSubdomain(clientId: 2, id: 4, params: [...]);
$client->sites->deleteVhostSubdomain(id: 4);
```

#### Web folders + folder users

```php
// sites_web_folder_* (protected directories)
$client->sites->findFolder(id: 8);
$client->sites->addFolder(clientId: 2, params: [...]);
$client->sites->updateFolder(clientId: 2, id: 8, params: [...]);
$client->sites->deleteFolder(id: 8);

// sites_web_folder_user_* (HTTP-basic auth users for protected folders)
$client->sites->findFolderUser(id: 9);
$client->sites->addFolderUser(clientId: 2, params: [...]);
$client->sites->updateFolderUser(clientId: 2, id: 9, params: [...]);
$client->sites->deleteFolderUser(id: 9);
```

#### Redis

**Quirk:** `sites_redis_add` takes `($session, $params)` — no `$client_id`, unlike every other `*_add`. The SDK honours this:

```php
// Add — no $clientId
$newId = $client->sites->addRedis(params: [
    'server_id'        => 1,
    'parent_domain_id' => 5,
    'maxmemory'        => '256mb',
]);

// Other CRUD follows the normal pattern
$client->sites->findRedis(id: 11);
$client->sites->updateRedis(clientId: 2, id: 11, params: [...]);
$client->sites->deleteRedis(id: 11);

// List every Redis instance on the panel
$all = $client->sites->allRedis();
```

#### Database versions

```php
// Available PG/MySQL versions on a server
$versions = $client->sites->databaseVersions(serverId: 1);
```

---

### `$client->clients`

`client_*` — ISPConfig customer / reseller records.

> **Important:** `client_update` is only callable by a reseller API user. A plain Remote User can read clients but updates will throw a SOAP fault.

#### Reads

```php
$client_record = $client->clients->find(clientId: 2);
$all_clients = $client->clients->all();
$gid = $client->clients->groupId(clientId: 2);     // gid for filesystem ownership + sites_*_update

// Lookups — return null if not found
$rec = $client->clients->findByUsername('pulli_main');
$rec = $client->clients->findByCustomerNo('C-12345');

// Direct id lookup by username — returns 0 if not found
$id = $client->clients->idByUsername('pulli_main');

// Misc reads
$email = $client->clients->emailContact(clientId: 2);
$sites = $client->clients->sitesByUser(clientId: 2);
```

#### Writes

```php
$newId = $client->clients->add(
    params: [
        'company_name' => 'Acme GmbH',
        'username'     => 'acme_main',
        'password'     => $secret,
        'email'        => 'admin@acme.example',
    ],
    resellerId: null,
);

// Reseller-only — see warning above
$client->clients->update(clientId: 2, resellerId: null, params: ['company_name' => 'New Name']);

// Standard delete (panel-level)
$client->clients->delete(clientId: 2);

// IRREVERSIBLE — deletes the client AND every site/db/email/dns/ftp/shell/cron they own
$client->clients->deleteEverything(clientId: 2);

// Bypass the normal update path to change just the password
$client->clients->changePassword(clientId: 2, newPassword: $newSecret);
```

#### Client templates (additional / template combinations)

```php
$tmpl = $client->clients->findTemplateAdditional(id: 4);
$newId = $client->clients->addTemplateAdditional(clientId: 2, params: [...]);
$client->clients->deleteTemplateAdditional(id: 4);

$allTemplates = $client->clients->allTemplates();
```

---

### `$client->php`

```php
// Restart the per-site PHP-FPM pool. Throws Exception on falsy return.
$client->php->restart(serverId: 1, serverPhpId: 11);

// List PHP versions the panel knows about on a server (use to pick a $serverPhpId at provisioning)
$versions = $client->php->versions(serverId: 1);
// → [ ['server_php_id' => 11, 'name' => 'PHP 8.5', ...], ... ]
```

---

### `$client->cron`

`sites_cron_*` — panel-managed cron entries scoped to a site.

```php
use Pulli\TimmeSoapClient\Enums\{CronType, Toggle};

// All cron entries for a site
$crons = $client->cron->forSite(domainId: 5);

// A single cron by id
$cron = $client->cron->find(cronId: 17);

// Add. Defaults: every minute, full shell, server_id=1, active=yes.
$newId = $client->cron->add(
    clientId: 2,
    domainId: 5,
    command:  '/opt/php-8.5/bin/php /var/www/.../artisan schedule:run',
    minute:   '*',
    hour:     '*',
    mday:     '*',
    month:    '*',
    wday:     '*',
    type:     CronType::Full,    // Url | Full | Chrooted
    serverId: 1,
    active:   Toggle::Yes,
);

// Idempotency is your job — typical pattern:
foreach ($client->cron->forSite($domainId) as $row) {
    if (str_contains($row['command'] ?? '', 'artisan schedule:run')) {
        return; // already there
    }
}
$client->cron->add($clientId, $domainId, $command);

$client->cron->update(clientId: 2, cronId: 17, params: ['active' => 'n']);
$client->cron->delete(cronId: 17);
```

---

### `$client->supervisor`

`sites_supervisor_*` — panel-managed supervisord jobs.

> **Important:** `sites_supervisor_add` is **not** exposed via the Remote API. Create jobs in the panel UI (Sites → Supervisor); the SDK only lists and restarts existing ones.

```php
// All supervisor jobs for a site
$jobs = $client->supervisor->forSite(domainId: 5);

// Restart one
$client->supervisor->restart(jobId: 42);

// Typical restart-all-for-site pattern
foreach ($client->supervisor->forSite($domainId) as $j) {
    $id = $j['supervisor_id'] ?? $j['id'] ?? null;
    if ($id) {
        $client->supervisor->restart((int) $id);
    }
}
```

---

### `$client->databases`

`sites_database_*` and `sites_database_user_*`.

> **Quirk:** ISPConfig auto-prefixes DB names with `c<client_id>`. Pass the suffix you want and inspect the returned record to learn the final full name.

#### Databases

```php
$db = $client->databases->find(databaseId: 21);

$allForUser = $client->databases->allByUser(clientId: 2);

// Get an unused port on the server for a new instance
$port = $client->databases->freePort(serverId: 1);

// List with optional filter
$dbs = $client->databases->all(['parent_domain_id' => 5]);

$newId = $client->databases->add(
    clientId: 2,
    params: [
        'server_id'        => 1,
        'parent_domain_id' => 5,
        'type'             => 'mysql',
        'database_name'    => 'myapp',   // becomes "c2myapp"
        'database_user_id' => $userId,
        'active'           => 'y',
    ],
);

$client->databases->update(clientId: 2, databaseId: 21, params: ['active' => 'n']);
$client->databases->delete(databaseId: 21);
```

#### Database users

```php
$user = $client->databases->findUser(databaseUserId: 33);

$newId = $client->databases->addUser(
    clientId: 2,
    params: [
        'server_id'              => 1,
        'database_user'          => 'myapp_rw',
        'database_password'      => $secret,
    ],
);

$client->databases->updateUser(clientId: 2, databaseUserId: 33, params: [...]);
$client->databases->deleteUser(databaseUserId: 33);
```

---

### `$client->shellUsers`

`sites_shell_user_*` — SSH/SFTP shell users on a site.

```php
$su = $client->shellUsers->find(shellUserId: 7);

$all = $client->shellUsers->all(['parent_domain_id' => 5]);

$newId = $client->shellUsers->add(
    clientId: 2,
    params: [
        'server_id'        => 1,
        'parent_domain_id' => 5,
        'username'         => 'deploy_runner',
        'password'         => $hashed,
        'shell'            => '/bin/bash',
        'chroot'           => 'jailkit',
    ],
);

$client->shellUsers->update(clientId: 2, shellUserId: 7, params: [...]);
$client->shellUsers->delete(shellUserId: 7);
```

---

### `$client->ftpUsers`

`sites_ftp_user_*` — FTP accounts on a site.

```php
$ftp = $client->ftpUsers->find(ftpUserId: 4);

$all = $client->ftpUsers->all(['parent_domain_id' => 5]);

$newId = $client->ftpUsers->add(
    clientId: 2,
    params: [
        'server_id'        => 1,
        'parent_domain_id' => 5,
        'username'         => 'site_ftp',
        'password'         => $hashed,
    ],
);

$client->ftpUsers->update(clientId: 2, ftpUserId: 4, params: [...]);
$client->ftpUsers->delete(ftpUserId: 4);

// FTP user + server in one call (sites_ftp_user_server_get)
$bundle = $client->ftpUsers->findWithServer(ftpUserId: 4);
```

---

### `$client->dns`

`dns_*` — DNS zones and records.

#### Zones

```php
use Pulli\TimmeSoapClient\Enums\Status;

$zone = $client->dns->findZone(zoneId: 10);

$zones = $client->dns->zones();                      // all
$zones = $client->dns->zones(['active' => 'y']);     // filtered

$mine = $client->dns->zonesByUser(clientId: 2, serverId: 1);

// origin lookup → primary id
$zoneId = $client->dns->zoneIdByOrigin(clientId: 2, origin: 'example.com.');

// Manual zone creation
$newId = $client->dns->addZone(
    clientId: 2,
    params: [
        'server_id' => 1,
        'origin'    => 'example.com.',
        'ns'        => 'ns1.example.com.',
        'mbox'      => 'hostmaster.example.com.',
        'refresh'   => 7200,
        'retry'     => 540,
        'expire'    => 604800,
        'minimum'   => 86400,
        'ttl'       => 86400,
        'xfer'      => '',
        'active'    => 'Y',
    ],
);

// Zone from server-side template (dns_templatezone_add)
$newId = $client->dns->addZoneFromTemplate(
    clientId:   2,
    serverId:   1,
    domain:     'example.com',
    ip:         '203.0.113.42',
    templateId: 1,
    ns1:        'ns1.example.com',
    ns2:        'ns2.example.com',
    email:      'hostmaster@example.com',
);

$client->dns->updateZone(clientId: 2, zoneId: 10, params: ['ttl' => 300]);
$client->dns->deleteZone(zoneId: 10);
$client->dns->setZoneStatus(zoneId: 10, status: Status::Inactive);

// All RRs under a zone, any type
$records = $client->dns->recordsForZone(zoneId: 10);
```

#### Typed record CRUD (A, AAAA, ALIAS, CNAME, HINFO, MX, NS, PTR, RP, SRV, TXT)

Each record type has the same four-method shape. Example for `A`:

```php
$client->dns->findA(id: 100);
$client->dns->addA(clientId: 2, params: [
    'server_id' => 1,
    'zone'      => 10,
    'name'      => 'www',
    'type'      => 'A',
    'data'      => '203.0.113.42',
    'ttl'       => 86400,
    'active'    => 'Y',
]);
$client->dns->updateA(clientId: 2, id: 100, params: ['data' => '203.0.113.43']);
$client->dns->deleteA(id: 100);
```

Identical surface for the others:

```php
$client->dns->{findAaaa, addAaaa, updateAaaa, deleteAaaa}(...);
$client->dns->{findAlias, addAlias, updateAlias, deleteAlias}(...);
$client->dns->{findCname, addCname, updateCname, deleteCname}(...);
$client->dns->{findHinfo, addHinfo, updateHinfo, deleteHinfo}(...);
$client->dns->{findMx, addMx, updateMx, deleteMx}(...);
$client->dns->{findNs, addNs, updateNs, deleteNs}(...);
$client->dns->{findPtr, addPtr, updatePtr, deletePtr}(...);
$client->dns->{findRp, addRp, updateRp, deleteRp}(...);
$client->dns->{findSrv, addSrv, updateSrv, deleteSrv}(...);
$client->dns->{findTxt, addTxt, updateTxt, deleteTxt}(...);
```

#### Long-tail record types — `record(type, op, …)`

For SOA, DS, DNSKEY, CAA, TLSA, SSHFP (or any RR type Timme adds later), use the generic accessor:

```php
use Pulli\TimmeSoapClient\Enums\{DnsRecordType, CrudOp};

$rec = $client->dns->record(DnsRecordType::Soa,  CrudOp::Get, 200);
$id  = $client->dns->record(DnsRecordType::Caa,  CrudOp::Add, 2, $params);
$client->dns->record(DnsRecordType::Sshfp, CrudOp::Delete, 201);
```

---

### `$client->mail`

`mail_*` — every mail entity ISPConfig exposes.

#### Domains

```php
use Pulli\TimmeSoapClient\Enums\Status;

$dom = $client->mail->findDomain(id: 30);
$dom = $client->mail->findDomainByName('example.com');   // null if missing

$newId = $client->mail->addDomain(clientId: 2, params: [
    'server_id' => 1,
    'domain'    => 'example.com',
    'active'    => 'y',
]);
$client->mail->updateDomain(clientId: 2, id: 30, params: [...]);
$client->mail->deleteDomain(id: 30);

$client->mail->setDomainStatus(id: 30, status: Status::Active);
```

#### Alias domains

```php
$client->mail->findAliasdomain(id: 31);
$client->mail->addAliasdomain(clientId: 2, params: [...]);
$client->mail->updateAliasdomain(clientId: 2, id: 31, params: [...]);
$client->mail->deleteAliasdomain(id: 31);
```

#### Mailboxes (users)

```php
$mb = $client->mail->findUser(id: 50);

// List all mail users on a server
$all = $client->mail->listUsers(serverId: 1);

$newId = $client->mail->addUser(clientId: 2, params: [
    'server_id'       => 1,
    'email'           => 'user@example.com',
    'login'           => 'user@example.com',
    'password'        => $hashed,
    'name'            => 'User Name',
    'quota'           => 524288000,    // bytes
    'maildir'         => '/var/vmail/example.com/user',
    'postfix'         => 'y',
    'access'          => 'y',
]);
$client->mail->updateUser(clientId: 2, id: 50, params: [...]);
$client->mail->deleteUser(id: 50);
```

#### Mailbox backups

```php
use Pulli\TimmeSoapClient\Enums\BackupAction;

// List existing backups for a mailbox
$backups = $client->mail->userBackups(userId: 50);

// Generic action on a backup record
$client->mail->userBackup(backupId: 88, action: BackupAction::BackupRestore);

// Shortcut: signed download URL
$url = $client->mail->userBackupDownloadLink(backupId: 88);

// Toggle backup_active flag on the mailbox
$client->mail->userBackupActive(serverId: 1, mailUserId: 50);

// Trigger an immediate out-of-schedule backup
$client->mail->userInstantBackup(serverId: 1, mailUserId: 50);
```

#### Per-user filters (`mail_user_filter_*`)

```php
$f = $client->mail->findUserFilter(id: 70);
$id = $client->mail->addUserFilter(clientId: 2, params: [
    'mailuser_id' => 50,
    'rulename'    => 'subject match',
    'source'      => 'Subject',
    'op'          => 'contains',
    'searchterm'  => '[urgent]',
    'action'      => 'Move',
    'target'      => 'Urgent',
    'active'      => 'y',
]);
$client->mail->updateUserFilter(clientId: 2, id: 70, params: [...]);
$client->mail->deleteUserFilter(id: 70);
```

#### Aliases / forwards / catchalls

```php
// alias
$client->mail->findAlias(id: 100);
$client->mail->addAlias(clientId: 2, params: ['source' => 'alias@example.com', 'destination' => 'real@example.com', ...]);
$client->mail->updateAlias(clientId: 2, id: 100, params: [...]);
$client->mail->deleteAlias(id: 100);

// forward
$client->mail->findForward(id: 101);
$client->mail->addForward(clientId: 2, params: [...]);
$client->mail->updateForward(clientId: 2, id: 101, params: [...]);
$client->mail->deleteForward(id: 101);

// catchall
$client->mail->findCatchall(id: 102);
$client->mail->addCatchall(clientId: 2, params: ['source' => '@example.com', 'destination' => 'all@example.com']);
$client->mail->updateCatchall(clientId: 2, id: 102, params: [...]);
$client->mail->deleteCatchall(id: 102);
```

#### Blacklists / whitelists

```php
$client->mail->{findBlacklist, addBlacklist, updateBlacklist, deleteBlacklist}(...);
$client->mail->{findWhitelist, addWhitelist, updateWhitelist, deleteWhitelist}(...);
```

#### Spamfilter

```php
// spamfilter user policy
$client->mail->{findSpamfilterUser, addSpamfilterUser, updateSpamfilterUser, deleteSpamfilterUser}(...);

// spamfilter per-source blacklists/whitelists
$client->mail->{findSpamfilterBlacklist, addSpamfilterBlacklist, updateSpamfilterBlacklist, deleteSpamfilterBlacklist}(...);
$client->mail->{findSpamfilterWhitelist, addSpamfilterWhitelist, updateSpamfilterWhitelist, deleteSpamfilterWhitelist}(...);
```

#### Filters / policies / transport / relay / fetchmail

```php
// Server-level filters (mail_filter_*)
$client->mail->{findFilter, addFilter, updateFilter, deleteFilter}(...);

// Policy (mail_policy_*)
$client->mail->{findPolicy, addPolicy, updatePolicy, deletePolicy}(...);

// Transport (mail_transport_*)
$client->mail->{findTransport, addTransport, updateTransport, deleteTransport}(...);

// Relay recipients
$client->mail->{findRelayRecipient, addRelayRecipient, updateRelayRecipient, deleteRelayRecipient}(...);

// Fetchmail
$client->mail->{findFetchmail, addFetchmail, updateFetchmail, deleteFetchmail}(...);
```

#### Quotas

```php
// Mail-specific quota usage for a system user
$info = $client->mail->quotaByUser(username: 'mailuser1');
```

---

### `$client->server`

`server_*` — discovery, config, IPs, fail2ban, record-by-id.

#### Discovery + config

```php
// Names of every Remote API function the panel exposes to the current user
$fns = $client->server->functionList();

// Per-server function manifest (with role flags)
$fns = $client->server->functions();

// Resolve server_id from IP / hostname
$serverId = $client->server->idByIp(ip: '203.0.113.42');
$serverId = $client->server->idByName(name: 'web01.example.com');

// Datalog progress flag (per-server queue health)
$status = $client->server->sysDatalogStatus(serverId: 1);

// Server config (the whole server row)
$srv = $client->server->config(serverId: 1);

// All servers
$all = $client->server->all();

// Installed ISPConfig app versions
$ver = $client->server->appVersion();

// Generic table read — useful for tables not yet wrapped
$row = $client->server->recordById(table: 'web_database', primaryId: 21);
```

#### Fail2ban

> **Note:** ISPConfig only exposes `add`, `delete`, and `get_all` for f2b — no single-record `get`, no `update`.

```php
// blacklist
$rows = $client->server->f2bBlacklists(serverId: 1);
$id   = $client->server->addF2bBlacklist(clientId: 2, params: ['server_id' => 1, 'ip_address' => '198.51.100.7']);
$client->server->deleteF2bBlacklist(id: $id);

// whitelist
$rows = $client->server->f2bWhitelists(serverId: 1);
$id   = $client->server->addF2bWhitelist(clientId: 2, params: ['server_id' => 1, 'ip_address' => '203.0.113.10']);
$client->server->deleteF2bWhitelist(id: $id);
```

#### Server IPs

```php
$ip    = $client->server->findIp(id: 5);
$newId = $client->server->addIp(clientId: 2, params: [
    'server_id'      => 1,
    'client_id'      => 0,
    'ip_type'        => 'IPv4',
    'ip_address'     => '203.0.113.99',
    'virtualhost'    => 'y',
]);
$client->server->updateIp(clientId: 2, id: 5, params: [...]);
$client->server->deleteIp(id: 5);
```

---

### `$client->monitor`

`monitor_*` — ISPConfig's monitoring feeds. The surface is broad; the SDK wraps the common reads and provides a generic accessor for everything else.

```php
// Service state summary for a server
$state = $client->monitor->serverState(serverId: 1);

// Raw datalog for a given type
$rows = $client->monitor->dataLog(serverId: 1, type: 'cpu_info');

// Generic accessor — composes monitor_get_<name>
$data = $client->monitor->state('mem_usage', 1);                // monitor_get_mem_usage
$data = $client->monitor->state('disk_usage', 1);               // monitor_get_disk_usage
$data = $client->monitor->state('mailq', 1);                    // monitor_get_mailq
$data = $client->monitor->state('rkhunter', 1);                 // monitor_get_rkhunter
// (any monitor_get_* function ISPConfig exposes works the same way)
```

---

### `$client->domains`

`domains_*` — the registrar / domain module endpoints (only present when ISPConfig's domain module is enabled).

```php
$dom = $client->domains->find(id: 4);

$newId = $client->domains->add(clientId: 2, params: [
    'domain'    => 'newdomain.example',
    'client_id' => 2,
    'server_id' => 1,
]);

$client->domains->delete(id: 4);

$all = $client->domains->allByUser(clientId: 2);
```

---

### `$client->utility`

Cross-cutting functions that don't belong to a specific entity.

```php
// Every client visible to the current API user across all resellers
// (different scope from $client->clients->all())
$all = $client->utility->allClients();

// Top-level form of the function-list lookup (same payload as $client->server->functionList())
$fns = $client->utility->functionList();

// Disk/filesystem quota info for a system user
$q = $client->utility->quotaByUser(username: 'web5');
```

---

## Enums

Backed enums for the ISPConfig magic-string fields. Always prefer these to passing raw strings.

Two namespaces:

- `Pulli\TimmeSoapClient\Enums\*` — passed as direct method arguments (e.g. `Cron::add(type: CronType::Full)`).
- `Pulli\TimmeSoapClient\Enums\Params\*` — used as values **inside** `$params` arrays via `->value` (e.g. `'type' => VhostType::Vhost->value`).

### Method-argument enums

```php
use Pulli\TimmeSoapClient\Enums\{
    Toggle, Status, CronType, DnsRecordType, CrudOp, BackupAction,
};

// Toggle — ISPConfig's y/n flag
Toggle::Yes->value;           // 'y'
Toggle::No->value;            // 'n'
Toggle::fromBool(true);       // Toggle::Yes
Toggle::fromBool(false);      // Toggle::No

// Status — ISPConfig's active/inactive flag (set_status family)
Status::Active->value;        // 'active'
Status::Inactive->value;      // 'inactive'
Status::fromBool(true);       // Status::Active
Status::fromBool(false);      // Status::Inactive

// CronType — Cron::add()'s type parameter
CronType::Url;
CronType::Full;
CronType::Chrooted;

// DnsRecordType — for Dns::record(type, op, ...)
DnsRecordType::A;
DnsRecordType::Aaaa;
DnsRecordType::Alias;
DnsRecordType::Cname;
DnsRecordType::Hinfo;
DnsRecordType::Mx;
DnsRecordType::Ns;
DnsRecordType::Ptr;
DnsRecordType::Rp;
DnsRecordType::Soa;
DnsRecordType::Srv;
DnsRecordType::Txt;
DnsRecordType::Ds;
DnsRecordType::Dnskey;
DnsRecordType::Caa;
DnsRecordType::Tlsa;
DnsRecordType::Sshfp;

// CrudOp — for Dns::record(type, op, ...)
CrudOp::Get;
CrudOp::Add;
CrudOp::Update;
CrudOp::Delete;

// BackupAction — Sites::backup() and Mail::userBackup()
BackupAction::BackupDownload;
BackupAction::BackupDownloadLink;
BackupAction::BackupRestore;
```

### Param-value enums

These cover the magic-string values consumers used to have to remember when building `$params` arrays for `add`/`update` calls. Pass them via `->value`:

```php
use Pulli\TimmeSoapClient\Enums\Toggle;
use Pulli\TimmeSoapClient\Enums\Params\{
    IpType, VhostType, WebSubdomain, PhpHandler,
    DatabaseType, ChrootMode, DnsZoneType, SslAction,
};

$client->sites->add(clientId: 2, params: [
    'server_id'  => 1,
    'domain'     => 'example.com',
    'type'       => VhostType::Vhost->value,         // 'vhost'
    'subdomain'  => WebSubdomain::Www->value,        // 'www'
    'php'        => PhpHandler::PhpFpm->value,       // 'php-fpm'
    'active'     => Toggle::Yes->value,              // 'y' (existing method-arg enum)
]);

$client->server->addIp(clientId: 2, params: [
    'server_id'  => 1,
    'ip_type'    => IpType::IPv4->value,             // 'IPv4'
    'ip_address' => '203.0.113.99',
]);

$client->databases->add(clientId: 2, params: [
    'server_id'        => 1,
    'parent_domain_id' => 5,
    'type'             => DatabaseType::Mysql->value, // 'mysql'
    'database_name'    => 'myapp',
    'active'           => Toggle::Yes->value,
]);

$client->shellUsers->add(clientId: 2, params: [
    'server_id'        => 1,
    'parent_domain_id' => 5,
    'username'         => 'deploy_runner',
    'shell'            => '/bin/bash',
    'chroot'           => ChrootMode::Jailkit->value, // 'jailkit'
]);

$client->dns->addZone(clientId: 2, params: [
    'server_id' => 1,
    'origin'    => 'example.com.',
    'type'      => DnsZoneType::Master->value,        // 'MASTER' (uppercase — ISPConfig's only uppercase enum field)
    'active'    => 'Y',
]);

// SSL state machine — flip the action on a vhost update
$client->sites->update(clientId: 2, domainId: 5, params: [
    'ssl_action'     => SslAction::Create->value,    // 'create'
    'ssl_letsencrypt' => Toggle::Yes->value,
]);
```

#### Full param-value enum reference

| Enum | ISPConfig field | Values |
|---|---|---|
| `IpType` | `server_ip.ip_type` | `IPv4='IPv4'`, `IPv6='IPv6'` |
| `VhostType` | `web_domain.type` | `Vhost='vhost'`, `Alias='alias'`, `Subdomain='subdomain'`, `VhostAlias='vhostalias'`, `VhostSubdomain='vhostsubdomain'` |
| `WebSubdomain` | `web_domain.subdomain` | `None='none'`, `Www='www'`, `Wildcard='*'` |
| `PhpHandler` | `web_domain.php` | `Disabled='no'`, `FastCgi='fast-cgi'`, `PhpFpm='php-fpm'`, `Mod='mod'` |
| `DatabaseType` | `sites_database.type` | `Mysql='mysql'`, `Postgresql='postgresql'` |
| `ChrootMode` | `shell_user.chroot` | `None='no'`, `Jailkit='jailkit'`, `SshChroot='ssh-chroot'` |
| `DnsZoneType` | `dns_zone.type` | `Master='MASTER'`, `Slave='SLAVE'` (uppercase!) |
| `SslAction` | `web_domain.ssl_action` | `Create='create'`, `Save='save'`, `Delete='del'` |
