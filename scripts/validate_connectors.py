#!/usr/bin/env python3
# Author: Tom Sapletta · https://tom.sapletta.com
# Part of the ifURI solution.

"""Validate connector manifests + the aggregated catalog against the JSON schemas.

- data/connectors/<id>/manifest.json  -> schema/connector.schema.json (+ id == folder)
- data/connectors.json (catalog)      -> schema/connectors.schema.json
- catalog ids and folder manifest ids must be the same set.

Exit 0 when clean, 1 on any error. Used by CI (ci-deploy.yml) and locally.
"""
import glob
import json
import os
import sys

from jsonschema import Draft202012Validator

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def load(rel):
    with open(os.path.join(ROOT, rel), encoding="utf-8") as fh:
        return json.load(fh)


def main() -> int:
    errors = 0
    manifest_v = Draft202012Validator(load("schema/connector.schema.json"))
    manifest_ids = set()

    for path in sorted(glob.glob(os.path.join(ROOT, "data/connectors/*/manifest.json"))):
        folder = os.path.basename(os.path.dirname(path))
        with open(path, encoding="utf-8") as fh:
            data = json.load(fh)
        manifest_ids.add(data.get("id"))
        if data.get("id") != folder:
            print(f"FAIL {folder}: id '{data.get('id')}' != folder name"); errors += 1
        for e in sorted(manifest_v.iter_errors(data), key=lambda e: list(e.path)):
            print(f"FAIL {folder}: {list(e.path)} -> {e.message}"); errors += 1

    catalog = load("data/connectors.json")
    for e in sorted(Draft202012Validator(load("schema/connectors.schema.json")).iter_errors(catalog),
                    key=lambda e: list(e.path)):
        print(f"FAIL connectors.json: {list(e.path)} -> {e.message}"); errors += 1

    catalog_ids = {c.get("id") for c in catalog.get("connectors", [])}
    for cid in sorted(catalog_ids - manifest_ids):
        print(f"FAIL catalog entry '{cid}' has no data/connectors/{cid}/manifest.json"); errors += 1
    for cid in sorted(manifest_ids - catalog_ids):
        print(f"FAIL manifest '{cid}' is missing from the catalog (connectors.json)"); errors += 1

    print(f"{len(manifest_ids)} manifest(s), {len(catalog_ids)} catalog entr(ies), {errors} error(s)")
    return 1 if errors else 0


if __name__ == "__main__":
    sys.exit(main())
