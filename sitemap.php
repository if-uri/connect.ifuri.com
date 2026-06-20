<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

$catalog = hub_catalog();
$updated = (string) ($catalog['updatedAt'] ?? gmdate('Y-m-d'));
$urls = [
    ['loc' => hub_url('/'), 'priority' => '1.0'],
    ['loc' => hub_url('/categories'), 'priority' => '0.8'],
    ['loc' => hub_url('/submit'), 'priority' => '0.8'],
    ['loc' => hub_url('/connectors.json'), 'priority' => '0.7'],
    ['loc' => hub_url('/registry.json'), 'priority' => '0.7'],
    ['loc' => hub_url('/search.json'), 'priority' => '0.7'],
    ['loc' => hub_url('/mcp.json'), 'priority' => '0.7'],
    ['loc' => hub_url('/a2a.json'), 'priority' => '0.7'],
    ['loc' => hub_url('/llms.txt'), 'priority' => '0.6'],
];
foreach (hub_connectors() as $connector) {
    $urls[] = ['loc' => hub_connector_url($connector), 'priority' => '0.9'];
}

header('Content-Type: application/xml; charset=utf-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
  <url>
    <loc><?php echo hub_h($url['loc']); ?></loc>
    <lastmod><?php echo hub_h($updated); ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority><?php echo hub_h($url['priority']); ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
