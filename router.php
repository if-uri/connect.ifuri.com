<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

if ($path === '/') {
    require __DIR__ . '/index.php';
    return true;
}
if ($path === '/install') {
    require __DIR__ . '/install.php';
    return true;
}
if ($path === '/submit') {
    require __DIR__ . '/submit.php';
    return true;
}
if ($path === '/validate-connector') {
    require __DIR__ . '/api/validate_connector.php';
    return true;
}
if ($path === '/connectors.json') {
    require __DIR__ . '/api/connectors.php';
    return true;
}
if ($path === '/registry.json') {
    require __DIR__ . '/api/registry.php';
    return true;
}
if ($path === '/search.json') {
    require __DIR__ . '/api/search.php';
    return true;
}
if ($path === '/sitemap.xml') {
    require __DIR__ . '/sitemap.php';
    return true;
}
if ($path === '/robots.txt') {
    require __DIR__ . '/robots.php';
    return true;
}
if ($path === '/llms.txt') {
    require __DIR__ . '/llms.php';
    return true;
}
if (preg_match('#^/connectors/([a-z0-9._-]+)\.json$#', $path, $match)) {
    $_GET['id'] = $match[1];
    require __DIR__ . '/api/connector.php';
    return true;
}
if (preg_match('#^/connectors/([a-z0-9._-]+)$#', $path, $match)) {
    $_GET['id'] = $match[1];
    require __DIR__ . '/connector.php';
    return true;
}

http_response_code(404);
echo 'Not found';
return true;
