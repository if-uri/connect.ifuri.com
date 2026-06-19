# connect.ifuri.com

PHP connector hub for ifuri and urirun.

The site is designed for Plesk hosting and exposes:

- `GET /` - human-friendly connector picker,
- `GET /connectors/{id}` - indexable connector detail page with URI routes,
- `GET /connectors/{id}.json` - machine-readable connector manifest,
- `GET /connectors.json` - connector catalog,
- `GET /registry.json` - machine-readable URI package catalog,
- `GET /search.json` - flat connector and URI route search index,
- `GET /install?connectors=planfile,namecheap-dns` - shell installer script,
- `GET /sitemap.xml` - search-engine sitemap for the hub and connector pages,
- `GET /robots.txt` - crawler policy with sitemap location,
- `GET /llms.txt` - compact LLM-readable connector index.

## Local run

```bash
php -S 127.0.0.1:8099 router.php
```

Open `http://127.0.0.1:8099/`.

Example connector page:

```text
http://127.0.0.1:8099/connectors/namecheap-dns
http://127.0.0.1:8099/connectors/namecheap-dns.json
http://127.0.0.1:8099/search.json
```

## Test

```bash
bash tests/smoke.sh
CONNECT_HUB_BASE=https://connect.ifuri.com bash tests/smoke.sh
CONNECT_HUB_BASE=https://connect.ifuri.com CONNECT_HUB_INSTALL_TEST=1 bash tests/smoke.sh
```

The smoke test validates PHP syntax, catalog JSON, every connector detail page,
every connector JSON manifest, the route search index, every generated installer
script, local router behavior, public URLs when `CONNECT_HUB_BASE` is set,
OpenGraph/Twitter metadata, `sitemap.xml`, `robots.txt` and `llms.txt`.

## One-liner

```bash
curl -fsSL 'https://connect.ifuri.com/install?connectors=planfile,namecheap-dns' | bash
```

By default the installer uses the current GitHub source for the Python urirun
package. Override it when the final `if-uri/urirun` package is published:

```bash
URIRUN_PIP_SPEC='urirun @ git+https://github.com/if-uri/urirun.git@main#subdirectory=runtimes/python' \
curl -fsSL 'https://connect.ifuri.com/install?connectors=planfile' | bash
```

## Plesk deploy

Point the domain document root to this repository root or upload the repository
contents to the domain `httpdocs` directory. No Composer step is required.

Optional GitHub Actions deploy uses these repository secrets:

- `PLESK_HOST`
- `PLESK_USER`
- `PLESK_PATH`
- `PLESK_SSH_KEY`

When the secrets are not configured, the workflow still runs tests and skips the
deploy step.
