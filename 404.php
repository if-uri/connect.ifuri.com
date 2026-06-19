<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

http_response_code(404);
$site = hub_site();
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>404 — connect.ifuri.com</title>
  <meta name="robots" content="noindex">
  <meta name="theme-color" content="#4F46E5">
  <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
  <link rel="stylesheet" href="/assets/ifuri-tokens.css">
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
  <header class="site-header">
    <div class="wrap">
      <a class="brand" href="/">
        <img class="mark" src="/assets/mark.svg" alt="ifuri" width="34" height="34">
        <span>connect.ifuri.com</span>
      </a>
      <nav>
        <a href="/#connectors">Connectors</a>
        <a href="https://github.com/if-uri/connect.ifuri.com">GitHub</a>
      </nav>
    </div>
  </header>

  <main class="wrap error-page">
    <p class="eyebrow">404</p>
    <h1>Page not found</h1>
    <p class="lead">That URL is not part of the connector hub. It may have moved, or the connector id is wrong.</p>
    <div class="hero-actions">
      <a class="button primary" href="/">Back to catalog</a>
      <a class="button" href="/#connectors">Browse connectors</a>
    </div>
    <p class="muted" style="margin-top:24px">Machine endpoints: <a href="/connectors.json">/connectors.json</a> · <a href="/registry.json">/registry.json</a> · <a href="/search.json">/search.json</a></p>
  </main>

  <footer class="wrap footer">
    <span>Site v<?php echo hub_h(hub_version()); ?></span>
    <span>connect.ifuri.com</span>
    <?php if (!empty($site['ecosystem'])): ?>
      <nav class="footer-eco" aria-label="ifuri ecosystem">
        <?php foreach ($site['ecosystem'] as $eco): ?>
          <a href="<?php echo hub_h((string) $eco['url']); ?>"><?php echo hub_h((string) $eco['label']); ?></a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>
  </footer>
  <script src="/assets/app.js" defer></script>
  <script src="/assets/ifuri-ecobar.js" defer></script>
</body>
</html>
