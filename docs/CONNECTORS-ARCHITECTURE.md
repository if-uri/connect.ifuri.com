# Connector catalog architecture

## Where we are

The source of truth is now one folder per connector:

```text
data/catalog.meta.json
data/connectors/<id>/manifest.json
schema/connector.schema.json
```

`data/connectors.json` is a generated aggregate committed to the repository so
Plesk can serve the hub without a build step at request time.

Build and check:

```bash
python3 tools/build_catalog.py
python3 tools/build_catalog.py --check
```

The served machine endpoints are derived from the generated aggregate:

- `GET /connectors.json` -> full public connector catalog,
- `GET /connectors/{id}.json` -> one connector manifest plus registry entry,
- `GET /registry.json` -> runtime registry projection,
- `GET /search.json` -> flat connector and URI route search index,
- `GET /install?connectors=...` -> shell installer for selected ids,
- `GET /llms.txt` -> compact LLM-readable index.

## The invariant

Whatever the internal layout, the served bytes of `/connectors.json` and
`/registry.json` must not change unless the change is intentional. That is what
makes future refactors safe for the ifuri app.

This is guarded by:

```bash
python3 tools/build_catalog.py --check
php tests/snapshot_test.php
bash tests/smoke.sh
```

`tests/snapshot_test.php` compares `/connectors.json` and `/registry.json`
against `tests/golden/*`, with volatile `generatedAt` normalized.

```bash
php tests/snapshot_test.php           # verify
php tests/snapshot_test.php --update  # only after an intentional contract change
```

## Folder layout

Each connector owns its manifest and future assets:

```text
data/connectors/<id>/
  manifest.json        # source of truth, schema/connector.schema.json
  icon.svg             # optional, folder-relative asset
  README.md            # optional long-form docs
  examples/
    *.json             # optional example payloads
```

`data/catalog.meta.json` stores shared catalog metadata and `connectorOrder`.
That keeps ordering stable without adding UI-only order fields to manifests.

## Field mapping

`manifest.json` is a superset of one public catalog entry.

| manifest field | catalog entry | rule |
|----------------|---------------|------|
| `id` | `id` | must equal folder name |
| `name`,`status`,`category`,`summary`,`description` | same | copied 1:1 |
| `uriSchemes`,`routes`,`useCases`,`flowExample`,`requires`,`keywords` | same | copied 1:1 |
| `install` | `install` | copied 1:1 |
| `docsUrl` | `docsUrl` | copied 1:1 |
| `provenance`,`publisher`,`adapterKinds` | same | copied for trust-aware clients |
| `icon`,`readme` | same when present | currently folder-relative metadata |

The generator validates:

- required fields,
- folder name equals `id`,
- route schemes are listed in `uriSchemes`,
- `status` is `available` or `planned`,
- `provenance` is `verified` or `community`,
- community connectors declare `publisher`,
- community connectors must declare `adapterKinds` explicitly (use `[]` for
  "registers no executors") — an absent declaration is itself a rejection, since
  the allowlist can only protect against kinds the manifest actually claims,
- every declared community `adapterKind` is on the allowlist.

## Trust model

Folder-per-connector solves review mechanics: less merge conflict pressure,
clear ownership and a natural place for docs/assets. It does not by itself solve
the supply-chain risk of executable installers and adapter kinds.

Trust fields are first-class:

- `provenance: verified` - maintained or reviewed by if-uri,
- `provenance: community` - third-party connector, must declare `publisher`.

Before accepting external publishers, CI should enforce an adapter-kind
allowlist by provenance:

| adapter kind | community? |
|--------------|------------|
| `http-service`, `planfile-task`, `domain-monitor` | allowed |
| `argv-template`, `shell-template`, `command` | verified only |

Rationale: arbitrary shell or argv templates from a third-party catalog entry
are an execution surface.

## Current order of work

1. Done: public connector pages and machine endpoints.
2. Done: folder manifests plus generated aggregate.
3. Done: generator check and snapshot check in smoke tests.
4. Done: adapter-kind allowlist enforcement for community manifests.
5. Next: CODEOWNERS for `data/connectors/<id>/`.
6. Next: submit connector flow that validates a manifest before it reaches the hub.
