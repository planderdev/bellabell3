<?php
declare(strict_types=1);
$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if ($urlPath === '/' || $urlPath === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}
$requested = realpath(__DIR__ . rawurldecode($urlPath));
$assetsRoot = realpath(__DIR__ . '/assets');
if ($requested && $assetsRoot && str_starts_with($requested, $assetsRoot . DIRECTORY_SEPARATOR) && is_file($requested)) {
    return false;
}
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
exit;
