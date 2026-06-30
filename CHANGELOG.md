# Changelog

All notable changes to `pulli/timmehosting-soap-client` will be documented in this file.

## v1.2.0 - 2026-06-30

### Added

- `Sites::setCustomPhpIni()` — idempotently set a vhost's `custom_php_ini` field (raw php.ini directives applied to the site's FPM pool), mirroring `setNginxDirectives()`.

## v1.1.0 - 2026-06-04

Adds backed enums for the ISPConfig magic-string values that consumers used to have to remember when building `$params` arrays. Same pattern as the existing top-level enums (`Toggle`, `Status`, `CronType`, …), but organised in a separate namespace to keep the distinction clear:

- **`Pulli\TimmeSoapClient\Enums\*`** — direct method-argument enums (existing)
- **`Pulli\TimmeSoapClient\Enums\Params\*`** — values used **inside** `$params` arrays via `->value` (new)

### New param-value enums

| Enum | ISPConfig field | Values |
|---|---|---|
| `IpType` | `server_ip.ip_type` | `IPv4`, `IPv6` |
| `VhostType` | `web_domain.type` | `Vhost`, `Alias`, `Subdomain`, `VhostAlias`, `VhostSubdomain` |
| `WebSubdomain` | `web_domain.subdomain` | `None`, `Www`, `Wildcard` (`*`) |
| `PhpHandler` | `web_domain.php` | `Disabled` (`no`), `FastCgi`, `PhpFpm`, `Mod` |
| `DatabaseType` | `sites_database.type` | `Mysql`, `Postgresql` |
| `ChrootMode` | `shell_user.chroot` | `None` (`no`), `Jailkit`, `SshChroot` |
| `DnsZoneType` | `dns_zone.type` | `Master`, `Slave` (uppercase! — ISPConfig's only uppercase enum field) |
| `SslAction` | `web_domain.ssl_action` | `Create`, `Save`, `Delete` (`del`) |

### Usage

```php
use Pulli\TimmeSoapClient\Enums\Toggle;
use Pulli\TimmeSoapClient\Enums\Params{VhostType, WebSubdomain, PhpHandler};

$client->sites->add(clientId: 2, params: [
'server_id' => 1,
'domain'    => 'example.com',
'type'      => VhostType::Vhost->value,        // 'vhost'
'subdomain' => WebSubdomain::Www->value,       // 'www'
'php'       => PhpHandler::PhpFpm->value,      // 'php-fpm'
'active'    => Toggle::Yes->value,             // 'y'
]);
```

See [USAGE.md → Param-value enums](https://github.com/the-pulli/timmehosting-soap-client/blob/main/USAGE.md#param-value-enums) for the full reference with worked examples for every entity.

### Compatibility

Non-breaking — purely additive. All existing code keeps working unchanged.

## v1.0.0 - 2026-06-04

Initial stable release. Complete typed coverage of the ISPConfig SOAP Remote API surface as documented by Timme Hosting at [https://timmehosting.de/ispconfig-api-schnittstelle-zur-automatisierung](https://timmehosting.de/ispconfig-api-schnittstelle-zur-automatisierung).

See the [v1.0.0 release page](https://github.com/the-pulli/timmehosting-soap-client/releases/tag/v1.0.0) for the full description.
