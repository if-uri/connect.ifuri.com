<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

$catalog = hub_catalog();
$site = hub_site();
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=300');
echo "# ifuri Connect\n\n";
echo (string) ($site['description'] ?? 'URI connector hub for ifuri and urirun.') . "\n\n";
echo "Canonical site: " . hub_url('/') . "\n";
echo "Machine catalog: " . hub_url('/connectors.json') . "\n";
echo "Runtime registry: " . hub_url('/registry.json') . "\n";
echo "Installer endpoint: " . hub_url('/install?connectors=planfile') . "\n";
echo "Catalog version: " . (string) ($catalog['version'] ?? 'unknown') . "\n";
echo "Updated: " . (string) ($catalog['updatedAt'] ?? 'unknown') . "\n\n";
echo "## Connectors\n\n";
foreach (hub_connectors() as $connector) {
    echo "### " . (string) ($connector['name'] ?? $connector['id']) . "\n";
    echo "ID: " . (string) $connector['id'] . "\n";
    echo "Status: " . (string) ($connector['status'] ?? 'unknown') . "\n";
    echo "URL: " . hub_connector_url($connector) . "\n";
    echo "Summary: " . (string) ($connector['summary'] ?? '') . "\n";
    echo "URI schemes: " . implode(', ', array_map(static fn ($scheme) => (string) $scheme . '://', $connector['uriSchemes'] ?? [])) . "\n";
    echo "Routes:\n";
    foreach (($connector['routes'] ?? []) as $route) {
        echo "- " . (string) $route . "\n";
    }
    echo "\n";
}
