# TODO

## Connector hub roadmap

- [x] Add a visible connector category page for browser, monitoring, task,
      DNS, time and transport connectors. (`/categories`)
- [x] Add version compatibility checks between connector manifests and the
      current `urirun` GitHub release tag. (`scripts/check_connectors.py` —
      pins + runtime baseline)
- [x] Add an automated check that each listed connector has a reachable GitHub
      repo, README, changelog and smoke-test command.
      (`scripts/check_connectors.py --online`)
- [x] Add a public example linking hub install commands to
      `if-uri/examples/11-novnc_lan_flow` and `12-full_e2e_connect_lab`.
      (LAN-demo section on `/categories`)
- [x] Keep `search.json`, `llms.txt`, `sitemap.xml` and generated connector
      pages synchronized in CI. (CI runs `build_catalog --check` +
      `check_connectors`; search/llms/sitemap are generated from the catalog)
- [x] Add a submission flow that can open a GitHub issue or PR with a validated
      connector manifest. (`/submit` "Open a GitHub issue" + issue template)

## Related resources

- Runtime: https://github.com/if-uri/urirun
- Docs: https://github.com/if-uri/docs
- Examples: https://github.com/if-uri/examples
- App/host: https://github.com/if-uri/app
- Work summary: https://github.com/if-uri/docs/blob/main/work-summary-2026-06-20.md
