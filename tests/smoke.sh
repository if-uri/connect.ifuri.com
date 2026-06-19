#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BASE_URL="${CONNECT_HUB_BASE:-}"

cd "$ROOT"

find . -path './.git' -prune -o -name '*.php' -print -exec php -l {} \; >/tmp/connect-ifuri-php-lint.log
python3 -m json.tool data/connectors.json >/tmp/connect-ifuri-connectors.json
python3 -m json.tool data/catalog.meta.json >/tmp/connect-ifuri-catalog-meta.json
python3 -m json.tool schema/connector.schema.json >/tmp/connect-ifuri-connector-schema.json
python3 -m json.tool schema/connectors.schema.json >/tmp/connect-ifuri-schema.json
python3 tools/build_catalog.py --check
python3 tests/policy_test.py
php tests/snapshot_test.php

python3 - <<'PY'
import json
import re
from pathlib import Path

catalog = json.loads(Path("data/connectors.json").read_text())
ids = set()
assert catalog.get("site", {}).get("baseUrl", "").startswith("https://"), "site.baseUrl must be absolute https"
for connector in catalog["connectors"]:
    cid = connector["id"]
    assert re.match(r"^[a-z0-9][a-z0-9._-]*$", cid), cid
    assert cid not in ids, f"duplicate connector id: {cid}"
    ids.add(cid)
    assert connector["status"] in {"available", "planned"}, cid
    assert len(connector["summary"]) >= 40, cid
    assert len(connector["description"]) >= len(connector["summary"]), cid
    assert connector["uriSchemes"], cid
    assert connector["routes"], cid
    for route in connector["routes"]:
        assert "://" in route, (cid, route)
        assert route.split("://", 1)[0] in connector["uriSchemes"], (cid, route)
    assert connector.get("useCases"), cid
    assert connector.get("examples"), cid
print(f"validated {len(ids)} connectors")
PY

connector_ids="$(python3 - <<'PY'
import json
print(" ".join(c["id"] for c in json.load(open("data/connectors.json"))["connectors"]))
PY
)"

for id in $connector_ids; do
  php -r 'require "lib/hub.php"; echo hub_installer_script([$argv[1]]);' "$id" >"/tmp/connect-ifuri-install-$id.sh"
  bash -n "/tmp/connect-ifuri-install-$id.sh"
done

php -r 'require "lib/hub.php"; echo hub_installer_script(["planfile","namecheap-dns"]);' >/tmp/connect-ifuri-install.sh
bash -n /tmp/connect-ifuri-install.sh

PORT="$(python3 - <<'PY'
import socket
s = socket.socket()
s.bind(("127.0.0.1", 0))
print(s.getsockname()[1])
s.close()
PY
)"
php -S "127.0.0.1:${PORT}" router.php >/tmp/connect-ifuri-server.log 2>&1 &
SERVER_PID="$!"
trap 'kill "$SERVER_PID" >/dev/null 2>&1 || true' EXIT

python3 - "http://127.0.0.1:${PORT}" <<'PY'
import json
import sys
import time
import urllib.error
import urllib.request
from urllib.error import URLError

base = sys.argv[1].rstrip("/")

def fetch(path):
    with urllib.request.urlopen(base + path, timeout=20) as response:
        body = response.read().decode("utf-8")
        return response.status, response.headers.get("content-type", ""), body

last = None
for _ in range(60):
    try:
        status, _, _ = fetch("/")
        if status == 200:
            break
    except Exception as exc:
        last = exc
        time.sleep(0.2)
else:
    raise SystemExit(f"local PHP server did not start: {last}")

