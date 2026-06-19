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

$raw = file_get_contents('php://input');
$payload = json_decode((string) $raw, true);
if (!is_array($payload)) {
    hub_send_json([
        'ok' => false,
        'errors' => [
            hub_validation_error('body', 'request body must be a JSON object'),
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
