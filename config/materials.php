<?php
declare(strict_types=1);

return [
    'hero_images' => [
        ['src' => '/assets/media/hero-haenyeo.webp', 'alt' => '제주 해녀와 수산 원물'],
        ['src' => '/assets/media/photo/farm/farm6.jpg', 'alt' => '농장에서 반려견과 함께한 두 사람'],
    ],
    'meaning_image' => ['src' => '/assets/media/meaning-bamboo.webp', 'alt' => '조릿대'],
    'mission_story' => null,
    'mission_image' => ['src' => '/assets/bongbong.webp', 'alt' => '봉봉이'],
    'mission_images' => [
        ['src' => '/assets/bongbong.webp', 'alt' => '봉봉이'],
        ['src' => '/assets/media/mission-bongbong.webp', 'alt' => '봉봉이'],
        ['src' => '/assets/media/mission-beach.webp', 'alt' => '봉봉이'],
    ],
    'brand_image' => ['src' => '/assets/media/brand-ingredients.webp', 'alt' => '프롬한라 원물 촬영 사진'],
    'patents' => [],
    'agreement' => ['date' => '2025.10.28', 'copy' => null, 'image' => null],
    'social_assets' => [],
    // Publish only explicitly adopted records with status = approved.
    'history_approved' => [],
    // Each year maps to confirmed local image records: [['src' => ..., 'alt' => ...]].
    'history_photos' => [],
    'history_interval_ms' => 5000,
    'global_copy' => null,
    'global_images' => [],
    'form' => null,
];
