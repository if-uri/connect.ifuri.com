from __future__ import annotations

from __PKG__ import connector_manifest, run, urirun_bindings


def test_manifest_and_binding_share_route():
    manifest = connector_manifest()
    bindings = urirun_bindings()
    route = "__SCHEME__://host/__SCHEME__/query/run"

    assert manifest["id"] == "__ID__"
    assert route in manifest["routes"]
    assert list(bindings["bindings"]) == [route]
    assert bindings["bindings"][route]["meta"]["connector"] == "__ID__"


def test_run_returns_json_result():
    result = run("example", timeout=1.5)

    assert result["ok"] is True
    assert result["connector"] == "__ID__"
    assert result["target"] == "example"
    assert result["timeout"] == 1.5
