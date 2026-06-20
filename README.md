# connect.ifuri.com

PHP connector hub for ifuri and urirun.

The site is designed for Plesk hosting and exposes:

- `GET /` - human-friendly connector picker,
- `GET /connectors/{id}` - indexable connector detail page with URI routes,
- `GET /connectors/{id}.json` - machine-readable connector manifest,
- `GET /connectors.json` - connector catalog,
- `GET /registry.json` - machine-readable URI package catalog,
- `GET /search.json` - flat connector and URI route search index,
- `GET /submit` - connector manifest builder and validation page,
- `POST /validate-connector` - connector manifest validation endpoint,
- `GET /install?connectors=planfile,namecheap-dns` - shell installer script,
- `GET /sitemap.xml` - search-engine sitemap for the hub and connector pages,
- `GET /robots.txt` - crawler policy with sitemap location,
- `GET /llms.txt` - compact LLM-readable connector index.

Connector source files live in `data/connectors/{id}/manifest.json`.
`data/connectors.json` is generated from those manifests:

```bash
python3 tools/build_catalog.py
python3 tools/build_catalog.py --check
```

Manifests and the generated catalog are validated against `schema/connector.schema.json`
and `schema/connectors.schema.json`:

```bash
python3 scripts/validate_connectors.py   # needs python3-jsonschema; run in CI
```

Contributor docs:

- [`docs/CONNECTORS-ARCHITECTURE.md`](docs/CONNECTORS-ARCHITECTURE.md)
- [`docs/SUBMIT-CONNECTOR.md`](docs/SUBMIT-CONNECTOR.md)
- [`docs/PLESK.md`](docs/PLESK.md)
- [`TODO.md`](TODO.md)
- [`CHANGELOG.md`](CHANGELOG.md)

Public docs:

