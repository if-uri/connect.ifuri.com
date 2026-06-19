<?php
declare(strict_types=1);

function hub_base_path(): string
{
    return dirname(__DIR__);
}

function hub_json(string $path): array
{
    $fullPath = hub_base_path() . '/' . ltrim($path, '/');
    $raw = file_get_contents($fullPath);
    if ($raw === false) {
        throw new RuntimeException("Cannot read {$path}");
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException("Invalid JSON in {$path}");
    }
    return $data;
}

function hub_catalog(): array
{
    return hub_json('data/connectors.json');
}

function hub_connectors(): array
{
    return hub_catalog()['connectors'] ?? [];
}

function hub_site(): array
{
    return hub_catalog()['site'] ?? [];
}

function hub_base_url(): string
{
    $env = getenv('CONNECT_HUB_PUBLIC_BASE');
    $base = is_string($env) && trim($env) !== '' ? trim($env) : (string) (hub_site()['baseUrl'] ?? 'https://connect.ifuri.com');
    return rtrim($base, '/');
}

function hub_url(string $path = '/'): string
{
    if ($path === '') {
        $path = '/';
    }
    return hub_base_url() . '/' . ltrim($path, '/');
}

function hub_connector_map(): array
{
    $map = [];
    foreach (hub_connectors() as $connector) {
        if (isset($connector['id'])) {
            $map[$connector['id']] = $connector;
        }
    }
    return $map;
}

function hub_connector(?string $id): ?array
{
    if ($id === null || !preg_match('/^[a-z0-9._-]+$/', $id)) {
        return null;
    }
    return hub_connector_map()[$id] ?? null;
}

function hub_connector_path(array $connector): string
{
    return '/connectors/' . rawurlencode((string) $connector['id']);
}

function hub_connector_url(array $connector): string
{
    return hub_url(hub_connector_path($connector));
}

function hub_connector_json_path(array $connector): string
{
    return hub_connector_path($connector) . '.json';
}

function hub_connector_json_url(array $connector): string
{
    return hub_url(hub_connector_json_path($connector));
}

function hub_selected_ids(?string $raw): array
{
    if ($raw === null || trim($raw) === '') {
        return [];
    }
    $parts = preg_split('/[,\s]+/', $raw) ?: [];
    $ids = [];
    foreach ($parts as $part) {
        $id = strtolower(trim($part));
        if ($id !== '' && preg_match('/^[a-z0-9._-]+$/', $id)) {
            $ids[$id] = true;
        }
    }
    return array_keys($ids);
}

function hub_available_connectors(): array
{
    return array_values(array_filter(hub_connectors(), static fn ($item) => ($item['status'] ?? '') === 'available'));
}

function hub_default_install_path(): string
{
    $ids = implode(',', array_column(hub_available_connectors(), 'id'));
    return '/install?connectors=' . rawurlencode($ids);
}

function hub_install_path(array $ids): string
{
    $clean = [];
    foreach ($ids as $id) {
        $value = strtolower(trim((string) $id));
        if ($value !== '' && preg_match('/^[a-z0-9._-]+$/', $value)) {
            $clean[$value] = true;
        }
    }
    return '/install?connectors=' . rawurlencode(implode(',', array_keys($clean)));
}

function hub_install_command(array $ids): string
{
    return "curl -fsSL '" . hub_url(hub_install_path($ids)) . "' | bash";
}

function hub_installer_script(array $ids): string
{
    $catalog = hub_catalog();
    $map = hub_connector_map();
    $selected = [];
    foreach ($ids as $id) {
        if (isset($map[$id])) {
            $selected[] = $map[$id];
        }
    }
    if (!$selected) {
        $selected = array_values(array_filter(hub_connectors(), static fn ($item) => ($item['status'] ?? '') === 'available'));
    }

    $coreSpec = (string) ($catalog['defaultPipSpec'] ?? '');
    $pipPackages = [];
    foreach ($selected as $connector) {
        if (($connector['status'] ?? '') !== 'available') {
            continue;
        }
        $install = $connector['install'] ?? [];
        if (($install['pipSpec'] ?? '') !== '' && $install['pipSpec'] !== $coreSpec) {
            $pipPackages[(string) $install['pipSpec']] = true;
        }
        foreach (($install['pipPackages'] ?? []) as $package) {
            if ((string) $package !== '') {
                $pipPackages[(string) $package] = true;
            }
        }
    }

    $connectorList = implode(' ', array_map('escapeshellarg', array_column($selected, 'id')));
    $script = <<<'SH'
#!/usr/bin/env bash
set -euo pipefail

if ! command -v python3 >/dev/null 2>&1; then
  echo "python3 is required" >&2
  exit 1
fi

PYTHON_BIN="${PYTHON_BIN:-python3}"
PIP_BIN="${PIP_BIN:-$PYTHON_BIN -m pip}"

if ! $PYTHON_BIN -m pip --version >/dev/null 2>&1; then
  echo "python3 -m pip is required" >&2
  exit 1
fi

SH;
    $script .= "\nCONNECTORS=(" . $connectorList . ")\n";
    $script .= "echo \"Installing ifuri connectors: \${CONNECTORS[*]}\"\n";
    $script .= "if [ -n \"\${URIRUN_PIP_SPEC:-}\" ]; then\n";
    $script .= "  \$PIP_BIN install \"\$URIRUN_PIP_SPEC\"\n";
    $script .= "else\n";
    if ($coreSpec !== '') {
        $escapedCore = str_replace("'", "'\"'\"'", $coreSpec);
        $script .= "  \$PIP_BIN install '{$escapedCore}'\n";
    }
    $script .= "fi\n";
    foreach (array_keys($pipPackages) as $spec) {
        $escaped = str_replace("'", "'\"'\"'", $spec);
        $script .= "  \$PIP_BIN install '{$escaped}'\n";
    }
    $script .= <<<'SH'

if command -v urirun >/dev/null 2>&1; then
  echo
  echo "urirun installed:"
  urirun --help | head -20 || true
fi

echo
echo "Next:"
echo "  urirun connectors list   # after connector entry-point support lands"
echo "  ifuri host dashboard     # from the ifuri app/host package"
SH;
    return $script . "\n";
}

