<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/i18n.php';
$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
// /en and /zh serve the same pages as Korean; anything else under those prefixes stays a 404.
if (preg_match('~^/(en|zh)(/brand|/product|/ingredients)?/?$~', $urlPath, $localeMatch)) {
    locale($localeMatch[1]);
    $urlPath = $localeMatch[2] ?? '/';
}
$_SERVER['LOCALE_PAGE'] = $urlPath === '/index.php' ? '/' : $urlPath;
if ($urlPath === '/' || $urlPath === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}
if ($urlPath === '/brand') {
    require __DIR__ . '/brand.php';
    return true;
}
if ($urlPath === '/product') {
    require __DIR__ . '/product.php';
    return true;
}
if ($urlPath === '/ingredients') {
    require __DIR__ . '/ingredients.php';
    return true;
}
if ($urlPath === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nDisallow: /admin\nDisallow: /api/\n\nSitemap: " . SITE_URL . "/sitemap.xml\n";
    return true;
}
if ($urlPath === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach (['/', '/brand', '/product', '/ingredients'] as $page) {
        foreach (array_keys(LOCALES) as $code) {
            echo '<url><loc>' . SITE_URL . page_url($page, $code) . '</loc></url>';
        }
    }
    echo '</urlset>';
    return true;
}
if ($urlPath === '/api/contact') {
    require __DIR__ . '/../includes/common.php';
    require __DIR__ . '/../includes/contact-handler.php';
    locale((string) ($_POST['locale'] ?? 'ko'));
    handle_inquiry_request();
    return true;
}
if (preg_match('~^/media/research/([1-9]\d{0,14})$~', $urlPath, $slideMatch)) {
    require __DIR__ . '/../includes/research-slides.php';
    serve_research_slide((int) $slideMatch[1]);
    return true;
}
if (preg_match('~^/media/site/([1-9]\d{0,14})$~', $urlPath, $siteImageMatch)) {
    require __DIR__ . '/../includes/research-slides.php';
    serve_site_image((int) $siteImageMatch[1]);
    return true;
}
if (preg_match('~^/admin(?:/([a-z]+))?/?$~', $urlPath, $adminMatch)) {
    require __DIR__ . '/../includes/common.php';
    require __DIR__ . '/../includes/admin.php';
    handle_admin_request($adminMatch[1] ?? '');
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
