<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

$site = hub_site();
$canonical = hub_url('/categories');
$socialImage = (string) ($site['image'] ?? hub_url('/assets/social-card.svg'));
$title = hub_t('catTitle');
$description = hub_t('catDescription');

// Group connectors by category.
$byCategory = [];
foreach (hub_connectors() as $connector) {
    $cat = (string) ($connector['category'] ?? 'Other');
    $byCategory[$cat][] = $connector;
}
ksort($byCategory, SORT_NATURAL | SORT_FLAG_CASE);
foreach ($byCategory as &$list) {
    usort($list, static fn ($a, $b) => strcmp((string) $a['id'], (string) $b['id']));
}
unset($list);

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $title,
    'description' => $description,
    'url' => $canonical,
];
?><!doctype html>
<html lang="<?php echo hub_h(hub_lang()); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo hub_h($title); ?></title>
  <meta name="description" content="<?php echo hub_h($description); ?>">
  <link rel="canonical" href="<?php echo hub_h($canonical); ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ifuri Connect">
  <meta property="og:title" content="<?php echo hub_h($title); ?>">
  <meta property="og:description" content="<?php echo hub_h($description); ?>">
  <meta property="og:url" content="<?php echo hub_h($canonical); ?>">
  <meta property="og:image" content="<?php echo hub_h($socialImage); ?>">
  <meta name="twitter:card" content="summary_large_image">
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
        <img class="mark" src="/assets/mark.svg" alt="ifuri" width="34" height="34">
        <span>connect.ifuri.com</span>
      </a>
      <nav>
        <a href="/#connectors"><?php echo hub_h(hub_t('navConnectors')); ?></a>
        <a href="/categories"><?php echo hub_h(hub_t('navCategories')); ?></a>
        <a href="/connectors.json">connectors.json</a>
        <a href="/llms.txt">llms.txt</a>
        <a href="https://github.com/if-uri/connect.ifuri.com">GitHub</a>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <section class="detail-hero">
      <p class="eyebrow"><?php echo hub_h(hub_t('catEyebrow')); ?></p>
      <h1><?php echo hub_h(hub_t('catH1')); ?></h1>
      <p class="lead"><?php echo hub_h($description); ?></p>
      <div class="schemes large">
        <?php foreach (array_keys($byCategory) as $cat): ?>
          <a href="#cat-<?php echo hub_h(rawurlencode(strtolower($cat))); ?>"><span><?php echo hub_h($cat); ?> (<?php echo count($byCategory[$cat]); ?>)</span></a>
        <?php endforeach; ?>
      </div>
    </section>

    <?php foreach ($byCategory as $cat => $list): ?>
      <section class="panel" id="cat-<?php echo hub_h(rawurlencode(strtolower((string) $cat))); ?>">
        <h2><?php echo hub_h((string) $cat); ?></h2>
        <div class="card-grid">
          <?php foreach ($list as $connector): ?>
            <?php
              $status = (string) ($connector['status'] ?? 'planned');
              $cpath = hub_connector_path($connector);
            ?>
            <a class="connector-card" href="<?php echo hub_h($cpath); ?>">
              <div class="card-top">
                <strong><?php echo hub_h((string) $connector['name']); ?></strong>
                <em class="status <?php echo hub_h($status); ?>"><?php echo hub_h($status === 'available' ? hub_t('statusAvailable') : ($status === 'planned' ? hub_t('statusPlanned') : $status)); ?></em>
              </div>
              <p><?php echo hub_h((string) ($connector['summary'] ?? '')); ?></p>
              <div class="schemes">
                <?php foreach (($connector['uriSchemes'] ?? []) as $scheme): ?>
                  <span><?php echo hub_h((string) $scheme); ?>://</span>
                <?php endforeach; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>

    <section class="panel" id="lan-demos">
      <h2><?php echo hub_h(hub_t('lanTitle')); ?></h2>
      <p class="lead"><?php echo hub_h(hub_t('lanLead')); ?></p>
      <div class="command-box">
        <code><?php echo hub_h(hub_install_command(['http-check', 'time-tools'])); ?></code>
        <button class="primary" data-copy="<?php echo hub_h(hub_install_command(['http-check', 'time-tools'])); ?>"><?php echo hub_h(hub_t('copy')); ?></button>
      </div>
      <ul class="clean-list">
        <li><a href="https://github.com/if-uri/examples/tree/main/11-novnc_lan_flow">11-novnc_lan_flow</a> — <?php echo hub_h(hub_t('lanDemo1')); ?></li>
        <li><a href="https://github.com/if-uri/examples/tree/main/12-full_e2e_connect_lab">12-full_e2e_connect_lab</a> — <?php echo hub_h(hub_t('lanDemo2')); ?></li>
      </ul>
      <div class="links inline">
        <a href="/#connectors"><?php echo hub_h(hub_t('allConnectors')); ?></a>
        <a href="https://docs.ifuri.com/connectors.html"><?php echo hub_h(hub_t('connectorDocs')); ?></a>
        <a href="https://docs.ifuri.com/novnc-demo.html"><?php echo hub_h(hub_t('novncGuide')); ?></a>
      </div>
    </section>
  </main>

  <footer class="wrap footer">
    <span><?php echo hub_h(hub_t('footerSite')); ?><?php echo hub_h(hub_version()); ?></span>
    <span><?php echo hub_h(hub_t('footerCatalog')); ?> <?php echo hub_h((string) (hub_catalog()['version'] ?? 'unknown')); ?></span>
    <span><a href="/sitemap.xml">sitemap.xml</a></span>
  </footer>

  <script>window.CONNECT_HUB_BASE = <?php echo json_encode(hub_base_url(), JSON_UNESCAPED_SLASHES); ?>;
window.CONNECT_I18N = <?php echo json_encode(hub_js_i18n(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;</script>
  <script src="/assets/app.js"></script>
  <script src="/assets/ifuri-ecobar.js" defer></script>
</body>
</html>
