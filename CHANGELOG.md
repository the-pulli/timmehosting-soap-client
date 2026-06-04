# Changelog

All notable changes to `pulli/timmehosting-soap-client` will be documented in this file.

## v1.0.0 - 2026-06-04

Initial stable release. Complete typed coverage of the ISPConfig SOAP Remote API surface as documented by Timme Hosting at [https://timmehosting.de/ispconfig-api-schnittstelle-zur-automatisierung](https://timmehosting.de/ispconfig-api-schnittstelle-zur-automatisierung).

### Coverage

- **236 SOAP functions** wrapped with typed resource methods — every endpoint Timme documents
- Generic `$client->call('soap_function_name', ...)` escape hatch for anything beyond the documented surface — session id is injected automatically
- The full set of Timme example scripts is archived in [`reference/timme-ispconfig-api/`](reference/timme-ispconfig-api/) so the canonical parameter shapes survive even if Timme reorganises the page

### Architecture

- `Pulli\TimmeSoapClient\Client` — one SOAP session per instance, lazy login on first call, logout on destruct, injectable `\SoapClient` for tests, TLS verification on by default
- `Pulli\TimmeSoapClient\Resource` — abstract base for resource accessors
- `Pulli\TimmeSoapClient\Exception` — semantic errors. `\SoapFault` still propagates from the SOAP transport layer
- 14 resource accessors on `Client`: `sites`, `clients`, `php`, `cron`, `supervisor`, `databases`, `shellUsers`, `ftpUsers`, `dns`, `mail`, `server`, `monitor`, `domains`, `utility`

### Backed enums for ISPConfig magic-string fields

- `Toggle` — `y`/`n` (with `Toggle::fromBool()`)
- `Status` — `active`/`inactive` (with `Status::fromBool()`)
- `CronType` — `url` / `full` / `chrooted`
- `DnsRecordType` — `a`, `aaaa`, `alias`, `cname`, `hinfo`, `mx`, `ns`, `ptr`, `rp`, `soa`, `srv`, `txt`, `ds`, `dnskey`, `caa`, `tlsa`, `sshfp`
- `CrudOp` — `get` / `add` / `update` / `delete`
- `BackupAction` — `backup_download`, `backup_download_link`, `backup_restore`

### Factory

`Client::fromEnv()` reads `TIMME_SOAP_PANEL`, `TIMME_SOAP_USER`, `TIMME_SOAP_KEY`, optional `TIMME_SOAP_VERIFY_TLS=0`.

### CI

- Tests matrix: PHP 8.4 + 8.5 × prefer-lowest + prefer-stable × ubuntu + windows (8 jobs)
- Pre-commit hook (`.githooks/pre-commit`) runs Pint + Pest locally before every commit, auto-activated via composer post-install