function hub_registry(): array
{
    $items = [];
    foreach (hub_connectors() as $connector) {
        $items[] = hub_registry_entry($connector);
    }
    return [
        'version' => 'ifuri.registry.v1',
        'generatedAt' => gmdate('c'),
        'connectors' => $items,
    ];
}

function hub_registry_entry(array $connector): array
{
    return [
        'id' => $connector['id'],
        'name' => $connector['name'],
        'status' => $connector['status'],
        'category' => $connector['category'] ?? null,
        'summary' => $connector['summary'] ?? null,
        'description' => $connector['description'] ?? null,
        'uriSchemes' => $connector['uriSchemes'] ?? [],
        'routes' => $connector['routes'] ?? [],
        'examples' => $connector['examples'] ?? [],
        'flowExample' => $connector['flowExample'] ?? [],
        'install' => $connector['install'] ?? [],
        'docsUrl' => $connector['docsUrl'] ?? null,
        'hubUrl' => hub_connector_url($connector),
        'manifestUrl' => hub_connector_json_url($connector),
    ];
}

function hub_connector_manifest(array $connector): array
{
    return [
        'version' => 'ifuri.connector.v1',
        'generatedAt' => gmdate('c'),
        'connector' => $connector,
        'registryEntry' => hub_registry_entry($connector),
        'installCommand' => hub_install_command([(string) $connector['id']]),
    ];
}

function hub_search_index(): array
{
    $records = [];
    foreach (hub_connectors() as $connector) {
        $connectorId = (string) $connector['id'];
        $connectorText = implode(' ', array_filter([
            $connectorId,
            $connector['name'] ?? '',
            $connector['status'] ?? '',
            $connector['category'] ?? '',
            $connector['summary'] ?? '',
            $connector['description'] ?? '',
            implode(' ', $connector['uriSchemes'] ?? []),
            implode(' ', $connector['keywords'] ?? []),
            implode(' ', $connector['useCases'] ?? []),
            implode(' ', $connector['routes'] ?? []),
        ]));
        $records[] = [
            'type' => 'connector',
            'id' => $connectorId,
            'title' => $connector['name'] ?? $connectorId,
            'status' => $connector['status'] ?? null,
            'category' => $connector['category'] ?? null,
            'summary' => $connector['summary'] ?? null,
            'uriSchemes' => $connector['uriSchemes'] ?? [],
            'routes' => $connector['routes'] ?? [],
            'url' => hub_connector_url($connector),
            'manifestUrl' => hub_connector_json_url($connector),
            'text' => $connectorText,
        ];
        foreach (($connector['routes'] ?? []) as $route) {
            $scheme = str_contains((string) $route, '://') ? explode('://', (string) $route, 2)[0] : null;
            $records[] = [
                'type' => 'route',
                'id' => $connectorId . ':' . $route,
                'connectorId' => $connectorId,
                'connectorName' => $connector['name'] ?? $connectorId,
                'route' => $route,
                'scheme' => $scheme,
                'status' => $connector['status'] ?? null,
                'url' => hub_connector_url($connector) . '#routes',
                'manifestUrl' => hub_connector_json_url($connector),
                'text' => trim($route . ' ' . ($connector['name'] ?? '') . ' ' . ($connector['summary'] ?? '')),
            ];
        }
    }

    return [
        'version' => 'ifuri.search.v1',
        'generatedAt' => gmdate('c'),
        'site' => hub_site(),
        'recordCount' => count($records),
        'records' => $records,
    ];
}

function hub_keywords(array $connector = null): string
{
    $base = ['ifuri', 'urirun', 'URI connector', 'URI registry', 'automation'];
    if ($connector !== null) {
        $base = array_merge($base, $connector['keywords'] ?? [], $connector['uriSchemes'] ?? []);
    }
    return implode(', ', array_values(array_unique(array_filter(array_map('strval', $base)))));
}

function hub_send_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: public, max-age=120');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function hub_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