catalog = json.loads(open("data/connectors.json").read())
paths = [
    ("/", "text/html"),
    ("/submit", "text/html"),
    ("/connectors.json", "application/json"),
    ("/registry.json", "application/json"),
    ("/search.json", "application/json"),
    ("/connectors/planfile.json", "application/json"),
    ("/install?connectors=planfile,namecheap-dns", "text/x-shellscript"),
    ("/sitemap.xml", "application/xml"),
    ("/robots.txt", "text/plain"),
    ("/llms.txt", "text/plain"),
    ("/assets/social-card.svg", "image/svg+xml"),
]
for path, expected in paths:
    status, content_type, body = fetch(path)
    assert status == 200, (path, status)
    assert expected in content_type, (path, content_type)
    if path.endswith(".json"):
        json.loads(body)
    if path == "/":
        assert 'application/ld+json' in body
        assert 'property="og:title"' in body
        assert 'name="twitter:card"' in body
        assert 'https://get.ifuri.com' in body
    if path == "/submit":
        assert 'id="connectorBuilder"' in body
        assert 'id="validateManifest"' in body
    if path == "/install?connectors=planfile,namecheap-dns":
        assert "planfile>=0.1.103" in body
    if path == "/sitemap.xml":
        assert "https://connect.ifuri.com/connectors/planfile" in body
    if path == "/robots.txt":
        assert "Sitemap: https://connect.ifuri.com/sitemap.xml" in body
    if path == "/llms.txt":
        assert "## Connectors" in body
        assert "https://get.ifuri.com" in body

registry = json.loads(fetch("/registry.json")[2])
assert len(registry["connectors"]) == len(catalog["connectors"])
search = json.loads(fetch("/search.json")[2])
assert search["version"] == "ifuri.search.v1"
assert search["recordCount"] >= len(catalog["connectors"])
assert any(item["type"] == "route" and item["route"] == "dns://host/records/command/plan" for item in search["records"])

for connector in catalog["connectors"]:
    path = "/connectors/" + connector["id"]
    status, content_type, body = fetch(path)
    assert status == 200, path
    assert "text/html" in content_type, (path, content_type)
    assert connector["name"] in body, path
    assert 'application/ld+json' in body, path
    assert 'property="og:title"' in body, path
    assert 'data-tab-target="routes"' in body, path
    for route in connector["routes"]:
        assert route in body, (path, route)
    assert f"https://connect.ifuri.com/connectors/{connector['id']}" in fetch("/sitemap.xml")[2]
    manifest_path = "/connectors/" + connector["id"] + ".json"
    status, content_type, body = fetch(manifest_path)
    assert status == 200, manifest_path
    assert "application/json" in content_type, (manifest_path, content_type)
    manifest = json.loads(body)
    assert manifest["version"] == "ifuri.connector.v1"
    assert manifest["connector"]["id"] == connector["id"]
    assert manifest["registryEntry"]["hubUrl"].endswith("/connectors/" + connector["id"])
    assert manifest["registryEntry"]["manifestUrl"].endswith(manifest_path)

valid_manifest = {
    "id": "demo-connector",
    "name": "Demo Connector",
    "status": "planned",
    "category": "Test",
    "summary": "A valid connector manifest used by the smoke test.",
    "description": "A valid connector manifest used by the smoke test to prove the public validation endpoint works.",
    "uriSchemes": ["demo"],
    "routes": ["demo://host/resource/query/status"],
    "install": {"mode": "planned", "pipSpec": "urirun-connector-demo"},
    "adapterKinds": ["http-service"],
    "provenance": "community",
    "publisher": {"name": "Smoke Test"},
}
request = urllib.request.Request(
    base + "/validate-connector",
    data=json.dumps(valid_manifest).encode("utf-8"),
    headers={"content-type": "application/json"},
    method="POST",
)
with urllib.request.urlopen(request, timeout=20) as response:
    validation = json.loads(response.read().decode("utf-8"))
assert validation["ok"] is True

invalid_manifest = dict(valid_manifest)
invalid_manifest["adapterKinds"] = ["shell-template"]
request = urllib.request.Request(
    base + "/validate-connector",
    data=json.dumps(invalid_manifest).encode("utf-8"),
    headers={"content-type": "application/json"},
    method="POST",
)
try:
    urllib.request.urlopen(request, timeout=20)