- [`docs.ifuri.com/connectors.html`](https://docs.ifuri.com/connectors.html)
- [`docs.ifuri.com/getting-started.html`](https://docs.ifuri.com/getting-started.html)
- [`docs.ifuri.com/registry-and-bindings.html`](https://docs.ifuri.com/registry-and-bindings.html)
- [`docs.ifuri.com` work summary source](https://github.com/if-uri/docs/blob/main/work-summary-2026-06-20.md)

## Local run

```bash
php -S 127.0.0.1:8099 router.php
```

Open `http://127.0.0.1:8099/`.

Example connector page:

```text
http://127.0.0.1:8099/connectors/namecheap-dns
http://127.0.0.1:8099/connectors/namecheap-dns.json
http://127.0.0.1:8099/connectors/http-check
http://127.0.0.1:8099/connectors/http-check.json
http://127.0.0.1:8099/connectors/time-tools
http://127.0.0.1:8099/connectors/time-tools.json
http://127.0.0.1:8099/search.json
http://127.0.0.1:8099/submit
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
curl -fsSL 'https://connect.ifuri.com/install?connectors=http-check' | bash
curl -fsSL 'https://connect.ifuri.com/install?connectors=time-tools' | bash
```

By default the installer uses the pinned GitHub source for the Python urirun
package. Override it only when testing another runtime ref or fork:

```bash
URIRUN_PIP_SPEC='urirun @ git+https://github.com/if-uri/urirun.git@v0.3.14#subdirectory=adapters/python' \
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

## Verified connector packages

`planfile` is now an external connector package for task queues and sprint
workflows:

- package: [`github.com/if-uri/urirun-connector-planfile`](https://github.com/if-uri/urirun-connector-planfile)
- hub page: [`/connectors/planfile`](https://connect.ifuri.com/connectors/planfile)
- routes: `task://host/tickets/query/list`, `task://host/ticket/command/create`, `planfile://host/dsl/command/run`
- install: `curl -fsSL 'https://connect.ifuri.com/install?connectors=planfile' | bash`

It was tested through direct CLI execution, generated bindings, `urirun run`,
Docker, MCP tools and A2A skill projection.

`domain-monitor` is now an external connector package for domain observations,
logs, screenshot artifacts and domain-check flows:

- package: [`github.com/if-uri/urirun-connector-domain-monitor`](https://github.com/if-uri/urirun-connector-domain-monitor)
- hub page: [`/connectors/domain-monitor`](https://connect.ifuri.com/connectors/domain-monitor)
- routes: `monitor://host/http/query/status`, `monitor://host/dns/query/current`, `flow://host/domain/command/check`, `flow://host/daily/command/run`
- install: `curl -fsSL 'https://connect.ifuri.com/install?connectors=domain-monitor' | bash`

It was tested through a fresh GitHub install, direct CLI execution, generated
bindings, `urirun run`, Docker, MCP tools and A2A skill projection.

`namecheap-dns` is now a dedicated external connector package for safe
Namecheap host-record changes:

- package: [`github.com/if-uri/urirun-connector-namecheap-dns`](https://github.com/if-uri/urirun-connector-namecheap-dns)
- hub page: [`/connectors/namecheap-dns`](https://connect.ifuri.com/connectors/namecheap-dns)
- routes: `dns://host/records/query/current`, `dns://host/records/command/plan`, `dns://host/records/command/backup`, `dns://host/records/command/apply`
- install: `curl -fsSL 'https://connect.ifuri.com/install?connectors=namecheap-dns' | bash`

It was tested through a fresh GitHub install, direct CLI execution, generated
bindings, `urirun run`, Docker, MCP tools and A2A skill projection.

`sqlite-context` is now an external connector package for host memory:

- package: [`github.com/if-uri/urirun-connector-sqlite-context`](https://github.com/if-uri/urirun-connector-sqlite-context)
- hub page: [`/connectors/sqlite-context`](https://connect.ifuri.com/connectors/sqlite-context)
- routes: `data://host/record/command/upsert`, `artifact://host/artifacts/query/list`, `log://host/logs/query/recent`
- install: `curl -fsSL 'https://connect.ifuri.com/install?connectors=sqlite-context' | bash`

It was tested through a fresh GitHub install, direct CLI execution, generated
bindings, `urirun run`, Docker, MCP tools and A2A skill projection.

`http-check` proves the external connector flow end to end:

- package: [`github.com/if-uri/urirun-connector-http-check`](https://github.com/if-uri/urirun-connector-http-check)
- hub page: [`/connectors/http-check`](https://connect.ifuri.com/connectors/http-check)
- route: `httpcheck://host/http/query/status`
- install: `curl -fsSL 'https://connect.ifuri.com/install?connectors=http-check' | bash`

It was tested through the public installer, direct CLI execution and
`urirun run` against a compiled registry.

`time-tools` is the second external connector package and proves the same flow
without requiring network services or secrets:

- package: [`github.com/if-uri/urirun-connector-time-tools`](https://github.com/if-uri/urirun-connector-time-tools)
- hub page: [`/connectors/time-tools`](https://connect.ifuri.com/connectors/time-tools)
- route: `time://host/clock/query/now`
- install: `curl -fsSL 'https://connect.ifuri.com/install?connectors=time-tools' | bash`

## Authoring a new connector

Scaffold a connector package from the template (mirrors `urirun-connector-http-check`):

```bash
scripts/new-connector.sh <id> [scheme] [Name]   # e.g. scripts/new-connector.sh weather-now weather "Weather Now"
cd ../urirun-connector-<id> && make test          # editable install + urirun smoke (validate/compile/run + MCP)
```

The generated `connector.manifest.json` is schema-valid and ready to submit at
https://connect.ifuri.com/submit . Implement `run()` in the package's `core.py`.
CI (`scripts/validate_connectors.py` + template self-check) keeps manifests valid.

## Related repositories

- Runtime: [if-uri/urirun](https://github.com/if-uri/urirun)
- Public docs: [if-uri/docs](https://github.com/if-uri/docs)
- Examples and E2E flows: [if-uri/examples](https://github.com/if-uri/examples)
- App/host: [if-uri/app](https://github.com/if-uri/app)
- Installer: [if-uri/get](https://github.com/if-uri/get)
- HTTP connector: [if-uri/urirun-connector-http-check](https://github.com/if-uri/urirun-connector-http-check)
- Time connector: [if-uri/urirun-connector-time-tools](https://github.com/if-uri/urirun-connector-time-tools)
- Browser connector: [if-uri/urirun-connector-browser-control](https://github.com/if-uri/urirun-connector-browser-control)
- Namecheap DNS connector: [if-uri/urirun-connector-namecheap-dns](https://github.com/if-uri/urirun-connector-namecheap-dns)
