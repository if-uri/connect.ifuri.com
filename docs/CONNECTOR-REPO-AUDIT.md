# Connector Repository Audit

The public connector catalog is generated from per-connector source manifests:

```text
data/connectors/*/manifest.json -> tools/build_catalog.py -> data/connectors.json
```

`data/connectors.json` is the machine-readable catalog served by the hub at
`/connectors.json`. Registry, search and connector detail JSON projections read
from the same generated catalog.

## Run The Audit

From the repository root:

```bash
python tools/audit_connector_repos.py
```

By default the script scans the local organization checkout directory at `..`
for repositories named `urirun-connector-*`. If GitHub CLI is available, it also
queries public, non-archived repositories from `if-uri`:

```bash
gh repo list if-uri --limit 500 --visibility public --json name,nameWithOwner,isArchived
```

The script excludes `urirun-connectors-toolkit` because it is a toolkit, not a
connector repository.

The audit writes a JSON report to:

```text
data/connector-repo-audit.json
```

## Check Mode

Use strict mode for CI or release checks:

```bash
python tools/audit_connector_repos.py --check
```

If connector repositories are still being tagged gradually, treat missing tags
as warnings:

```bash
python tools/audit_connector_repos.py --check --warn-missing-tags
```

## Version Contract

Each connector repository should have:

- `VERSION` with a semver value such as `0.1.0` or `0.1.0-beta.1`.
- `pyproject.toml` with the same `project.version` when the connector is a
  Python package.
- `data/connectors/{id}/manifest.json` with the same `version`.
- `data/connectors.json` generated with the same `version`.
- Git tag `vX.Y.Z`, matching the `VERSION` value.

The audit only reports mismatches and missing files/tags. It does not create
tags, push commits or modify connector repositories.