except urllib.error.HTTPError as exc:
    assert exc.code == 422
    rejected = json.loads(exc.read().decode("utf-8"))
    assert rejected["ok"] is False
    assert any(error["field"] == "adapterKinds" for error in rejected["errors"])
else:
    raise AssertionError("dangerous community adapterKind was accepted")
PY

if [[ -n "$BASE_URL" ]]; then
  python3 - "$BASE_URL" <<'PY'
import json
import sys
import urllib.error
import urllib.request

base = sys.argv[1].rstrip("/")
catalog = json.loads(open("data/connectors.json").read())
checks = [
    ("/", "text/html"),
    ("/submit", "text/html"),
    ("/connectors.json", "application/json"),
    ("/registry.json", "application/json"),
    ("/search.json", "application/json"),
    ("/connectors/planfile.json", "application/json"),
    ("/install?connectors=planfile,namecheap-dns", "text/x-shellscript"),
    ("/sitemap.xml", "application/xml"),
    ("/robots.txt", "text/plain"),
    ("/llms.txt", "text/plain"),
]
for connector in catalog["connectors"]:
    checks.append(("/connectors/" + connector["id"], "text/html"))
    checks.append(("/connectors/" + connector["id"] + ".json", "application/json"))

for path, expected in checks:
    with urllib.request.urlopen(base + path, timeout=25) as response:
        body = response.read().decode("utf-8")
        content_type = response.headers.get("content-type", "")
    assert response.status == 200, (path, response.status)
    assert expected in content_type, (path, content_type)
    if path.endswith(".json"):
        json.loads(body)
    if path.startswith("/install"):
        assert "planfile>=0.1.103" in body
    if path.startswith("/connectors/"):
        if path.endswith(".json"):
            payload = json.loads(body)
            assert payload["version"] == "ifuri.connector.v1"
        else:
            assert 'application/ld+json' in body
            assert 'property="og:title"' in body

valid_manifest = {
    "id": "public-smoke",
    "name": "Public Smoke",
    "status": "planned",
    "category": "Test",
    "summary": "A valid connector manifest used by the public smoke test.",
    "description": "A valid connector manifest used by the public smoke test to prove remote validation works.",
    "uriSchemes": ["public"],
    "routes": ["public://host/resource/query/status"],
    "install": {"mode": "planned", "pipSpec": "urirun-connector-public-smoke"},
    "adapterKinds": ["http-service"],
    "provenance": "community",
    "publisher": {"name": "Smoke Test"},
}
request = urllib.request.Request(
    base + "/validate-connector",
    data=json.dumps(valid_manifest).encode("utf-8"),
    headers={"content-type": "application/json"},
    method="POST",
)
with urllib.request.urlopen(request, timeout=25) as response:
    validation = json.loads(response.read().decode("utf-8"))
assert validation["ok"] is True
PY
fi

if [[ "${CONNECT_HUB_INSTALL_TEST:-0}" == "1" ]]; then
  if [[ -z "$BASE_URL" ]]; then
    echo "CONNECT_HUB_BASE is required for CONNECT_HUB_INSTALL_TEST=1" >&2
    exit 1
  fi
  tmp="$(mktemp -d)"
  python3 -m venv "$tmp/venv"
  PYTHON_BIN="$tmp/venv/bin/python" bash -c "curl -fsSL '$BASE_URL/install?connectors=planfile' | bash"
  "$tmp/venv/bin/python" - <<'PY'
import importlib.util
assert importlib.util.find_spec("urirun") is not None
assert importlib.util.find_spec("planfile") is not None
PY
  "$tmp/venv/bin/urirun" host task list --project "$tmp/project" --sprint all --json >/tmp/connect-ifuri-urirun-host-task.json
fi

echo "connect.ifuri.com smoke OK"
