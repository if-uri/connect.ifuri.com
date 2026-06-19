#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BASE_URL="${CONNECT_HUB_BASE:-}"

cd "$ROOT"

find . -name '*.php' -print -exec php -l {} \; >/tmp/connect-ifuri-php-lint.log
python3 -m json.tool data/connectors.json >/tmp/connect-ifuri-connectors.json
php -r 'require "lib/hub.php"; echo hub_installer_script(["planfile","namecheap-dns"]);' >/tmp/connect-ifuri-install.sh
bash -n /tmp/connect-ifuri-install.sh

if [[ -n "$BASE_URL" ]]; then
  python3 - "$BASE_URL" <<'PY'
import json
import sys
import urllib.request

base = sys.argv[1].rstrip("/")
checks = [
    ("/", "text/html"),
    ("/connectors.json", "application/json"),
    ("/registry.json", "application/json"),
    ("/install?connectors=planfile,namecheap-dns", "text/x-shellscript"),
]
for path, expected in checks:
    with urllib.request.urlopen(base + path, timeout=20) as response:
        body = response.read().decode("utf-8")
        content_type = response.headers.get("content-type", "")
    assert response.status == 200, (path, response.status)
    assert expected in content_type, (path, content_type)
    if path.endswith(".json"):
        json.loads(body)
    if path.startswith("/install"):
        assert "planfile>=0.1.103" in body
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
