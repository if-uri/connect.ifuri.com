# urirun-connector-__ID__

__NAME__ connector for [ifURI](https://ifuri.com) / [urirun](https://github.com/if-uri/urirun).

- Declares `__SCHEME__://` URI routes with `@CONNECTOR.command(...)`.
- Uses `urirun.connector("__ID__", scheme="__SCHEME__")` so the connector id,
  default target and binding export are declared once.
- `connector.manifest.json` is the connect.ifuri.com catalog entry (validated by schema).
- CLI: `__CLI__ run <target>` · `__CLI__ manifest` · `__CLI__ bindings`.

## Quick start
```bash
pip install -e .
make test         # unit tests + bindings -> validate/compile/run -> MCP tools
```

Implement `run()` in `__PKG__/core.py`; keep `run_command()` as the URI binding
declaration unless you need more routes. Submit the manifest at
https://connect.ifuri.com/submit
