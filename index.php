<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

$catalog = hub_catalog();
$connectors = hub_connectors();
$site = hub_site();
$available = hub_available_connectors();
$defaultIds = implode(',', array_column($available, 'id'));
$defaultInstall = hub_default_install_path();
$canonical = hub_url('/');
$socialImage = (string) ($site['image'] ?? hub_url('/assets/social-card.svg'));
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $site['title'] ?? 'ifuri Connect',
    'description' => $site['description'] ?? 'URI connector hub for ifuri and urirun.',
    'url' => $canonical,
    'mainEntity' => [
        '@type' => 'ItemList',
        'itemListElement' => array_map(
            static fn ($connector, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => hub_connector_url($connector),
                'name' => $connector['name'] ?? $connector['id'],
            ],
            $connectors,
            array_keys($connectors)
        ),
    ],
];
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo hub_h((string) ($site['title'] ?? 'connect.ifuri.com - connector hub')); ?></title>
  <meta name="description" content="<?php echo hub_h((string) ($site['description'] ?? 'Connector hub for ifuri and urirun.')); ?>">
  <meta name="keywords" content="<?php echo hub_h(hub_keywords()); ?>">
  <link rel="canonical" href="<?php echo hub_h($canonical); ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ifuri Connect">
  <meta property="og:title" content="<?php echo hub_h((string) ($site['title'] ?? 'ifuri Connect')); ?>">
  <meta property="og:description" content="<?php echo hub_h((string) ($site['description'] ?? 'URI connector hub for ifuri and urirun.')); ?>">
  <meta property="og:url" content="<?php echo hub_h($canonical); ?>">
  <meta property="og:image" content="<?php echo hub_h($socialImage); ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo hub_h((string) ($site['title'] ?? 'ifuri Connect')); ?>">
  <meta name="twitter:description" content="<?php echo hub_h((string) ($site['description'] ?? 'URI connector hub for ifuri and urirun.')); ?>">
  <meta name="twitter:image" content="<?php echo hub_h($socialImage); ?>">
  <meta name="theme-color" content="#4F46E5">
  <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
  <link rel="sitemap" type="application/xml" href="/sitemap.xml">
  <link rel="stylesheet" href="/assets/ifuri-tokens.css">
  <link rel="stylesheet" href="/assets/app.css">
  <script type="application/ld+json"><?php echo json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
</head>
<body>
  <header class="site-header">
    <div class="wrap">
      <a class="brand" href="/">
        <span class="mark">if</span>
        <span>connect.ifuri.com</span>
      </a>
      <nav>
        <a href="#connectors">Connectors</a>
        <a href="/connectors.json">connectors.json</a>
        <a href="/registry.json">registry.json</a>
        <a href="/llms.txt">llms.txt</a>
        <a href="https://github.com/if-uri/connect.ifuri.com">GitHub</a>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <section class="hero">
      <p class="eyebrow">Connector hub for ifuri + urirun</p>
      <h1>Install URI connectors with one command.</h1>
      <p class="lead">Choose connectors, copy one command and let ifuri/urirun expose their URI bindings, registry entries, requirements and flow examples.</p>
      <div class="hero-actions">
        <button class="primary" data-copy="<?php echo hub_h(hub_install_command($defaultIds === '' ? [] : explode(',', $defaultIds))); ?>">Copy default install</button>
        <a class="button" href="<?php echo hub_h($defaultInstall); ?>">Open installer script</a>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <div>
          <h2>Build install command</h2>
          <p>Select multiple connectors. Planned connectors stay visible but are not included in the installer yet.</p>
        </div>
        <button id="selectAvailable">Select available</button>
      </div>
      <div class="command-box">
        <code id="installCommand"><?php echo hub_h(hub_install_command($defaultIds === '' ? [] : explode(',', $defaultIds))); ?></code>
        <button class="primary" id="copyInstall">Copy</button>
      </div>
    </section>

    <div id="connectors" class="connector-filter">
      <input id="connectorSearch" type="search" placeholder="Filter connectors by name, summary or URI scheme…" aria-label="Filter connectors" autocomplete="off">
      <span id="connectorCount" class="muted-count"></span>
    </div>

    <section class="connectors" aria-label="Connectors">
      <?php foreach ($connectors as $connector): ?>
        <?php
          $id = (string) $connector['id'];
          $status = (string) ($connector['status'] ?? 'planned');
          $disabled = $status !== 'available';
          $search = strtolower(trim($id . ' ' . (string) $connector['name'] . ' ' . (string) $connector['summary'] . ' ' . (string) ($connector['category'] ?? '') . ' ' . implode(' ', $connector['uriSchemes'] ?? []) . ' ' . implode(' ', $connector['routes'] ?? []) . ' ' . implode(' ', $connector['keywords'] ?? [])));
        ?>
        <article class="connector" data-status="<?php echo hub_h($status); ?>" data-id="<?php echo hub_h($id); ?>" data-search="<?php echo hub_h($search); ?>">
          <label class="connector-top">
            <input type="checkbox" class="connector-check" value="<?php echo hub_h($id); ?>" <?php echo $disabled ? 'disabled' : 'checked'; ?>>
            <span>
              <strong><?php echo hub_h((string) $connector['name']); ?></strong>
              <em class="status <?php echo hub_h($status); ?>"><?php echo hub_h($status); ?></em>
            </span>
          </label>
          <p class="category"><?php echo hub_h((string) ($connector['category'] ?? 'Connector')); ?></p>
          <p><?php echo hub_h((string) $connector['summary']); ?></p>
          <div class="schemes" aria-label="URI schemes">
            <?php foreach (($connector['uriSchemes'] ?? []) as $scheme): ?>
              <span><?php echo hub_h((string) $scheme); ?>://</span>
            <?php endforeach; ?>
          </div>
          <div class="routes">
            <?php foreach (($connector['routes'] ?? []) as $route): ?>
              <code><?php echo hub_h((string) $route); ?></code>
            <?php endforeach; ?>
          </div>
          <div class="connector-foot">
            <a href="<?php echo hub_h(hub_connector_path($connector)); ?>">Details</a>
            <a href="<?php echo hub_h((string) ($connector['docsUrl'] ?? 'https://github.com/if-uri/docs')); ?>">Docs</a>
            <button data-copy="<?php echo hub_h(hub_install_command([$id])); ?>" <?php echo $disabled ? 'disabled' : ''; ?>>Copy install</button>
          </div>
        </article>
      <?php endforeach; ?>
      <p id="noResults" class="no-results" hidden>No connectors match your filter.</p>
    </section>

    <section class="panel split">
      <div>
        <h2>Machine endpoints</h2>
        <p>ifuri app and future registry tooling can read the same catalog as users.</p>
      </div>
      <div class="links">
        <a href="/connectors.json">/connectors.json</a>
        <a href="/registry.json">/registry.json</a>
        <a href="/install?connectors=planfile,namecheap-dns">/install?connectors=planfile,namecheap-dns</a>
      </div>
    </section>
  </main>

  <footer class="wrap footer">
    <span>Catalog version <?php echo hub_h((string) ($catalog['version'] ?? 'unknown')); ?></span>
    <span>Updated <?php echo hub_h((string) ($catalog['updatedAt'] ?? 'unknown')); ?></span>
  </footer>

  <script>window.CONNECT_HUB_BASE = <?php echo json_encode(hub_base_url(), JSON_UNESCAPED_SLASHES); ?>;</script>
  <script src="/assets/app.js"></script>
</body>
</html>
