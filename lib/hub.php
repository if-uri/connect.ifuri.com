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

    $pipSpecs = [];
    foreach ($selected as $connector) {
        if (($connector['status'] ?? '') !== 'available') {
            continue;
        }
        $spec = $connector['install']['pipSpec'] ?? ($catalog['defaultPipSpec'] ?? '');
        if ($spec !== '') {
            $pipSpecs[$spec] = true;
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
    foreach (array_keys($pipSpecs) as $spec) {
        $escaped = str_replace("'", "'\"'\"'", $spec);
        $script .= "  \$PIP_BIN install '{$escaped}'\n";
    }
    $script .= "fi\n";
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
        $items[] = [
            'id' => $connector['id'],
            'name' => $connector['name'],
            'status' => $connector['status'],
            'routes' => $connector['routes'] ?? [],
            'install' => $connector['install'] ?? [],
            'docsUrl' => $connector['docsUrl'] ?? null,
        ];
    }
    return [
        'version' => 'ifuri.registry.v1',
        'generatedAt' => gmdate('c'),
        'connectors' => $items,
    ];
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
