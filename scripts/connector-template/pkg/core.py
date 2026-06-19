from __future__ import annotations

import json
from importlib import resources
from typing import Any

from urirun import v2

ROUTE = "__SCHEME__://host/__SCHEME__/query/run"
CONNECTOR_ID = "__ID__"


def _json_resource(name: str) -> dict[str, Any]:
    text = resources.files(__package__).joinpath(name).read_text(encoding="utf-8")
    data = json.loads(text)
    if not isinstance(data, dict):
        raise ValueError(f"{name} must contain a JSON object")
    return data


def connector_manifest() -> dict[str, Any]:
    return _json_resource("connector.manifest.json")


@v2.uri_command(ROUTE, meta={"label": "__NAME__", "connector": CONNECTOR_ID})
def run_command(target: str, timeout: float = 10.0) -> list[str]:
    """Declare the URI binding once; the function signature becomes the schema."""
    return ["__CLI__", "run", "{target}", "--timeout", "{timeout}"]


def urirun_bindings() -> dict[str, Any]:
    return v2.connector_bindings(connector=CONNECTOR_ID)


def run(target: str, timeout: float = 10.0) -> dict[str, Any]:
    # TODO: implement the connector's real behaviour and return JSON.
    return {"ok": True, "connector": CONNECTOR_ID, "target": target, "timeout": timeout}
