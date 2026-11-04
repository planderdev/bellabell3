<?php
declare(strict_types=1);

// Admin-edited site content. content.php holds the defaults; rows in site_content override them by path
// ("values.0"), per language for text and with the locale "all" for images. A missing database or table
// must never break a public page, so every read falls back to the defaults.
require_once __DIR__ . '/contact-handler.php';

const SITE_LINK_PATTERN = '~^(/[A-Za-z0-9/_#-]*|#[A-Za-z0-9_-]+|https://[^\s"<>]+|tel:\+?[0-9-]+|mailto:[^\s"<>]+)$~';

function site_sections(): array
{
    static $sections = null;
    return $sections ??= require __DIR__ . '/site-sections.php';
}

// Every editable field by path, the only keys the site reads or the admin writes.
function site_fields(): array
{
    static $fields = null;
    if ($fields === null) {
        $fields = [];
        foreach (site_sections() as $page) {
            foreach ($page['sections'] as $section) {
                foreach ($section['fields'] as $field) {
                    $fields[$field['key']] = $field;
                }
            }
        }
    }
    return $fields;
}

function site_get(array $values, string $path): mixed
{
    foreach (explode('.', $path) as $part) {
        if (!is_array($values) || !array_key_exists($part, $values)) {
            return null;
        }
        $values = $values[$part];
    }
    return $values;
}

function site_set(array &$values, string $path, string $value): void
{
    $target = &$values;
    foreach (explode('.', $path) as $part) {
        if (!is_array($target)) {
            $target = [];
        }
        $target = &$target[$part];
    }
    $target = $value;
}

// content.php translated for one language, without any admin edits.
function site_defaults(?string $code = null): array
{
    $current = locale();
    locale($code ?? $current);
    $content = t_deep(require __DIR__ . '/content.php');
    $content['partner_title'] = t('제안 & 협업', 'title');
    $content['history_title'] = t('연혁', 'title');
    locale($current);
    return $content;
}

function site_rows(\PDO $pdo, string $code): array
{
    $statement = $pdo->prepare("SELECT key, value FROM site_content WHERE locale = :code OR locale = 'all'");
    $statement->execute(['code' => $code]);
    return $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
}

function site_content(): array
{
    $content = site_defaults();
    $pdo = db_connection();
    if (!$pdo instanceof \PDO) {
        return $content;
    }
    try {
        $rows = site_rows($pdo, locale());
    } catch (\Throwable $exception) {
        error_log('Site content query failed (' . get_class($exception) . ').');
        return $content;
    }
    foreach ($rows as $key => $value) {
        if (isset(site_fields()[$key])) {
            site_set($content, (string)$key, (string)$value);
        }
    }
    return $content;
}

function site_menu_rows(\PDO $pdo): array
{
    return $pdo->query('SELECT id, parent_id, label_ko, label_en, label_zh, href, visible, position FROM site_menu_items ORDER BY position, id')->fetchAll(\PDO::FETCH_ASSOC);
}

// Header menu for the current language: the admin's items once any exist, otherwise content.php's.
function site_menu(array $content): array
{
    $rows = [];
    $pdo = db_connection();
    if ($pdo instanceof \PDO) {
        try {
            $rows = site_menu_rows($pdo);
        } catch (\Throwable $exception) {
            error_log('Site menu query failed (' . get_class($exception) . ').');
        }
    }
    if (!$rows) {
        return $content['menu'];
    }
    $item = fn (array $row): array => [
        'label' => (string)($row['label_' . locale()] ?: $row['label_ko']),
        'href' => (string)$row['href'],
        'children' => [],
    ];
    $menu = [];
    foreach ($rows as $row) {
        if ($row['parent_id'] === null && $row['visible']) {
            $entry = $item($row);
            foreach ($rows as $child) {
                if ((int)$child['parent_id'] === (int)$row['id'] && $child['visible']) {
                    $entry['children'][] = $item($child);
                }
            }
            $menu[] = $entry;
        }
    }
    return $menu;
}

function site_ingredient_rows(\PDO $pdo): array
{
    return $pdo->query('SELECT id, name_ko, name_en, name_zh, body_ko, body_en, body_zh, image, icon FROM ingredient_items ORDER BY position, id')->fetchAll(\PDO::FETCH_ASSOC);
}

