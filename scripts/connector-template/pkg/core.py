# Author: Tom Sapletta · https://tom.sapletta.com
# Part of the ifURI solution.

from __future__ import annotations

import json
from importlib import resources
from typing import Any

import urirun

ROUTE = "__SCHEME__://host/__SCHEME__/query/run"
CONNECTOR_ID = "__ID__"
CONNECTOR = urirun.connector(CONNECTOR_ID, scheme="__SCHEME__", meta={"label": "__NAME__"})


def _json_resource(name: str) -> dict[str, Any]:
    text = resources.files(__package__).joinpath(name).read_text(encoding="utf-8")
    data = json.loads(text)
    if not isinstance(data, dict):
        raise ValueError(f"{name} must contain a JSON object")
    return data


def connector_manifest() -> dict[str, Any]:
    return _json_resource("connector.manifest.json")


@CONNECTOR.command("__SCHEME__/query/run")
def run_command(target: str, timeout: float = 10.0) -> list[str]:
    """Declare the URI binding once; the function signature becomes the schema."""
    return ["__CLI__", "run", "{target}", "--timeout", "{timeout}"]


def urirun_bindings() -> dict[str, Any]:
    return CONNECTOR.bindings()


def run(target: str, timeout: float = 10.0) -> dict[str, Any]:
    # TODO: implement the connector's real behaviour and return JSON.
    return {"ok": True, "connector": CONNECTOR_ID, "target": target, "timeout": timeout}
