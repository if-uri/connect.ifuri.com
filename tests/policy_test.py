#!/usr/bin/env python3
"""Proves the community supply-chain gate in tools/build_catalog.py.

A gate without a test is a wish. These cases assert that validate_manifest()
actually rejects the dangerous shapes (disallowed adapter kind, undeclared
adapter kinds, missing publisher) and accepts the safe ones — independent of the
live catalog, so it keeps protecting even after every connector is migrated.

    python3 tests/policy_test.py
"""

from __future__ import annotations

import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "tools"))

from build_catalog import validate_manifest  # noqa: E402


def manifest(**overrides) -> dict:
    base = {
        "id": "demo",
        "name": "Demo Connector",
        "status": "available",
        "category": "Test",
        "summary": "A connector used only by the policy test suite.",
        "description": "Synthetic manifest for exercising the provenance gate.",
        "uriSchemes": ["http"],
        "routes": ["http://host/demo/query/status"],
        "install": {"mode": "bundled"},
        "provenance": "verified",
    }
    base.update(overrides)
    return base


PATH = Path("data/connectors/demo/manifest.json")


def expect_ok(label: str, data: dict) -> bool:
    try:
        validate_manifest(PATH, data)
        return True
    except Exception as exc:  # noqa: BLE001
        print(f"FAIL  {label}: expected accept, got {type(exc).__name__}: {exc}")
        return False


def expect_reject(label: str, data: dict) -> bool:
    try:
        validate_manifest(PATH, data)
        print(f"FAIL  {label}: expected reject, but it was accepted")
        return False
    except ValueError:
        return True


CASES = [
    ("verified needs no adapterKinds", lambda: expect_ok("verified-baseline", manifest())),
    (
        "community + allowlisted kind accepted",
        lambda: expect_ok(
            "community-allowed",
            manifest(provenance="community", publisher={"name": "Acme"}, adapterKinds=["http-service"]),
        ),
    ),
    (
        "community + empty adapterKinds accepted",
        lambda: expect_ok(
            "community-empty",
            manifest(provenance="community", publisher={"name": "Acme"}, adapterKinds=[]),
        ),
    ),
    (
        "community + shell-template REJECTED",
        lambda: expect_reject(
            "community-shell",
            manifest(provenance="community", publisher={"name": "Acme"}, adapterKinds=["shell-template"]),
        ),
    ),
    (
        "community + undeclared adapterKinds REJECTED",
        lambda: expect_reject(
            "community-undeclared",
            manifest(provenance="community", publisher={"name": "Acme"}),
        ),
    ),
    (
        "community without publisher REJECTED",
        lambda: expect_reject(
            "community-no-publisher",
            manifest(provenance="community", adapterKinds=[]),
        ),
    ),
]


def main() -> int:
    passed = 0
    for label, run in CASES:
        if run():
            passed += 1
            print(f"ok    {label}")
    failed = len(CASES) - passed
    print(f"\npolicy gate: {passed}/{len(CASES)} passed")
    return 1 if failed else 0


if __name__ == "__main__":
    raise SystemExit(main())
