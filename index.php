<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

$catalog = hub_catalog();
$connectors = hub_connectors();
$available = array_values(array_filter($connectors, static fn ($item) => ($item['status'] ?? '') === 'available'));
$defaultIds = implode(',', array_column($available, 'id'));
$defaultInstall = '/install?connectors=' . rawurlencode($defaultIds);
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>connect.ifuri.com - connector hub</title>
  <meta name="description" content="Connector hub for ifuri and urirun. Pick integrations, copy one command and install URI connectors for your host or node.">
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
  <header class="site-header">
    <div class="wrap">
      <a class="brand" href="/">
        <span class="mark">if</span>
        <span>connect.ifuri.com</span>
      </a>
      <nav>
        <a href="/connectors.json">connectors.json</a>
        <a href="/registry.json">registry.json</a>
        <a href="https://github.com/if-uri/connect.ifuri.com">GitHub</a>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <section class="hero">
      <p class="eyebrow">Connector hub for ifuri + urirun</p>
      <h1>Install URI connectors with one command.</h1>
      <p class="lead">Choose any set of connectors, copy the generated one-liner and let ifuri/urirun expose their URI bindings, registry entries and flows.</p>
      <div class="hero-actions">
        <button class="primary" data-copy="<?php echo hub_h("curl -fsSL 'https://connect.ifuri.com{$defaultInstall}' | bash"); ?>">Copy default install</button>
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
        <code id="installCommand">curl -fsSL 'https://connect.ifuri.com<?php echo hub_h($defaultInstall); ?>' | bash</code>
        <button class="primary" id="copyInstall">Copy</button>
      </div>
    </section>

    <section class="connectors" aria-label="Connectors">
      <?php foreach ($connectors as $connector): ?>
        <?php
          $id = (string) $connector['id'];
          $status = (string) ($connector['status'] ?? 'planned');
          $disabled = $status !== 'available';
        ?>
        <article class="connector" data-status="<?php echo hub_h($status); ?>" data-id="<?php echo hub_h($id); ?>">
          <label class="connector-top">
            <input type="checkbox" class="connector-check" value="<?php echo hub_h($id); ?>" <?php echo $disabled ? 'disabled' : 'checked'; ?>>
            <span>
              <strong><?php echo hub_h((string) $connector['name']); ?></strong>
              <em class="status <?php echo hub_h($status); ?>"><?php echo hub_h($status); ?></em>
            </span>
          </label>
          <p><?php echo hub_h((string) $connector['summary']); ?></p>
          <div class="routes">
            <?php foreach (($connector['routes'] ?? []) as $route): ?>
              <code><?php echo hub_h((string) $route); ?></code>
            <?php endforeach; ?>
          </div>
          <div class="connector-foot">
            <a href="<?php echo hub_h((string) ($connector['docsUrl'] ?? 'https://github.com/if-uri/docs')); ?>">Docs</a>
            <button data-copy="<?php echo hub_h("curl -fsSL 'https://connect.ifuri.com/install?connectors={$id}' | bash"); ?>" <?php echo $disabled ? 'disabled' : ''; ?>>Copy install</button>
          </div>
        </article>
      <?php endforeach; ?>
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

  <script src="/assets/app.js"></script>
</body>
</html>
