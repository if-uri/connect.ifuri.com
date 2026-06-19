<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

$site = hub_site();
$canonical = hub_url('/submit');
$socialImage = (string) ($site['image'] ?? hub_url('/assets/social-card.svg'));
$template = hub_connector_manifest_template();
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'WebApplication',
    'name' => 'ifuri connector manifest builder',
    'applicationCategory' => 'DeveloperApplication',
    'description' => 'Build and validate a connector manifest for connect.ifuri.com.',
    'url' => $canonical,
];
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Submit a URI connector - ifuri Connect</title>
  <meta name="description" content="Build and validate a connector manifest for the ifuri and urirun connector hub.">
  <link rel="canonical" href="<?php echo hub_h($canonical); ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ifuri Connect">
  <meta property="og:title" content="Submit a URI connector">
  <meta property="og:description" content="Build and validate connector manifests for connect.ifuri.com.">
  <meta property="og:url" content="<?php echo hub_h($canonical); ?>">
  <meta property="og:image" content="<?php echo hub_h($socialImage); ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Submit a URI connector">
  <meta name="twitter:description" content="Build and validate connector manifests for connect.ifuri.com.">
  <meta name="twitter:image" content="<?php echo hub_h($socialImage); ?>">
  <meta name="theme-color" content="#4F46E5">
  <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
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
        <a href="/search.json">search.json</a>
        <a href="/llms.txt">llms.txt</a>
        <a href="https://github.com/if-uri/connect.ifuri.com">GitHub</a>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <section class="detail-hero">
      <p class="eyebrow">Connector submission</p>
      <h1>Build a connector manifest.</h1>
      <p class="lead">Fill the contract, validate it against the same policy as CI, then add the generated JSON to <code>data/connectors/&lt;id&gt;/manifest.json</code>.</p>
    </section>

    <section class="submit-layout" id="connectorBuilder">
      <form class="panel builder-form" id="connectorBuilderForm">
        <div class="panel-head">
          <div>
            <h2>Manifest fields</h2>
            <p>Community connectors must declare publisher and adapter kinds.</p>
          </div>
          <button type="button" id="loadTemplate">Reset sample</button>
        </div>

        <div class="form-grid">
          <label>
            <span>ID</span>
            <input name="id" value="<?php echo hub_h((string) $template['id']); ?>" autocomplete="off">
          </label>
          <label>
            <span>Name</span>
            <input name="name" value="<?php echo hub_h((string) $template['name']); ?>">
          </label>
          <label>
            <span>Status</span>
            <select name="status">
              <option value="planned" selected>planned</option>
              <option value="available">available</option>
            </select>
          </label>
          <label>
            <span>Provenance</span>
            <select name="provenance">
              <option value="community" selected>community</option>
              <option value="verified">verified</option>
            </select>
          </label>
          <label>
            <span>Category</span>
            <input name="category" value="<?php echo hub_h((string) $template['category']); ?>">
          </label>
          <label>
            <span>Install mode</span>
            <select name="installMode">
              <option value="planned" selected>planned</option>
              <option value="bundled">bundled</option>
              <option value="urirun-extra">urirun-extra</option>
            </select>
          </label>
        </div>

        <label>
          <span>Summary</span>
          <input name="summary" value="<?php echo hub_h((string) $template['summary']); ?>">
        </label>
        <label>
          <span>Description</span>
          <textarea name="description" rows="4"><?php echo hub_h((string) $template['description']); ?></textarea>
        </label>
        <label>
          <span>URI schemes</span>
          <textarea name="uriSchemes" rows="2"><?php echo hub_h(implode("\n", $template['uriSchemes'])); ?></textarea>
        </label>
        <label>
          <span>Routes</span>
          <textarea name="routes" rows="3"><?php echo hub_h(implode("\n", $template['routes'])); ?></textarea>
        </label>
        <label>
          <span>Adapter kinds</span>
          <textarea name="adapterKinds" rows="2"><?php echo hub_h(implode("\n", $template['adapterKinds'])); ?></textarea>
        </label>
        <label>
          <span>pip packages or pip spec</span>
          <textarea name="pipPackages" rows="2"><?php echo hub_h((string) ($template['install']['pipSpec'] ?? '')); ?></textarea>
        </label>
        <div class="form-grid">
          <label>
            <span>Publisher</span>
            <input name="publisherName" value="<?php echo hub_h((string) $template['publisher']['name']); ?>">
          </label>
          <label>
            <span>Publisher URL</span>
            <input name="publisherUrl" value="<?php echo hub_h((string) $template['publisher']['url']); ?>">
          </label>
          <label>
            <span>Publisher GitHub</span>
            <input name="publisherGithub" value="<?php echo hub_h((string) $template['publisher']['github']); ?>">
          </label>
          <label>
            <span>Docs URL</span>
            <input name="docsUrl" value="<?php echo hub_h((string) $template['docsUrl']); ?>">
          </label>
        </div>
        <label>
          <span>Keywords</span>
          <textarea name="keywords" rows="2"><?php echo hub_h(implode("\n", $template['keywords'])); ?></textarea>
        </label>

        <div class="hero-actions">
          <button class="primary" type="button" id="buildManifest">Build JSON</button>
          <button type="button" id="validateManifest">Validate</button>
        </div>
      </form>

      <section class="panel builder-output">
        <div class="panel-head">
          <div>
            <h2>Generated manifest</h2>
            <p id="manifestFolder">data/connectors/example-connector/manifest.json</p>
          </div>
          <button type="button" data-copy-target="manifestOutput">Copy JSON</button>
        </div>
        <textarea class="code-output" id="manifestOutput" spellcheck="false"></textarea>
        <div class="validation-result" id="validationResult" aria-live="polite"></div>
      </section>
    </section>
  </main>

  <footer class="wrap footer">
    <span>Site v<?php echo hub_h(hub_version()); ?></span>
    <span><a href="/docs/SUBMIT-CONNECTOR.md">Submit docs</a></span>
    <span><a href="/schema/connector.schema.json">connector.schema.json</a></span>
  </footer>

  <script>window.CONNECT_HUB_BASE = <?php echo json_encode(hub_base_url(), JSON_UNESCAPED_SLASHES); ?>;</script>
  <script>window.CONNECTOR_TEMPLATE = <?php echo json_encode($template, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;</script>
  <script src="/assets/app.js"></script>
</body>
</html>
