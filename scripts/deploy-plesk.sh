#!/usr/bin/env bash
# Publish connect.ifuri.com (PHP app). No --delete: preserves server-side data/.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
REMOTE="${IFURI_DEPLOY_HOST:-ifuri@ifuri.com}"
DOCROOT="${IFURI_CONNECT_DOCROOT:-/var/www/vhosts/ifuri.com/connect.ifuri.com}"
echo "== deploy connect.ifuri.com =="
rsync -az --exclude '.git' --exclude 'tests' --exclude 'scripts' --exclude '.github' --exclude 'node_modules' \
  "${ROOT}/" "${REMOTE}:${DOCROOT}/"
ssh "${REMOTE}" "cd '${DOCROOT}' && find . -type d -exec chmod 755 {} + && find . -type f -exec chmod a+r {} +"
curl -fsSI "https://connect.ifuri.com/" | head -3 || true
echo done
