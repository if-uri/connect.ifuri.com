#!/usr/bin/env python3
from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
META_PATH = ROOT / "data" / "catalog.meta.json"
CONNECTORS_DIR = ROOT / "data" / "connectors"
OUTPUT_PATH = ROOT / "data" / "connectors.json"
SCHEMA_PATH = ROOT / "schema" / "connector.schema.json"


CATALOG_FIELDS = [
    "id",
    "name",
    "status",
    "category",
    "summary",
    "description",
    "uriSchemes",
    "routes",
    "useCases",
    "examples",
    "flowExample",
    "requires",
    "install",
    "adapterKinds",
    "docsUrl",
    "keywords",
    "provenance",
    "publisher",
    "icon",
    "readme",
]


REQUIRED = {
    "id",
    "name",
    "status",
    "category",
    "summary",
    "description",
    "uriSchemes",
    "routes",
    "install",
    "provenance",
}


COMMUNITY_ADAPTER_ALLOWLIST = {
    "domain-monitor",
    "grpc-transport",
    "http-service",
    "local-function",
    "planfile-task",
}


def load_json(path: Path) -> dict:
    with path.open("r", encoding="utf-8") as handle:
        data = json.load(handle)
    if not isinstance(data, dict):
        raise ValueError(f"{path}: expected JSON object")
    return data


def validate_manifest(path: Path, manifest: dict) -> None:
    missing = sorted(REQUIRED - set(manifest))
    if missing:
        raise ValueError(f"{path}: missing required fields: {', '.join(missing)}")

    folder_id = path.parent.name
    if manifest["id"] != folder_id:
        raise ValueError(f"{path}: id must equal folder name {folder_id!r}")

    if manifest["status"] not in {"available", "planned"}:
        raise ValueError(f"{path}: unsupported status {manifest['status']!r}")

    if manifest["provenance"] not in {"verified", "community"}:
        raise ValueError(f"{path}: unsupported provenance {manifest['provenance']!r}")

    if manifest["provenance"] == "community":
        if not manifest.get("publisher"):
            raise ValueError(f"{path}: community connector requires publisher")
        # Require an explicit adapterKinds declaration: the allowlist can only
        # protect against kinds the manifest actually claims. A silent omission
        # would let a third party register executors the gate never sees, so an
        # absent key is itself a rejection (use [] for "registers no executors").
        if not isinstance(manifest.get("adapterKinds"), list):
            raise ValueError(
                f"{path}: community connector must declare adapterKinds "
                "(use [] if it registers no executors)"
            )
        for adapter_kind in manifest["adapterKinds"]:
            if adapter_kind not in COMMUNITY_ADAPTER_ALLOWLIST:
                raise ValueError(
                    f"{path}: community connector cannot use adapterKind {adapter_kind!r}; "
                    "review the allowlist before publishing"
                )

    schemes = set(manifest.get("uriSchemes") or [])
    if not schemes:
        raise ValueError(f"{path}: uriSchemes cannot be empty")

    for route in manifest.get("routes") or []:
        if "://" not in route:
            raise ValueError(f"{path}: route is not a URI: {route}")
        scheme = route.split("://", 1)[0]
        if scheme not in schemes:
            raise ValueError(f"{path}: route {route} uses scheme outside uriSchemes")


def public_connector(manifest: dict) -> dict:
    return {key: manifest[key] for key in CATALOG_FIELDS if key in manifest}


def schema_validator():
    """Draft 2020-12 validator for the formal connector manifest schema.

    Complements validate_manifest() (cross-field rules it can't express, e.g.
    id == folder name) by enforcing the structural contract: additionalProperties,
    maxLength, enums and patterns declared in schema/connector.schema.json.
    """
    try:
        import jsonschema
    except ModuleNotFoundError as exc:  # pragma: no cover
        raise RuntimeError(
            "jsonschema is required for manifest validation; "
            "install it with `pip install jsonschema` (CI: apt python3-jsonschema)"
        ) from exc
    return jsonschema.Draft202012Validator(load_json(SCHEMA_PATH))


def validate_schema(validator, path: Path, manifest: dict) -> None:
    errors = sorted(validator.iter_errors(manifest), key=lambda e: list(e.path))
    if errors:
        err = errors[0]
        loc = "/".join(str(p) for p in err.path) or "<root>"
        raise ValueError(f"{path}: schema violation at {loc}: {err.message}")


def build_catalog() -> dict:
    meta = load_json(META_PATH)
    validator = schema_validator()
    manifests: dict[str, dict] = {}
    for path in sorted(CONNECTORS_DIR.glob("*/manifest.json")):
        manifest = load_json(path)
        validate_manifest(path, manifest)
        validate_schema(validator, path, manifest)
        manifests[manifest["id"]] = manifest

    order = [str(item) for item in meta.get("connectorOrder", [])]
    ordered_ids = [item for item in order if item in manifests]
    ordered_ids.extend(sorted(set(manifests) - set(ordered_ids)))

    catalog = {key: value for key, value in meta.items() if key != "connectorOrder"}
    catalog["connectors"] = [public_connector(manifests[connector_id]) for connector_id in ordered_ids]
    return catalog


def encode(data: dict) -> str:
    return json.dumps(data, ensure_ascii=False, indent=2) + "\n"


def main() -> int:
    parser = argparse.ArgumentParser(description="Build data/connectors.json from connector manifests.")
    parser.add_argument("--check", action="store_true", help="fail if data/connectors.json is not up to date")
    args = parser.parse_args()

    try:
        rendered = encode(build_catalog())
    except Exception as exc:
        print(f"build_catalog: {exc}", file=sys.stderr)
        return 1

    if args.check:
        current = OUTPUT_PATH.read_text(encoding="utf-8") if OUTPUT_PATH.exists() else ""
        if current != rendered:
            print("data/connectors.json is out of date; run python3 tools/build_catalog.py", file=sys.stderr)
            return 1
        return 0

    OUTPUT_PATH.write_text(rendered, encoding="utf-8")
    print(f"wrote {OUTPUT_PATH.relative_to(ROOT)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
