# `data/` — connector catalog source of truth

This directory holds the connector catalog the hub serves. **It is preserved on
deploy** (`scripts/deploy-plesk.sh` rsyncs without `--delete`), so production
submissions and edits are never clobbered by a push.

## Contents

| Path | What it is |
| --- | --- |
| `connectors/<id>/manifest.json` | Per-connector manifest (one folder per connector). Validated against [`../schema/connector.schema.json`](../schema/connector.schema.json). `id` must equal the folder name. |
| `connectors.json` | Aggregated catalog served at `/connectors.json`. **Generated** from the per-connector manifests by `make build-catalog` (`tools/build_catalog.py`); validated against [`../schema/connectors.schema.json`](../schema/connectors.schema.json). |
| `catalog.meta.json` | Catalog-wide metadata: `version`, `updatedAt`, `defaultPipSpec`, and `site` (titles, ecosystem links). |

## Workflow

1. Add or edit `connectors/<id>/manifest.json`.
2. `make build-catalog` — regenerate `connectors.json` from the manifests.
3. `make check-catalog` — verify the generated catalog is up to date.
4. `python3 scripts/validate_connectors.py` — schema-validate every manifest +
   the catalog (also run in CI via `ci-deploy.yml`).

Derived endpoints (`/registry.json`, `/search.json`, `/mcp.json`, `/a2a.json`)
are computed from this data by `lib/hub.php` at request time and cached
(`Cache-Control: public, max-age=120`).
