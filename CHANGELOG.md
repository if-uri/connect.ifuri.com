# Changelog

## [Unreleased] - 2026-07-14

### Fixed
- Fix relative-imports issues (ticket-9a2a0b15)
- Fix import-optimization issues (ticket-0c897afb)
- Fix no-relative-imports issues (ticket-14244e94)
- Fix ast-print-statements issues (ticket-1ae6b75e)
- Fix ast-missing-return-type issues (ticket-9597682d)
- Fix ruff-print-statements issues (ticket-c1c252bf)
- Fix smart-return-type issues (ticket-8db96da5)
- Fix string-concat-fstring issues (ticket-1962a78b)
- Fix magic-numbers issues (ticket-a428088f)
- Fix ai-boilerplate issues (ticket-475a3528)
- Fix import-optimization issues (ticket-50290cf1)
- Fix ast-unused-imports issues (ticket-cfbf7f89)
- Fix ast-sorted-imports issues (ticket-63726133)
- Fix import-optimization issues (ticket-9895c99c)
- Fix relative-imports issues (ticket-e77aadcf)
- Fix ast-unused-imports issues (ticket-fd486279)
- Fix ast-print-statements issues (ticket-f7c0afa5)
- Fix ruff-print-statements issues (ticket-ff567739)
- Fix ai-boilerplate issues (ticket-fc013543)
- Fix import-optimization issues (ticket-0c5cff9e)
- Fix no-relative-imports issues (ticket-74bd8373)
- Fix ast-print-statements issues (ticket-27554c94)
- Fix ast-missing-return-type issues (ticket-9cb6f4bb)
- Fix ruff-print-statements issues (ticket-85bb37ae)
- Fix smart-return-type issues (ticket-cc69b63e)
- Fix ai-boilerplate issues (ticket-72779074)
- Fix import-optimization issues (ticket-38fb6b3b)
- Fix ast-unused-imports issues (ticket-70e15952)
- Fix ast-sorted-imports issues (ticket-051333b4)
- Fix ast-print-statements issues (ticket-2804c524)
- Fix ruff-print-statements issues (ticket-f97ef168)
- Fix ai-boilerplate issues (ticket-d3839eea)
- Fix import-optimization issues (ticket-fe513d22)
- Fix ast-unused-imports issues (ticket-f9354cd9)
- Fix ast-sorted-imports issues (ticket-9a2e14d9)
- Fix ast-string-concat issues (ticket-3aa28510)
- Fix ast-print-statements issues (ticket-1613965b)
- Fix ruff-print-statements issues (ticket-6ec41fdc)
- Fix ruff-sorted-imports issues (ticket-01a33195)
- Fix string-concat-fstring issues (ticket-7e839902)
- Fix ai-boilerplate issues (ticket-d76d966e)
- Fix import-optimization issues (ticket-fd7a322f)
- Fix ast-unused-imports issues (ticket-fef45dc6)
- Fix ast-sorted-imports issues (ticket-0ee9e3c3)
- Fix ast-string-concat issues (ticket-b2f1d07c)
- Fix ast-print-statements issues (ticket-7e2d9f84)
- Fix ruff-print-statements issues (ticket-bb0dbf6b)
- Fix ruff-sorted-imports issues (ticket-c2973b5a)
- Fix string-concat-fstring issues (ticket-b95fd1fd)
- Fix magic-numbers issues (ticket-c95a7508)
- Fix ai-boilerplate issues (ticket-0761b69c)
- Fix import-optimization issues (ticket-be730d05)

## [Unreleased]

### Added
- Add structure-audit follow-up tasks for connector contract pages,
  compatibility badges, install bundles and matrix-test health history.
- Bilingual PL/EN UI driven by the shared ifuri ecobar `?lang=` signal
  (Polish default, English fallback) across `index.php`, `connector.php` and
  `submit.php`. UI strings live in a single `lib/i18n.php` catalog consumed via
  new `hub_lang()` / `hub_t()` / `hub_js_i18n()` helpers; client-side strings in
  `assets/app.js` read `window.CONNECT_I18N`. Connector catalog content
  (names, summaries) stays data-driven and untranslated.
- Render the shared cross-domain ifuri ecobar on connector detail pages so they
  match the homepage and submit page.
- Add repository-level TODO for connector hub roadmap work.
- Link the hub README to the current cross-repository work summary and related
  runtime, app, examples and connector repositories.

### Changed
- Document the hub as part of the broader `urirun` connector ecosystem rather
  than a standalone PHP site.
- Point connector templates and active runtime install snippets at
  `github.com/if-uri/urirun`.
- Document local `urirun connectors ...` dry-run commands next to public
  installer one-liners.
- Update the `planfile` catalog entry from bundled integration metadata to the
  external `urirun-connector-planfile` package, including full route coverage,
  GitHub pip spec, examples and refreshed generated snapshots.
- Update `domain-monitor` catalog entry to the external
  `urirun-connector-domain-monitor` package, including full route coverage,
  GitHub pip spec, examples and refreshed generated snapshots.
- Update `namecheap-dns` catalog entry to the dedicated external
  `urirun-connector-namecheap-dns` package, including full route coverage,
  GitHub pip spec, examples and refreshed generated snapshots.
- Update `sqlite-context` catalog entry to the external
  `urirun-connector-sqlite-context` package, including full route coverage,
  GitHub pip spec, examples and refreshed generated snapshots.
