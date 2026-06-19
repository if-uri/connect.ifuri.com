# Connector catalog architecture

## Where we are (and why it's correct)

The source of truth is a single **`data/connectors.json`**. At 7 first-party
connectors this is the right call: atomic, one fetch, trivial to validate, **no
build step**. The served machine endpoints are derived from it:

- `GET /connectors.json` → the catalog verbatim (`hub_catalog()`).
- `GET /registry.json` → a projection with a volatile `generatedAt` (`hub_registry()`).
- `GET /install?connectors=…` → a shell installer for the selected ids.

Do **not** split into folders yet. Premature.

## The invariant (already guarded)

Whatever the internal layout, **the served bytes of `/connectors.json` and
`/registry.json` must not change** unless the change is intentional. That is what
makes a future "source = folders, served = generated aggregate" migration safe and
reversible — the ifuri app never sees the seam.

This is **locked by a test, today**: `tests/snapshot_test.php` compares the served
output (registry `generatedAt` normalized) against `tests/golden/*`.

```bash
php tests/snapshot_test.php           # CI gate — fails on any contract drift
php tests/snapshot_test.php --update  # only after an *intentional* catalog change
```

Green snapshot == contract untouched. Run it after editing `data/connectors.json`
or `lib/hub.php`; if the diff is intended, `--update` and commit the refreshed golden.

## Migration trigger (not a calendar, an event)

Move to folders the moment **either** happens — not before:

1. a connector needs its **own assets** (icon, long README, screenshots, example
   payload files), or
2. an **external publisher** wants to contribute a connector.

Until then the single file wins.

## Target layout — design for assets, get publishers for free

Per the priority decision: lay out for **assets-per-connector**. It is the richer
superset; external publishers are the *same* layout plus process (CODEOWNERS, CI,
trust policy), not a different one.

```
data/connectors/<id>/
  manifest.json        # source of truth — schema/connector.schema.json
  icon.svg             # optional, folder-only asset
  README.md            # optional long-form docs
  examples/
    *.json             # optional example payloads, referenced by examples[].payloadFile
```

### Field mapping (`manifest.json` → `connectors.json` entry)

`manifest.json` is a **superset** of one catalog entry. Aggregation:

| manifest field | catalog entry | rule |
|----------------|---------------|------|
| `id` | `id` | must equal folder name (CI-checked) |
| `name`,`status`,`category`,`summary`,`description` | same | copied 1:1 |
| `uriSchemes`,`routes`,`useCases`,`flowExample`,`requires`,`keywords` | same | copied 1:1 |
| `install` | `install` | copied 1:1 |
| `docsUrl` | `docsUrl` | copied 1:1 |
| `examples[].payloadFile` | `examples[].payload` | file inlined; `payloadFile` dropped |
| `icon` (folder-relative) | — | resolved to a hub URL; emitted only if present |
| `readme` | — | rendered into the detail page; not in catalog JSON |
| `provenance`,`publisher`,`adapterKinds` | — | trust metadata; surfaced as a badge, not part of the install contract |

**Byte-compat rule:** for the current 7 connectors, none carry `icon`/`readme`/
`provenance`, so the generated `connectors.json` is identical to today's — the
snapshot test passes unchanged. New fields appear only when a connector adds them,
which *is* an intentional change (regenerate goldens then).

### Aggregation: runtime glob vs build step

Two options, both keep the endpoints byte-identical:

- **Runtime glob** in `lib/hub.php`: `hub_catalog()` globs `data/connectors/*/manifest.json`,
  inlines assets, sorts deterministically, returns the same array shape. Zero build,
  fits the dependency-free Plesk model. Preferred while the catalog is small.
- **Build step** (`bin/build-catalog.php` → writes `data/connectors.json`): better if
  glob cost ever matters or you want the aggregate committed. The snapshot test doubles
  as the build's correctness check.

Either way: **deterministic ordering** (sort by `id`) so output is stable.

## Trust model — required *before* external publishers (independent of layout)

Folder-per-connector solves review *mechanics* (merge conflicts, CODEOWNERS). It does
**not** solve that a connector declares `adapterKinds` + an `install` surface and the
hub serves `/install` — that is executable / supply-chain surface. Stand this up
**before** the first outside PR:

1. **Schema gate in CI** — every `manifest.json` validated against
   `schema/connector.schema.json`; reject anything malformed or with an unknown
   `adapter kind`. A binding without a declared schema does not merge.
2. **Adapter-kind allowlist by provenance** — `verified` connectors may use any kind;
   `community` connectors are restricted, e.g.

   | kind | community? |
   |------|-----------|
   | `http-service`, `planfile-task`, `domain-monitor` | allowed |
   | `argv-template`, `shell-template`, `command` | **verified only** |

   Rationale: arbitrary shell/argv from a third party is RCE-by-catalog.
3. **Provenance split** — `verified` vs `community` is a first-class field, shown as a
   UI badge and carried in `/registry.json`. Users (and the ifuri app) decide what they
   trust. Default the app to `verified`-only unless the user opts in.
4. **CODEOWNERS** per `data/connectors/<id>/` so the owning team reviews changes to its
   own connector; if-uri maintainers own the schema, allowlist and `verified` tier.

PyPI / npm / Terraform Registry learned this *after* incidents. Cheaper to have the
model before the first foreign connector lands than to retrofit it.

## Order of work (all layout-independent except the last)

1. ✅ Snapshot test + goldens (`tests/snapshot_test.php`) — the safety net. **Done.**
2. CI workflow: `php -l`, snapshot test, `manifest.json` schema validation.
3. Adapter-kind allowlist + provenance enforcement in CI.
4. Only then: flip `hub_catalog()` to glob folders, migrating one connector as the
   pattern. Snapshot stays green → done, invisibly.
