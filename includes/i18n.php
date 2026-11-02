<?php
declare(strict_types=1);

// Korean text is the translation key, so templates stay readable and a missing entry falls back to Korean.
const LOCALES = [
    'ko' => ['label' => 'KOR', 'html' => 'ko', 'prefix' => ''],
    'en' => ['label' => 'ENG', 'html' => 'en', 'prefix' => '/en'],
    'zh' => ['label' => '中文', 'html' => 'zh-CN', 'prefix' => '/zh'],
];

function locale(?string $set = null): string
{
    static $current = 'ko';
    if ($set !== null && isset(LOCALES[$set])) {
        $current = $set;
    }
    return $current;
}

function t(string $korean, string $context = ''): string
{
    static $maps = [];
    $code = locale();
    if ($code === 'ko') {
        return $korean;
    }
    $maps[$code] ??= require __DIR__ . '/lang/' . $code . '.php';
    return $maps[$code][$korean . '|' . $context] ?? $maps[$code][$korean] ?? $korean;
}

function t_deep(array $values): array
{
    return array_map(fn ($value) => is_array($value) ? t_deep($value) : (is_string($value) ? t($value) : $value), $values);
}

function page_url(string $path, ?string $code = null): string
{
    $prefix = LOCALES[$code ?? locale()]['prefix'];
    if ($prefix === '') {
        return $path;
    }
    return $path === '/' ? $prefix : $prefix . $path;
}
