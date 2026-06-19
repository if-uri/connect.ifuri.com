<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/hub.php';

$connector = hub_connector((string) ($_GET['id'] ?? ''));
if ($connector === null) {
    hub_send_json(['error' => 'connector_not_found'], 404);
    exit;
}

hub_send_json(hub_connector_manifest($connector));