// Ingredients for the current language: the admin's once any exist, otherwise content.php's.
function site_ingredients(array $content): array
{
    $rows = [];
    $pdo = db_connection();
    if ($pdo instanceof \PDO) {
        try {
            $rows = site_ingredient_rows($pdo);
        } catch (\Throwable $exception) {
            error_log('Ingredients query failed (' . get_class($exception) . ').');
        }
    }
    if (!$rows) {
        return $content['ingredients'];
    }
    return array_map(fn (array $row): array => [
        'id' => 'ingredient-' . (int)$row['id'],
        'name' => (string)($row['name_' . locale()] ?: $row['name_ko']),
        'body' => (string)($row['body_' . locale()] ?: $row['body_ko']),
        'image' => (string)$row['image'],
        'icon' => (string)$row['icon'],
    ], $rows);
}

function site_research_rows(\PDO $pdo): array
{
    return $pdo->query('SELECT id, tag_ko, tag_en, tag_zh, title_ko, title_en, title_zh, body_ko, body_en, body_zh, image FROM research_items ORDER BY position, id')->fetchAll(\PDO::FETCH_ASSOC);
}

// Cards for /research. A missing database or table falls back to content.php.
function site_research_items(array $content): array
{
    $rows = [];
    $pdo = db_connection();
    if ($pdo instanceof \PDO) {
        try {
            $rows = site_research_rows($pdo);
        } catch (\Throwable $exception) {
            error_log('Research items query failed (' . get_class($exception) . ').');
        }
    }
    if (!$rows) {
        return $content['research_items'];
    }
    $code = locale();
    $pick = fn (array $row, string $field): string => (string)($row[$field . '_' . $code] ?: $row[$field . '_ko']);
    return array_map(fn (array $row): array => [
        'tag' => $pick($row, 'tag'),
        'title' => $pick($row, 'title'),
        'body' => $pick($row, 'body'),
        'image' => (string)$row['image'],
    ], $rows);
}

function site_product_rows(\PDO $pdo): array
{
    return $pdo->query('SELECT id, category, name_ko, name_en, name_zh, detail_ko, detail_en, detail_zh, size_ko, size_en, size_zh, image, url FROM product_items ORDER BY position, id')->fetchAll(\PDO::FETCH_ASSOC);
}

// Products for /product, grouped in category order. A missing database or table falls back to content.php.
function site_products(array $content): array
{
    $rows = [];
    $pdo = db_connection();
    if ($pdo instanceof \PDO) {
        try {
            $rows = site_product_rows($pdo);
        } catch (\Throwable $exception) {
            error_log('Products query failed (' . get_class($exception) . ').');
        }
    }
    $order = array_flip(array_column($content['product_categories'], 'id'));
    $code = locale();
    $pick = fn (array $row, string $field): string => (string)($row[$field . '_' . $code] ?: $row[$field . '_ko']);
    $products = $rows ? array_map(fn (array $row): array => [
        'category' => (string)$row['category'],
        'name' => $pick($row, 'name'),
        'detail' => $pick($row, 'detail'),
        'size' => $pick($row, 'size'),
        'image' => $row['image'] === '' ? null : ['src' => (string)$row['image'], 'alt' => trim($pick($row, 'name') . ' ' . $pick($row, 'detail'))],
        'url' => (string)$row['url'],
    ], $rows) : $content['products'];
    $products = array_values(array_filter($products, fn (array $product): bool => isset($order[$product['category']])));
    $positions = array_keys($products);
    array_multisort(array_map(fn (array $product): int => $order[$product['category']], $products), SORT_ASC, $positions, SORT_ASC, $products);
    return $products;
}

function site_brand_gallery_rows(\PDO $pdo): array
{
    return $pdo->query('SELECT id, image FROM brand_gallery_items ORDER BY position, id')->fetchAll(\PDO::FETCH_ASSOC);
}

// Activity photos on the brand slide, managed at /admin/gallery. Until then the files listed in content.php show.
function site_brand_gallery(array $fallback): array
{
    $pdo = db_connection();
    if (!$pdo instanceof \PDO) {
        return $fallback;
    }
    try {
        $rows = site_brand_gallery_rows($pdo);
    } catch (\Throwable $exception) {
        error_log('Brand gallery query failed (' . get_class($exception) . ').');
        return $fallback;
    }
    return $rows ? array_column($rows, 'image') : $fallback;
}

function site_ingredient_icons(): array
{
    static $icons = null;
    return $icons ??= require __DIR__ . '/ingredient-icons.php';
}

function site_ingredient_icon(string $key, string $class): string
{
    $icon = site_ingredient_icons()[$key] ?? null;
    return $icon ? '<svg class="' . e($class) . '" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icon['svg'] . '</svg>' : '';
}

function site_href(string $href): string
{
    return str_starts_with($href, '/') ? page_url($href) : $href;
}

function site_link_attributes(string $href): string
{
    return str_starts_with($href, 'http') ? ' target="_blank" rel="noopener noreferrer"' : '';
}
