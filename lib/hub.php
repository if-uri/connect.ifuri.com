<?php
declare(strict_types=1);

function hub_base_path(): string
{
    return dirname(__DIR__);
}

function hub_version(): string
{
    static $version = null;
    if ($version === null) {
        $file = hub_base_path() . '/VERSION';
        $version = is_file($file) ? trim((string) file_get_contents($file)) : '';
        if ($version === '') {
            $version = '0.0.0';
        }
    }
    return $version;
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
    $modules = [];
    $bundled = [
        'planfile' => false,
        'sqlite-context' => false,
        'domain-monitor' => false,
        'namecheap-dns' => false,
        'grpc-transport' => false,
    ];
    foreach ($selected as $connector) {
        if (($connector['status'] ?? '') !== 'available') {
            continue;
        }
        $id = (string) $connector['id'];
        $installMode = (string) (($connector['install'] ?? [])['mode'] ?? '');
        if ($installMode === 'urirun-extra') {
            $modules[] = 'urirun_connector_' . str_replace('-', '_', $id);
        } elseif (array_key_exists($id, $bundled)) {
            $bundled[$id] = true;
        }
    }
    $moduleList = implode(' ', array_map('escapeshellarg', $modules));
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
    if ($pipPackages) {
        $script .= "echo \"Ensuring selected urirun runtime after connector package installs...\"\n";
        $script .= "if [ -n \"\${URIRUN_PIP_SPEC:-}\" ]; then\n";
        $script .= "  \$PIP_BIN install \"\$URIRUN_PIP_SPEC\"\n";
        $script .= "else\n";
        if ($coreSpec !== '') {
            $escapedCore = str_replace("'", "'\"'\"'", $coreSpec);
            $script .= "  \$PIP_BIN install '{$escapedCore}'\n";
        }
        $script .= "fi\n";
    }
    $script .= "\nREG_DIR=\"\${IFURI_REGISTRY_DIR:-\$HOME/.ifuri}\"\n";
    $script .= "WANT_PLANFILE=" . ($bundled['planfile'] ? '1' : '0') . "\n";
    $script .= "WANT_SQLITE_CONTEXT=" . ($bundled['sqlite-context'] ? '1' : '0') . "\n";
    $script .= "WANT_DOMAIN_MONITOR=" . ($bundled['domain-monitor'] ? '1' : '0') . "\n";
    $script .= "WANT_NAMECHEAP_DNS=" . ($bundled['namecheap-dns'] ? '1' : '0') . "\n";
    $script .= "WANT_GRPC_TRANSPORT=" . ($bundled['grpc-transport'] ? '1' : '0') . "\n";
    $script .= "MODULES=(" . $moduleList . ")\n";
    $script .= <<<'SH'

if command -v urirun >/dev/null 2>&1; then
  echo
  echo "system urirun found:"
  urirun --help | head -5 || true
fi

if [ -n "${URIRUN_BIN:-}" ]; then
  read -r -a URIRUN_CMD <<< "$URIRUN_BIN"
else
  URIRUN_CMD=("$PYTHON_BIN" -m urirun.v2)
fi

echo
echo "installer urirun runtime:"
"${URIRUN_CMD[@]}" --help | head -5 || true

mkdir -p "$REG_DIR"
HOST_DB="${IFURI_HOST_DB:-$REG_DIR/host.db}"
PLANFILE_PROJECT="${IFURI_PLANFILE_PROJECT:-$REG_DIR/planfile-project}"
SCREENSHOT_DIR="${IFURI_SCREENSHOT_DIR:-$REG_DIR/screenshots}"
BINDING_FILES=()

if [ "${#MODULES[@]}" -gt 0 ]; then
  echo
  echo "Building bindings from connector packages..."
  $PYTHON_BIN - "$REG_DIR/connector-packages.bindings.v2.json" "${MODULES[@]}" <<'PY'
import importlib, json, sys
import urirun

out = sys.argv[1]
for name in sys.argv[2:]:
    try:
        importlib.import_module(name)  # registers the package's connector-declared URI routes
    except Exception as exc:  # noqa: BLE001 - installer must not silently drop selected packages
        raise SystemExit(f"connector package import failed for {name}: {exc}") from exc
doc = urirun.connector_bindings()  # all routes registered by the imported connectors
with open(out, "w", encoding="utf-8") as fh:
    json.dump(doc, fh, indent=2)
print(f"  bindings: {len(doc.get('bindings', {}))} route(s) -> {out}")
PY
  BINDING_FILES+=("$REG_DIR/connector-packages.bindings.v2.json")
fi

if [ "$WANT_SQLITE_CONTEXT" = "1" ]; then
  echo "Building bundled sqlite-context bindings..."
  "${URIRUN_CMD[@]}" host data bindings --target host --db "$HOST_DB" --out "$REG_DIR/sqlite-context.bindings.v2.json"
  BINDING_FILES+=("$REG_DIR/sqlite-context.bindings.v2.json")
fi

if [ "$WANT_DOMAIN_MONITOR" = "1" ] || [ "$WANT_NAMECHEAP_DNS" = "1" ]; then
  echo "Building bundled domain/namecheap bindings..."
  mkdir -p "$PLANFILE_PROJECT" "$SCREENSHOT_DIR"
  "${URIRUN_CMD[@]}" host monitor bindings \
    --target host \
    --db "$HOST_DB" \
    --project "$PLANFILE_PROJECT" \
    --screenshot-dir "$SCREENSHOT_DIR" \
    --out "$REG_DIR/domain-monitor.bindings.v2.json"
  BINDING_FILES+=("$REG_DIR/domain-monitor.bindings.v2.json")
fi

if [ "$WANT_PLANFILE" = "1" ]; then
  echo "Building bundled planfile bindings..."
  mkdir -p "$PLANFILE_PROJECT"
  "${URIRUN_CMD[@]}" host task bindings --target host --project "$PLANFILE_PROJECT" --out "$REG_DIR/planfile.bindings.v2.json"
  BINDING_FILES+=("$REG_DIR/planfile.bindings.v2.json")
fi

if [ "$WANT_GRPC_TRANSPORT" = "1" ]; then
  echo "gRPC transport installed. Use: python3 -m urirun.v2_grpc serve <registry.json>"
fi

if [ "${#BINDING_FILES[@]}" -gt 0 ]; then
  echo
  echo "Compiling selected connector registry..."
  for file in "${BINDING_FILES[@]}"; do
    "${URIRUN_CMD[@]}" validate "$file"
  done
  "${URIRUN_CMD[@]}" compile "${BINDING_FILES[@]}" --out "$REG_DIR/connectors.registry.json" --on-conflict keep
  echo "  registry: $REG_DIR/connectors.registry.json"
  echo
  echo "Run a connector route:"
  echo "  ifuri-app urirun-call <uri> --registry $REG_DIR/connectors.registry.json --execute"
  echo "  urirun run <uri> $REG_DIR/connectors.registry.json --execute --allow '<scheme>://*'"
else
  echo
  echo "No installable connectors selected (planned connectors have no package yet)."
fi
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

function hub_community_adapter_allowlist(): array
{
    return [
        'domain-monitor',
        'grpc-transport',
        'http-service',
        'local-function',
        'planfile-task',
    ];
}

function hub_connector_manifest_template(): array
{
    return [
        'id' => 'example-connector',
        'name' => 'Example Connector',
        'status' => 'planned',
        'category' => 'Automation',
        'summary' => 'Short connector summary with the main URI schemes and purpose.',
        'description' => 'Longer connector description explaining what system it integrates, what URI commands it exposes and when a user should install it.',
        'uriSchemes' => ['example'],
        'routes' => ['example://host/resource/query/status'],
        'useCases' => [
            'Expose an existing service as URI-addressable commands.',
            'Generate registry entries that ifuri and urirun can discover.',
        ],
        'examples' => [
            [
                'title' => 'Query status',
                'uri' => 'example://host/resource/query/status',
                'payload' => ['limit' => 10],
            ],
        ],
        'flowExample' => ['example://host/resource/query/status'],
        'requires' => ['python>=3.10'],
        'install' => [
            'mode' => 'planned',
            'pipSpec' => 'urirun-connector-example',
        ],
        'adapterKinds' => ['http-service'],
        'docsUrl' => 'https://github.com/if-uri/docs',
        'keywords' => ['example', 'connector', 'URI'],
        'provenance' => 'community',
        'publisher' => [
            'name' => 'Your name or org',
            'url' => 'https://example.com',
            'github' => 'https://github.com/example',
        ],
    ];
}

function hub_validation_error(string $field, string $message): array
{
    return ['field' => $field, 'message' => $message];
}

function hub_validate_connector_manifest(array $manifest, ?string $folderId = null): array
{
    $errors = [];
    $required = ['id', 'name', 'status', 'category', 'summary', 'description', 'uriSchemes', 'routes', 'install', 'provenance'];
    foreach ($required as $field) {
        if (!array_key_exists($field, $manifest) || $manifest[$field] === '' || $manifest[$field] === []) {
            $errors[] = hub_validation_error($field, "{$field} is required");
        }
    }

    $id = (string) ($manifest['id'] ?? '');
    if ($id !== '' && !preg_match('/^[a-z0-9][a-z0-9._-]*$/', $id)) {
        $errors[] = hub_validation_error('id', 'id must match ^[a-z0-9][a-z0-9._-]*$');
    }
    if ($folderId !== null && $folderId !== '' && $id !== '' && $id !== $folderId) {
        $errors[] = hub_validation_error('id', "id must equal folder name {$folderId}");
    }

    if (isset($manifest['status']) && !in_array($manifest['status'], ['available', 'planned'], true)) {
        $errors[] = hub_validation_error('status', 'status must be available or planned');
    }

    $uriSchemes = $manifest['uriSchemes'] ?? [];
    if (!is_array($uriSchemes) || $uriSchemes === []) {
        $errors[] = hub_validation_error('uriSchemes', 'uriSchemes must be a non-empty array');
        $uriSchemes = [];
    }
    foreach ($uriSchemes as $scheme) {
        if (!is_string($scheme) || !preg_match('/^[a-z][a-z0-9+.-]*$/', $scheme)) {
            $errors[] = hub_validation_error('uriSchemes', 'each URI scheme must match ^[a-z][a-z0-9+.-]*$');
            break;
        }
    }

    $routes = $manifest['routes'] ?? [];
    if (!is_array($routes) || $routes === []) {
        $errors[] = hub_validation_error('routes', 'routes must be a non-empty array');
        $routes = [];
    }
    $schemeSet = array_fill_keys(array_map('strval', $uriSchemes), true);
    foreach ($routes as $route) {
        if (!is_string($route) || !str_contains($route, '://')) {
            $errors[] = hub_validation_error('routes', "route is not a URI: " . (is_scalar($route) ? (string) $route : '[non-scalar]'));
            continue;
        }
        $scheme = explode('://', $route, 2)[0];
        if (!isset($schemeSet[$scheme])) {
            $errors[] = hub_validation_error('routes', "route {$route} uses a scheme outside uriSchemes");
        }
    }

    if (!isset($manifest['install']) || !is_array($manifest['install'])) {
        $errors[] = hub_validation_error('install', 'install must be an object');
    } elseif (($manifest['install']['mode'] ?? '') === '') {
        $errors[] = hub_validation_error('install.mode', 'install.mode is required');
    } elseif (!in_array($manifest['install']['mode'], ['bundled', 'urirun-extra', 'planned'], true)) {
        $errors[] = hub_validation_error('install.mode', 'install.mode must be bundled, urirun-extra or planned');
    }

    $provenance = (string) ($manifest['provenance'] ?? '');
    if ($provenance !== '' && !in_array($provenance, ['verified', 'community'], true)) {
        $errors[] = hub_validation_error('provenance', 'provenance must be verified or community');
    }

    if ($provenance === 'community') {
        if (!isset($manifest['publisher']) || !is_array($manifest['publisher']) || (string) ($manifest['publisher']['name'] ?? '') === '') {
            $errors[] = hub_validation_error('publisher.name', 'community connector requires publisher.name');
        }
        if (!array_key_exists('adapterKinds', $manifest) || !is_array($manifest['adapterKinds'])) {
            $errors[] = hub_validation_error('adapterKinds', 'community connector must declare adapterKinds; use [] if it registers no executors');
        } else {
            $allowlist = array_fill_keys(hub_community_adapter_allowlist(), true);
            foreach ($manifest['adapterKinds'] as $adapterKind) {
                if (!is_string($adapterKind) || !isset($allowlist[$adapterKind])) {
                    $errors[] = hub_validation_error('adapterKinds', "community connector cannot use adapterKind " . (is_scalar($adapterKind) ? (string) $adapterKind : '[non-scalar]'));
                }
            }
        }
    }

    return $errors;
}

function hub_keywords(array $connector = null): string
{
    $base = ['ifuri', 'urirun', 'URI connector', 'URI registry', 'automation'];
    if ($connector !== null) {
        $base = array_merge($base, $connector['keywords'] ?? [], $connector['uriSchemes'] ?? []);
    }
    return implode(', ', array_values(array_unique(array_filter(array_map('strval', $base)))));
}

function hub_send_json(array $payload, int $status = 200, string $cacheControl = 'public, max-age=120'): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: ' . $cacheControl);
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function hub_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function hub_tool_name(string $uri): string
{
    $name = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $uri));
    return trim($name, '_');
}

