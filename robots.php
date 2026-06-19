<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/hub.php';

header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Sitemap: " . hub_url('/sitemap.xml') . "\n";
