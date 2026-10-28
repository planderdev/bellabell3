<?php
declare(strict_types=1);

// Shared response settings and template helpers.
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self'; font-src 'self'; style-src 'self'; script-src 'self' https://unpkg.com/lucide@1.63.0/dist/umd/lucide.min.js; base-uri 'none'; frame-ancestors 'self'; form-action 'none'");

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function local_image(?array $image, string $class = '', bool $priority = false): void
{
    if (!$image || empty($image['src'])) {
        return;
    }
    $path = realpath(__DIR__ . '/../public' . $image['src']);
    $root = realpath(__DIR__ . '/../public/assets');
    if (!$path || !$root || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || !is_file($path)) {
        return;
    }
    echo '<img class="' . e($class) . '" src="' . e($image['src']) . '" alt="' . e($image['alt'] ?? '') . '" loading="' . ($priority ? 'eager' : 'lazy') . '" decoding="async">';
}

function media_frame(?array $image, string $class = '', bool $priority = false): void
{
    ob_start();
    local_image($image, '', $priority);
    $markup = ob_get_clean();
    $missing = $markup === '';
    echo '<div class="media-frame ' . e($class) . ($missing ? ' missing-image' : '') . '" data-media-state="' . ($missing ? 'missing' : 'ready') . '"' . ($missing ? ' aria-hidden="true"' : '') . '>' . $markup . '</div>';
}
