# Submit a connector

Create a folder:

```text
data/connectors/<id>/manifest.json
```

Minimum checklist:

- `id` equals the folder name,
- `status` is `available` or `planned`,
- every route scheme is listed in `uriSchemes`,
- `install.mode` is declared,
- `provenance` is `verified` or `community`,
- `community` connectors include `publisher`,
- `community` connectors use only allowlisted `adapterKinds`.

Build and test:

```bash
python3 tools/build_catalog.py
bash tests/smoke.sh
```

Validate before editing the catalog:

```bash
curl -fsS https://connect.ifuri.com/validate-connector \
  -H 'Content-Type: application/json' \
  --data @data/connectors/<id>/manifest.json
```

Review the generated pages:

```bash
php -S 127.0.0.1:8099 router.php
open http://127.0.0.1:8099/submit
open http://127.0.0.1:8099/connectors/<id>
open http://127.0.0.1:8099/connectors/<id>.json
open http://127.0.0.1:8099/search.json
```

Public outputs produced from the same manifest:

- `/connectors/<id>`
- `/connectors/<id>.json`
- `/connectors.json`
- `/registry.json`
- `/search.json`
- `/llms.txt`

Do not add arbitrary shell or argv execution to a community connector without a
separate trust review. That is executable supply-chain surface.
