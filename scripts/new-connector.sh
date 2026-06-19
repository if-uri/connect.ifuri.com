#!/usr/bin/env bash
# Scaffold a new urirun connector package from the template.
# Usage: scripts/new-connector.sh <id> [scheme] [Name] [target-dir]
#   <id>      kebab-case connector id (e.g. weather-now)
#   [scheme]  URI scheme (default: id with dashes removed)
#   [Name]    display name (default: Title Case of id)
#   [target]  output dir (default: ../<repo-root>/urirun-connector-<id>)
set -euo pipefail
ID="${1:?usage: new-connector.sh <id> [scheme] [Name] [target-dir]}"
SCHEME="${2:-${ID//-/}}"
NAME="${3:-$(python3 -c 'import sys;print(sys.argv[1].replace("-"," ").title())' "$ID")}"
PKG="urirun_connector_${ID//-/_}"
CLI="urirun-${ID}"
SELF="$(cd "$(dirname "$0")" && pwd)"
TPL="$SELF/connector-template"
DST="${4:-$(cd "$SELF/../.." && pwd)/urirun-connector-$ID}"

[ -d "$TPL" ] || { echo "missing template: $TPL" >&2; exit 1; }
[ -e "$DST" ] && { echo "target already exists: $DST" >&2; exit 1; }

cp -r "$TPL" "$DST"
mv "$DST/pkg" "$DST/$PKG"
mv "$DST/pyproject.toml.tmpl" "$DST/pyproject.toml"
# substitute placeholders (order: longer keys first not needed, all distinct)
find "$DST" -type f -print0 | while IFS= read -r -d '' f; do
  sed -i \
    -e "s/__PKG__/$PKG/g" \
    -e "s/__CLI__/$CLI/g" \
    -e "s/__SCHEME__/$SCHEME/g" \
    -e "s/__ID__/$ID/g" \
    -e "s/__NAME__/$NAME/g" \
    "$f"
done
echo "created connector package: $DST"
echo "  id=$ID scheme=$SCHEME pkg=$PKG cli=$CLI name='$NAME'"
echo "  next: cd '$DST' && make test   (then submit manifest at https://connect.ifuri.com/submit)"
