<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/hub.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    hub_send_json([
        'ok' => false,
        'error' => 'method_not_allowed',
        'message' => 'POST a connector manifest JSON object to validate it.',
        'template' => hub_connector_manifest_template(),
    ], 405, 'no-store');
    exit;
}

$maxBody = 64 * 1024; // 64 KB is plenty for a connector manifest
$rlMax = (int) (getenv('CONNECT_VALIDATE_RL_MAX') ?: 30);
$rlWindow = (int) (getenv('CONNECT_VALIDATE_RL_WINDOW') ?: 60);

if (!hub_rate_limit('validate', hub_client_ip(), $rlMax, $rlWindow)) {
    header('Retry-After: ' . $rlWindow);
    hub_send_json([
        'ok' => false,
        'error' => 'rate_limited',
        'message' => "Too many validation requests. Try again in {$rlWindow}s.",
    ], 429, 'no-store');
    exit;
}

if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $maxBody) {
    hub_send_json([
        'ok' => false,
        'error' => 'payload_too_large',
        'message' => 'Manifest body exceeds 64 KB.',
    ], 413, 'no-store');
    exit;
}

$raw = file_get_contents('php://input', false, null, 0, $maxBody + 1);
if ($raw !== false && strlen($raw) > $maxBody) {
    hub_send_json([
        'ok' => false,
        'error' => 'payload_too_large',
        'message' => 'Manifest body exceeds 64 KB.',
    ], 413, 'no-store');
    exit;
}

$payload = json_decode((string) $raw, true, 32); // shallow depth cap to reject nesting spam
if (!is_array($payload)) {
    hub_send_json([
        'ok' => false,
        'errors' => [
            hub_validation_error('body', 'request body must be a JSON object (max 64 KB, max depth 32)'),
        ],
    ], 400, 'no-store');
    exit;
}

$folderId = isset($_GET['id']) ? (string) $_GET['id'] : (isset($payload['id']) ? (string) $payload['id'] : null);
$errors = hub_validate_connector_manifest($payload, $folderId);
$ok = $errors === [];
hub_send_json([
    'ok' => $ok,
    'errors' => $errors,
    'folder' => $ok ? 'data/connectors/' . (string) $payload['id'] . '/manifest.json' : null,
    'manifest' => $payload,
    'allowlistedCommunityAdapterKinds' => hub_community_adapter_allowlist(),
], $ok ? 200 : 422, 'no-store');
