<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

$ids = hub_selected_ids($_GET['connectors'] ?? null);
header('Content-Type: text/x-shellscript; charset=utf-8');
header('Cache-Control: no-store');
echo hub_installer_script($ids);
