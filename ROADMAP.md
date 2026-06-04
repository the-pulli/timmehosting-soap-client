# Roadmap

v1.0.0 is feature-complete against the Timme-documented ISPConfig
Remote API surface — **236 SOAP functions** wrapped, every endpoint on
<https://timmehosting.de/ispconfig-api-schnittstelle-zur-automatisierung>
reachable via a typed resource method. The Timme example scripts that
shaped the SDK's signatures are archived in [`reference/timme-ispconfig-api/`](reference/timme-ispconfig-api/).

## Beyond the documented surface

ISPConfig exposes more functions than Timme documents — version-specific
additions, ISPConfig forks, the broader `monitor_get_*` namespace,
custom plugins. Reach those today with:

```php
$fns = $client->server->functionList();   // or $client->utility->functionList()
$client->call('any_undocumented_function', ...$args);
```

When you find yourself reaching for `$client->call(...)` for the same
function more than a couple of times, lift it into a typed resource
method. The existing resources are the template.

## Possible future additions (not blocking v1.0.0)

- **Sub-resources for nested entities.** Today `$client->mail` carries
  ~60 methods. A future minor could split into `$client->mail->users`,
  `$client->mail->aliases`, `$client->mail->spamfilter`, etc. — purely
  ergonomic, no SOAP-level change.
- **Response DTOs.** Calls currently return raw `array<string, mixed>`
  from the wire. A future minor could shape them into typed read-models.
  This is a much bigger surface change and may not be worth the maintenance
  burden given how thin the SDK already is.
- **First-class Reseller / Helpdesk / Billing.** Not in Timme's docs
  page; only relevant if Timme ever enables those modules for your
  account.
