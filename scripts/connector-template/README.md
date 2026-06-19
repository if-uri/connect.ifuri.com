# urirun-connector-__ID__

__NAME__ connector for [ifURI](https://ifuri.com) / [urirun](https://github.com/tellmesh/urirun).

- Declares `__SCHEME__://` URI routes with `@urirun.v2.uri_command`.
- `connector.manifest.json` is the connect.ifuri.com catalog entry (validated by schema).
- CLI: `__CLI__ run <target>` · `__CLI__ manifest` · `__CLI__ bindings`.

## Quick start
```bash
pip install -e .
make smoke        # bindings -> urirun validate/compile/run -> MCP tools
```

Implement `run()` in `__PKG__/core.py`. Submit the manifest at
https://connect.ifuri.com/submit
