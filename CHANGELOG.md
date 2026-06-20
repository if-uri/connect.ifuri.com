# Changelog

## [Unreleased]

### Added
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
- Update the `planfile` catalog entry from bundled integration metadata to the
  external `urirun-connector-planfile` package, including full route coverage,
  GitHub pip spec, examples and refreshed generated snapshots.
- Update `domain-monitor` and `namecheap-dns` catalog entries to the external
  `urirun-connector-domain-monitor` package, including full route coverage,
  GitHub pip spec, examples and refreshed generated snapshots.
- Update `sqlite-context` catalog entry to the external
  `urirun-connector-sqlite-context` package, including full route coverage,
  GitHub pip spec, examples and refreshed generated snapshots.