function hub_mcp_tools(): array
{
    $tools = [];
    foreach (hub_connectors() as $c) {
        foreach (($c['routes'] ?? []) as $route) {
            $tools[] = [
                'name' => hub_tool_name((string) $route),
                'description' => trim(sprintf('%s — %s [%s]', $c['name'] ?? $c['id'], $c['summary'] ?? '', $c['status'] ?? 'planned')),
                'inputSchema' => ['type' => 'object'],
                'annotations' => [
                    'connector' => $c['id'] ?? null,
                    'uri' => $route,
                    'status' => $c['status'] ?? null,
                    'category' => $c['category'] ?? null,
                    'install' => $c['install'] ?? null,
                ],
            ];
        }
    }
    return [
        'version' => 'connect.mcp.v1',
        'generatedAt' => gmdate('c'),
        'server' => ['name' => 'ifuri-connect', 'url' => hub_base_url()],
        'note' => 'Discovery projection of the connector catalog. Install a connector (annotations.install) to get an executable urirun registry: curl -fsSL ' . hub_base_url() . '/install?connectors=<id> | bash',
        'tools' => $tools,
    ];
}

function hub_a2a_card(): array
{
    $skills = [];
    foreach (hub_connectors() as $c) {
        $tags = array_values(array_unique(array_merge($c['uriSchemes'] ?? [], $c['keywords'] ?? [])));
        $skills[] = [
            'id' => $c['id'] ?? '',
            'name' => $c['name'] ?? ($c['id'] ?? ''),
            'description' => $c['summary'] ?? ($c['description'] ?? ''),
            'tags' => $tags,
            'examples' => $c['routes'] ?? [],
        ];
    }
    return [
        'name' => 'ifuri-connect',
        'description' => 'ifURI connector hub: URI routes for DNS, files, LLM, devices, processes and more — installable as urirun registries and projectable to MCP tools.',
        'url' => hub_base_url(),
        'version' => hub_version(),
        'provider' => ['organization' => 'if-uri', 'url' => 'https://ifuri.com'],
        'capabilities' => ['streaming' => false, 'pushNotifications' => false, 'stateTransitionHistory' => false],
        'defaultInputModes' => ['application/json'],
        'defaultOutputModes' => ['application/json'],
        'skills' => $skills,
    ];
}

function hub_client_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        $value = (string) ($_SERVER[$key] ?? '');
        if ($value !== '') {
            $first = trim(explode(',', $value)[0]);
            if ($first !== '') {
                return $first;
            }
        }
    }
    return 'unknown';
}

/**
 * Simple file-based sliding-window rate limit (server-local temp, not deployed).
 * Returns true when the request is allowed, false when the limit is exceeded.
 */
function hub_rate_limit(string $bucket, string $id, int $max, int $window): bool
{
    $dir = sys_get_temp_dir() . '/ifuri-connect-rl';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    $file = $dir . '/' . preg_replace('/[^a-z0-9]+/i', '', $bucket) . '_' . substr(hash('sha256', $id), 0, 16);
    $now = time();
    $times = [];
    if (is_file($file)) {
        foreach (explode("\n", (string) @file_get_contents($file)) as $line) {
            $t = (int) $line;
            if ($t > $now - $window) {
                $times[] = $t;
            }
        }
    }
    if (count($times) >= $max) {
        return false;
    }
    $times[] = $now;
    @file_put_contents($file, implode("\n", $times), LOCK_EX);
    return true;
}
