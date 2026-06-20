# Author: Tom Sapletta · https://tom.sapletta.com
# Part of the ifURI solution.

from __future__ import annotations

import argparse
import json
import sys

from .core import connector_manifest, run, urirun_bindings


def emit(payload: dict) -> None:
    print(json.dumps(payload, indent=2, ensure_ascii=False, sort_keys=True))


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(prog="__CLI__")
    sub = parser.add_subparsers(dest="command", required=True)
    r = sub.add_parser("run", help="Run the connector and emit JSON")
    r.add_argument("target")
    r.add_argument("--timeout", type=float, default=10.0)
    sub.add_parser("manifest", help="Emit the connect.ifuri.com connector manifest")
    sub.add_parser("bindings", help="Emit urirun v2 bindings")

    args = parser.parse_args(argv)
    if args.command == "run":
        result = run(args.target, timeout=args.timeout)
        emit(result)
        return 0 if result.get("ok") else 2
    if args.command == "manifest":
        emit(connector_manifest()); return 0
    if args.command == "bindings":
        emit(urirun_bindings()); return 0
    return 1


if __name__ == "__main__":
    sys.exit(main())
