# reference/timme-ispconfig-api/

Local mirror of the ISPConfig SOAP example scripts published by Timme
Hosting at <https://timmehosting.de/ispconfig-api-schnittstelle-zur-automatisierung>.

**Why archived here:** Timme's docs page is the authoritative source for
which Remote API functions are exposed *and* the canonical parameter
shape per function. If Timme ever takes the page down or reorganises it,
this folder preserves the snapshot we built the SDK against.

- **Captured:** 2026-06-04
- **Files:** 239 `.php` examples (one per SOAP function, plus
  `soap_config.php` — the shared connection bootstrap)
- **URL inventory:** [`timme-ispconfig-api/URLS.txt`](timme-ispconfig-api/URLS.txt) — the
  original URLs each file was fetched from

The files are the **raw PHP source** as Timme served them via `.phps`
(HTML-syntax-highlighted on the wire — stripped back to plain PHP here).

Pint is configured to skip this folder (see `pint.json` at the repo
root) so the files stay byte-identical to what Timme published.

## Refreshing the snapshot

```bash
cd reference/timme-ispconfig-api
mkdir -p .html-tmp
< URLS.txt xargs -n1 -P16 -I{} sh -c '
  url="$1"
  stem=$(basename "${url%.phps}")
  curl -fsS "$url" -o ".html-tmp/${stem}.html"
' _ {}

for f in .html-tmp/*.html; do
  stem=$(basename "$f" .html)
  herd php -r '
    $html = file_get_contents($argv[1]);
    if (preg_match("|<pre>(.*?)</pre>|s", $html, $m)) {
      echo html_entity_decode(strip_tags($m[1]));
    }
  ' "$f" > "${stem}.php"
done
rm -rf .html-tmp
```
