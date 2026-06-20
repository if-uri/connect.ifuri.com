---
name: Connector submission
about: Submit a URI connector for the connect.ifuri.com catalog
title: 'Connector: <id>'
labels: connector-submission
---

<!--
Build and validate your manifest at https://connect.ifuri.com/submit
(it checks against the same policy as CI). Paste the validated JSON below.
-->

## Connector manifest

```json
PASTE_YOUR_VALIDATED_MANIFEST_JSON_HERE
```

## Checklist

- [ ] The manifest validates at https://connect.ifuri.com/submit (or `python3 scripts/validate_connectors.py`).
- [ ] The connector has a public GitHub repo with a README and CHANGELOG.
- [ ] An `available` connector pins a release tag in `install.pipSpec` (not `@main`).
- [ ] There is a smoke/Docker test command (e.g. `make docker-test`).
- [ ] `docsUrl`, `uriSchemes`, `routes` and at least one example are filled in.

A maintainer will copy the manifest into `data/connectors/<id>/manifest.json`,
run `make build-catalog`, and open a PR.
