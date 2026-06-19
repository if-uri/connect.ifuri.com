<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

$id = (string) ($_GET['id'] ?? '');
$connector = hub_connector($id);
if ($connector === null) {
    http_response_code(404);
    $title = 'Connector not found - ifuri Connect';
    ?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo hub_h($title); ?></title>
  <meta name="robots" content="noindex">
  <link rel="stylesheet" href="/assets/ifuri-tokens.css">
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
  <main class="wrap error-page">
    <p class="eyebrow">404</p>
    <h1>Connector not found.</h1>
    <p class="lead">The requested connector is not published in the ifuri connector catalog.</p>
    <a class="button primary" href="/">Back to connector hub</a>
  </main>
</body>
</html><?php
    exit;
}

$site = hub_site();
$canonical = hub_connector_url($connector);
$socialImage = (string) ($site['image'] ?? hub_url('/assets/social-card.svg'));
$title = (string) $connector['name'] . ' URI connector for ifuri + urirun';
$description = (string) ($connector['description'] ?? $connector['summary'] ?? 'URI connector for ifuri and urirun.');
$installCommand = hub_install_command([(string) $connector['id']]);
$status = (string) ($connector['status'] ?? 'planned');
$isAvailable = $status === 'available';
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'name' => $connector['name'] ?? $connector['id'],
    'applicationCategory' => $connector['category'] ?? 'DeveloperApplication',
    'operatingSystem' => 'Linux, macOS',
    'description' => $description,
    'url' => $canonical,
    'softwareRequirements' => $connector['requires'] ?? [],
    'keywords' => hub_keywords($connector),
    'offers' => [
        '@type' => 'Offer',
        'price' => '0',
        'priceCurrency' => 'USD',
    ],
];
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo hub_h($title); ?></title>
  <meta name="description" content="<?php echo hub_h($description); ?>">
  <meta name="keywords" content="<?php echo hub_h(hub_keywords($connector)); ?>">
  <link rel="canonical" href="<?php echo hub_h($canonical); ?>">
  <meta property="og:type" content="article">
  <meta property="og:site_name" content="ifuri Connect">
  <meta property="og:title" content="<?php echo hub_h($title); ?>">
  <meta property="og:description" content="<?php echo hub_h($description); ?>">
  <meta property="og:url" content="<?php echo hub_h($canonical); ?>">
  <meta property="og:image" content="<?php echo hub_h($socialImage); ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo hub_h($title); ?>">
  <meta name="twitter:description" content="<?php echo hub_h($description); ?>">
  <meta name="twitter:image" content="<?php echo hub_h($socialImage); ?>">
  <meta name="theme-color" content="#4F46E5">
  <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
  <link rel="sitemap" type="application/xml" href="/sitemap.xml">
  <link rel="alternate" type="application/json" href="<?php echo hub_h(hub_connector_json_path($connector)); ?>" title="<?php echo hub_h((string) $connector['name']); ?> connector manifest">
  <link rel="stylesheet" href="/assets/ifuri-tokens.css">
  <link rel="stylesheet" href="/assets/app.css">
  <script type="application/ld+json"><?php echo json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
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
        <a href="/connectors.json">connectors.json</a>
        <a href="/registry.json">registry.json</a>
        <a href="/llms.txt">llms.txt</a>
        <a href="https://github.com/if-uri/connect.ifuri.com">GitHub</a>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <section class="detail-hero">
      <p class="eyebrow"><?php echo hub_h((string) ($connector['category'] ?? 'Connector')); ?> connector</p>
      <div class="detail-title">
        <div>
          <h1><?php echo hub_h((string) $connector['name']); ?></h1>
          <p class="lead"><?php echo hub_h($description); ?></p>
        </div>
        <em class="status <?php echo hub_h($status); ?>"><?php echo hub_h($status); ?></em>
      </div>
      <div class="hero-actions">
        <button class="primary" data-copy="<?php echo hub_h($installCommand); ?>" <?php echo $isAvailable ? '' : 'disabled'; ?>>Copy install</button>
        <a class="button" href="<?php echo hub_h(hub_install_path([(string) $connector['id']])); ?>">Open installer script</a>
        <a class="button" href="<?php echo hub_h((string) ($connector['docsUrl'] ?? 'https://github.com/if-uri/docs')); ?>">Docs</a>
      </div>
    </section>

    <section class="panel">
      <div class="tabs" data-tabs>
        <div class="tab-list" role="tablist" aria-label="Connector sections">
          <button class="tab is-active" type="button" data-tab-target="overview" role="tab" aria-selected="true">Overview</button>
          <button class="tab" type="button" data-tab-target="routes" role="tab" aria-selected="false">URI routes</button>
          <button class="tab" type="button" data-tab-target="install" role="tab" aria-selected="false">Install</button>
          <button class="tab" type="button" data-tab-target="registry" role="tab" aria-selected="false">Registry</button>
        </div>

        <div class="tab-panel is-active" data-tab-panel="overview" role="tabpanel">
          <div class="detail-grid">
            <div>
              <h2>What it does</h2>
              <ul class="clean-list">
                <?php foreach (($connector['useCases'] ?? []) as $useCase): ?>
                  <li><?php echo hub_h((string) $useCase); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div>
              <h2>URI schemes</h2>
              <div class="schemes large">
                <?php foreach (($connector['uriSchemes'] ?? []) as $scheme): ?>
                  <span><?php echo hub_h((string) $scheme); ?>://</span>
                <?php endforeach; ?>
              </div>
              <h2>Requirements</h2>
              <ul class="clean-list compact">
                <?php foreach (($connector['requires'] ?? []) as $requirement): ?>
                  <li><code><?php echo hub_h((string) $requirement); ?></code></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>

        <div id="routes" class="tab-panel" data-tab-panel="routes" role="tabpanel" hidden>
          <h2>Routes</h2>
          <div class="route-list">
            <?php foreach (($connector['routes'] ?? []) as $route): ?>
              <button class="copy-row" type="button" data-copy="<?php echo hub_h((string) $route); ?>">
                <code><?php echo hub_h((string) $route); ?></code>
                <span>Copy</span>
              </button>
            <?php endforeach; ?>
          </div>

          <?php if (($connector['examples'] ?? []) !== []): ?>
            <h2>Examples</h2>
            <div class="examples">
              <?php foreach (($connector['examples'] ?? []) as $example): ?>
                <article class="example">
                  <h3><?php echo hub_h((string) ($example['title'] ?? 'Example')); ?></h3>
                  <code><?php echo hub_h((string) ($example['uri'] ?? '')); ?></code>
                  <pre><?php echo hub_h(json_encode($example['payload'] ?? (object) [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></pre>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="tab-panel" data-tab-panel="install" role="tabpanel" hidden>
          <h2>Install command</h2>
          <div class="command-box">
            <code><?php echo hub_h($installCommand); ?></code>
            <button class="primary" data-copy="<?php echo hub_h($installCommand); ?>" <?php echo $isAvailable ? '' : 'disabled'; ?>>Copy</button>
          </div>
          <?php if (!$isAvailable): ?>
            <p class="notice">This connector is planned. The installer is visible for contract design, but execution is disabled until the connector package is available.</p>
          <?php endif; ?>
        </div>

        <div class="tab-panel" data-tab-panel="registry" role="tabpanel" hidden>
          <h2>Registry entry</h2>
          <pre><?php echo hub_h(json_encode([
              'id' => $connector['id'],
              'name' => $connector['name'],
              'status' => $connector['status'],
              'uriSchemes' => $connector['uriSchemes'] ?? [],
              'routes' => $connector['routes'] ?? [],
              'install' => $connector['install'] ?? [],
              'hubUrl' => $canonical,
          ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)); ?></pre>
          <div class="links inline">
            <a href="/registry.json">Full registry</a>
            <a href="/connectors.json">Connector catalog</a>
            <a href="<?php echo hub_h(hub_connector_json_path($connector)); ?>">Connector JSON</a>
            <a href="/search.json">Search index</a>
            <a href="/llms.txt">LLM index</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="wrap footer">
    <span>Catalog version <?php echo hub_h((string) (hub_catalog()['version'] ?? 'unknown')); ?></span>
    <span><a href="/sitemap.xml">sitemap.xml</a></span>
  </footer>

  <script>window.CONNECT_HUB_BASE = <?php echo json_encode(hub_base_url(), JSON_UNESCAPED_SLASHES); ?>;</script>
  <script src="/assets/app.js"></script>
</body>
</html>
