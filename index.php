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
$ecosystem = is_array($site['ecosystem'] ?? null) ? $site['ecosystem'] : [];

// Language follows the same ?lang= signal as the shared ifuri ecobar, which
// defaults to Polish across the ecosystem (ifuri.com, get.ifuri.com, docs).
$lang = (($_GET['lang'] ?? '') === 'en') ? 'en' : 'pl';
$strings = [
    'eyebrow'        => ['en' => 'Connector hub for ifuri + urirun', 'pl' => 'Hub connectorów dla ifuri + urirun'],
    'h1'             => ['en' => 'Install URI connectors with one command.', 'pl' => 'Instaluj connectory URI jedną komendą.'],
    'lead'           => ['en' => 'Choose connectors, copy one command and let ifuri/urirun expose their URI bindings, registry entries, requirements and flow examples.', 'pl' => 'Wybierz connectory, skopiuj jedną komendę, a ifuri/urirun udostępni ich bindings URI, wpisy rejestru, wymagania i przykłady flow.'],
    'copyDefault'    => ['en' => 'Copy default install', 'pl' => 'Kopiuj domyślną instalację'],
    'openInstaller'  => ['en' => 'Open installer script', 'pl' => 'Otwórz skrypt instalatora'],
    'buildTitle'     => ['en' => 'Build install command', 'pl' => 'Zbuduj komendę instalacji'],
    'buildLead'      => ['en' => 'Select multiple connectors. Planned connectors stay visible but are not included in the installer yet.', 'pl' => 'Wybierz wiele connectorów. Planowane connectory pozostają widoczne, ale nie są jeszcze dołączane do instalatora.'],
    'selectAvailable'=> ['en' => 'Select available', 'pl' => 'Zaznacz dostępne'],
    'copy'           => ['en' => 'Copy', 'pl' => 'Kopiuj'],
    'filterPlaceholder' => ['en' => 'Filter connectors by name, summary or URI scheme…', 'pl' => 'Filtruj connectory po nazwie, opisie lub schemacie URI…'],
    'filterAria'     => ['en' => 'Filter connectors', 'pl' => 'Filtruj connectory'],
    'connectorsAria' => ['en' => 'Connectors', 'pl' => 'Connectory'],
    'verified'       => ['en' => '✓ verified', 'pl' => '✓ zweryfikowany'],
    'community'      => ['en' => 'community', 'pl' => 'społeczność'],
    'verifiedTitle'  => ['en' => 'Maintained and audited by if-uri', 'pl' => 'Utrzymywany i audytowany przez if-uri'],
    'communityTitle' => ['en' => 'Third-party community connector', 'pl' => 'Zewnętrzny connector społecznościowy'],
    'categoryFallback' => ['en' => 'Connector', 'pl' => 'Connector'],
    'schemesAria'    => ['en' => 'URI schemes', 'pl' => 'Schematy URI'],
    'details'        => ['en' => 'Details', 'pl' => 'Szczegóły'],
    'docs'           => ['en' => 'Docs', 'pl' => 'Dokumentacja'],
    'copyInstall'    => ['en' => 'Copy install', 'pl' => 'Kopiuj instalację'],
    'noResults'      => ['en' => 'No connectors match your filter.', 'pl' => 'Żaden connector nie pasuje do filtra.'],
    'machineTitle'   => ['en' => 'Machine endpoints', 'pl' => 'Endpointy maszynowe'],
    'machineLead'    => ['en' => 'ifuri app and future registry tooling can read the same catalog as users.', 'pl' => 'Aplikacja ifuri i przyszłe narzędzia rejestru czytają ten sam katalog co użytkownicy.'],
    'ecoTitle'       => ['en' => 'ifuri ecosystem', 'pl' => 'Ekosystem ifuri'],
    'ecoLead'        => ['en' => 'Use these public entry points together: website, one-line installer and connector hub.', 'pl' => 'Korzystaj z tych publicznych punktów wejścia razem: strona, jednoliniowy instalator i hub connectorów.'],
    'footerCatalog'  => ['en' => 'Catalog version', 'pl' => 'Wersja katalogu'],
    'footerSite'     => ['en' => 'Site v', 'pl' => 'Strona v'],
    'footerUpdated'  => ['en' => 'Updated', 'pl' => 'Zaktualizowano'],
    'statusAvailable'=> ['en' => 'available', 'pl' => 'dostępny'],
    'statusPlanned'  => ['en' => 'planned', 'pl' => 'planowany'],
    'jsCopied'       => ['en' => 'Copied', 'pl' => 'Skopiowano'],
    'jsOf'           => ['en' => 'of', 'pl' => 'z'],
];
$t = static fn (string $key): string => (string) ($strings[$key][$lang] ?? $strings[$key]['en'] ?? $key);
$jsLang = ['copied' => $t('jsCopied'), 'of' => $t('jsOf')];
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $site['title'] ?? 'ifuri Connect',
    'description' => $site['description'] ?? 'URI connector hub for ifuri and urirun.',
    'url' => $canonical,
    'sameAs' => array_values(array_filter(array_map(static fn ($item) => is_array($item) ? ($item['url'] ?? null) : null, $ecosystem))),
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
<html lang="<?php echo hub_h($lang); ?>">
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
  <link rel="alternate" type="application/json" href="/search.json" title="ifuri connector search index">
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
        <a href="#connectors">Connectors</a>
        <a href="/submit">Submit connector</a>
        <a href="https://github.com/if-uri/connect.ifuri.com">GitHub</a>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <section class="hero">
      <p class="eyebrow"><?php echo hub_h($t('eyebrow')); ?></p>
      <h1><?php echo hub_h($t('h1')); ?></h1>
      <p class="lead"><?php echo hub_h($t('lead')); ?></p>
      <div class="hero-actions">
        <button class="primary" data-copy="<?php echo hub_h(hub_install_command($defaultIds === '' ? [] : explode(',', $defaultIds))); ?>"><?php echo hub_h($t('copyDefault')); ?></button>
        <a class="button" href="<?php echo hub_h($defaultInstall); ?>"><?php echo hub_h($t('openInstaller')); ?></a>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <div>
          <h2><?php echo hub_h($t('buildTitle')); ?></h2>
          <p><?php echo hub_h($t('buildLead')); ?></p>
        </div>
        <button id="selectAvailable"><?php echo hub_h($t('selectAvailable')); ?></button>
      </div>
      <div class="command-box">
        <code id="installCommand"><?php echo hub_h(hub_install_command($defaultIds === '' ? [] : explode(',', $defaultIds))); ?></code>
        <button class="primary" id="copyInstall"><?php echo hub_h($t('copy')); ?></button>
      </div>
    </section>

    <div id="connectors" class="connector-filter">
      <input id="connectorSearch" type="search" placeholder="<?php echo hub_h($t('filterPlaceholder')); ?>" aria-label="<?php echo hub_h($t('filterAria')); ?>" autocomplete="off">
      <span id="connectorCount" class="muted-count"></span>
    </div>

    <section class="connectors" aria-label="<?php echo hub_h($t('connectorsAria')); ?>">
      <?php foreach ($connectors as $connector): ?>
        <?php
          $id = (string) $connector['id'];
          $status = (string) ($connector['status'] ?? 'planned');
          $provenance = (string) ($connector['provenance'] ?? 'community');
          $disabled = $status !== 'available';
          $search = strtolower(trim($id . ' ' . (string) $connector['name'] . ' ' . (string) $connector['summary'] . ' ' . (string) ($connector['category'] ?? '') . ' ' . implode(' ', $connector['uriSchemes'] ?? []) . ' ' . implode(' ', $connector['routes'] ?? []) . ' ' . implode(' ', $connector['keywords'] ?? [])));
        ?>
        <article class="connector" data-status="<?php echo hub_h($status); ?>" data-id="<?php echo hub_h($id); ?>" data-search="<?php echo hub_h($search); ?>">
          <label class="connector-top">
            <input type="checkbox" class="connector-check" value="<?php echo hub_h($id); ?>" <?php echo $disabled ? 'disabled' : 'checked'; ?>>
            <span>
              <strong><?php echo hub_h((string) $connector['name']); ?></strong>
              <em class="status <?php echo hub_h($status); ?>"><?php echo hub_h($status === 'available' ? $t('statusAvailable') : ($status === 'planned' ? $t('statusPlanned') : $status)); ?></em>
              <em class="prov <?php echo hub_h($provenance); ?>" title="<?php echo hub_h($provenance === 'verified' ? $t('verifiedTitle') : $t('communityTitle')); ?>"><?php echo hub_h($provenance === 'verified' ? $t('verified') : $t('community')); ?></em>
            </span>
          </label>
          <p class="category"><?php echo hub_h((string) ($connector['category'] ?? $t('categoryFallback'))); ?></p>
          <p><?php echo hub_h((string) $connector['summary']); ?></p>
          <div class="schemes" aria-label="<?php echo hub_h($t('schemesAria')); ?>">
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
            <a href="<?php echo hub_h(hub_connector_path($connector)); ?>"><?php echo hub_h($t('details')); ?></a>
            <a href="<?php echo hub_h((string) ($connector['docsUrl'] ?? 'https://github.com/if-uri/docs')); ?>"><?php echo hub_h($t('docs')); ?></a>
            <button data-copy="<?php echo hub_h(hub_install_command([$id])); ?>" <?php echo $disabled ? 'disabled' : ''; ?>><?php echo hub_h($t('copyInstall')); ?></button>
          </div>
        </article>
      <?php endforeach; ?>
      <p id="noResults" class="no-results" hidden><?php echo hub_h($t('noResults')); ?></p>
    </section>

    <section class="panel split">
      <div>
        <h2><?php echo hub_h($t('machineTitle')); ?></h2>
        <p><?php echo hub_h($t('machineLead')); ?></p>
      </div>
      <div class="links">
        <a href="/connectors.json">/connectors.json</a>
        <a href="/search.json">/search.json</a>
        <a href="/registry.json">/registry.json</a>
        <a href="/connectors/planfile.json">/connectors/planfile.json</a>
        <a href="/validate-connector">POST /validate-connector</a>
        <a href="/install?connectors=planfile,namecheap-dns">/install?connectors=planfile,namecheap-dns</a>
      </div>
    </section>

    <?php if ($ecosystem !== []): ?>
      <section class="panel split">
        <div>
          <h2><?php echo hub_h($t('ecoTitle')); ?></h2>
          <p><?php echo hub_h($t('ecoLead')); ?></p>
        </div>
        <div class="links">
          <?php foreach ($ecosystem as $item): ?>
            <?php if (!is_array($item) || !isset($item['url'], $item['label'])) continue; ?>
            <a href="<?php echo hub_h((string) $item['url']); ?>">
              <strong><?php echo hub_h((string) $item['label']); ?></strong>
              <?php if (($item['title'] ?? '') !== ''): ?>
                <span><?php echo hub_h((string) $item['title']); ?></span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>

  <footer class="wrap footer">
    <span><?php echo hub_h($t('footerCatalog')); ?> <?php echo hub_h((string) ($catalog['version'] ?? 'unknown')); ?></span>
    <span><?php echo hub_h($t('footerSite')); ?><?php echo hub_h(hub_version()); ?></span>
    <?php if (!empty($site['ecosystem'])): ?>
      <nav class="footer-eco" aria-label="ifuri ecosystem">
        <?php foreach ($site['ecosystem'] as $eco): ?>
          <a href="<?php echo hub_h((string) $eco['url']); ?>"><?php echo hub_h((string) $eco['label']); ?></a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>
    <span><?php echo hub_h($t('footerUpdated')); ?> <?php echo hub_h((string) ($catalog['updatedAt'] ?? 'unknown')); ?></span>
  </footer>

  <script>window.CONNECT_HUB_BASE = <?php echo json_encode(hub_base_url(), JSON_UNESCAPED_SLASHES); ?>;
window.CONNECT_I18N = <?php echo json_encode($jsLang, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;</script>
  <script src="/assets/app.js"></script>
  <script src="/assets/ifuri-ecobar.js" defer></script>
</body>
</html>
