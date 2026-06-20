#!/usr/bin/env python3
"""Reachability + version-compatibility checks for connector manifests.

Complements validate_connectors.py (JSON Schema) with policy checks:

- every connector declares docsUrl, uriSchemes, routes and at least one example;
- an `available` connector that ships a git package must pin a release tag
  (not a moving @main/@master/HEAD) so installs are reproducible against the
  runtime;
- `planned` connectors are checked leniently (no package required yet);
- with --online, the connector's GitHub repo plus README.md and CHANGELOG.md
  are reachable.

The current urirun runtime tag (--urirun-ref / URIRUN_REF, default v0.3.14) is
reported as the compatibility baseline so connector pins can be reconciled with
the runtime release.

Exit 0 when clean, 1 on errors. Warnings only fail the run with --strict.
"""
import argparse
import glob
import json
import os
import re
import sys
import urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
GIT_RE = re.compile(r"git\+(https://[^@#\s]+?)(?:\.git)?(?:@([^#\s]+))?(?:#|$|\s)")
MOVING = {"main", "master", "head", "HEAD"}


def reachable(url: str, timeout: float = 8.0) -> bool:
    req = urllib.request.Request(url, method="GET", headers={"User-Agent": "ifuri-connector-check"})
    try:
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            return 200 <= resp.status < 400
    except Exception:
        return False


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--online", action="store_true", help="HTTP-check repo/README/CHANGELOG")
    ap.add_argument("--strict", action="store_true", help="treat warnings as errors")
    ap.add_argument("--urirun-ref", default=os.environ.get("URIRUN_REF", "v0.3.14"))
    args = ap.parse_args()

    errors = 0
    warnings = 0
    available = 0
    planned = 0

    print(f"urirun runtime baseline: {args.urirun_ref}")
    for path in sorted(glob.glob(os.path.join(ROOT, "data/connectors/*/manifest.json"))):
        cid = os.path.basename(os.path.dirname(path))
        with open(path, encoding="utf-8") as fh:
            d = json.load(fh)
        status = d.get("status", "?")
        install = d.get("install") or {}
        pipspec = install.get("pipSpec", "") or ""
        git = GIT_RE.search(pipspec)

        def err(msg):
            nonlocal errors
            print(f"FAIL {cid}: {msg}")
            errors += 1

        def warn(msg):
            nonlocal warnings
            print(f"WARN {cid}: {msg}")
            warnings += 1

        # Required for every connector.
        if not d.get("docsUrl"):
            err("missing docsUrl (repo/docs link)")
        if not d.get("uriSchemes"):
            err("missing uriSchemes")
        if not d.get("routes"):
            err("missing routes")
        if not d.get("examples"):
            warn("no examples (used as the manifest-level smoke reference)")

        if status == "available":
            available += 1
            if git:
                repo, tag = git.group(1), git.group(2)
                if not tag:
                    err(f"available git package is not pinned to a tag: {pipspec!r}")
                elif tag.lower() in MOVING:
                    err(f"available git package pins a moving ref @{tag}; pin a release tag")
                else:
                    print(f"ok   {cid}: {status}, pinned @{tag}")
                    if args.online:
                        if not reachable(repo):
                            err(f"repo not reachable: {repo}")
                        for fname in ("README.md", "CHANGELOG.md"):
                            raw = repo.replace("github.com", "raw.githubusercontent.com") + f"/{tag}/{fname}"
                            if not reachable(raw):
                                warn(f"{fname} not reachable at @{tag}")
            else:
                # available without a standalone git package (e.g. a urirun extra)
                print(f"ok   {cid}: {status}, bundled (no standalone git package)")
        elif status == "planned":
            planned += 1
            print(f"ok   {cid}: planned (package pending)")
        else:
            warn(f"unknown status '{status}'")

    print(f"\n{available} available, {planned} planned, {errors} error(s), {warnings} warning(s)")
    if errors or (args.strict and warnings):
        return 1
    return 0


if __name__ == "__main__":
    sys.exit(main())
