<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = site_content();
$productCopy = $content['product'];
$pageTitle = 'Product | ' . t('벨아벨팜');
$categoryNames = array_column($content['product_categories'], 'name', 'id');
$categoryUrls = array_column($content['product_categories'], 'url', 'id');
$marqueeItems = ['From HALLA, For PET', ...array_column($content['product_categories'], 'name')];
$products = site_products($content);
?>
<!doctype html><html lang="<?= e(LOCALES[locale()]['html']) ?>">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="sub" lang="<?= e(LOCALES[locale()]['html']) ?>" data-home="<?= e(page_url('/')) ?>">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<main class="product-page" id="main">
<section class="product-hero" aria-labelledby="product-title">
<div class="inner">
<div>
<p class="product-hero-label" lang="en"><?= e($productCopy['label']) ?></p>
<h2 class="product-hero-title" id="product-title"><?= implode('<br/>', array_map('e', $productCopy['hero_lines'])) ?></h2>
<p class="product-hero-description"><?= implode('<br/>', array_map('e', $productCopy['hero_body'])) ?></p>
<a class="product-button" href="<?= e(site_href($productCopy['hero_url'])) ?>"<?= site_link_attributes($productCopy['hero_url']) ?>><span><?= e($productCopy['hero_button']) ?></span><i data-lucide="<?= str_starts_with($productCopy['hero_url'], '#') ? 'arrow-down' : 'arrow-up-right' ?>" class="site-icon" aria-hidden="true" focusable="false"></i></a>
</div>
<video class="product-hero-image" id="product-hero-video" autoplay muted loop playsinline preload="auto" poster="/assets/media/product/hero-ragdoll-poster.webp" width="960" height="720" aria-label="<?= e(t('랙돌 고양이가 프롬한라스틱 앞에서 입맛을 다시는 영상')) ?>">
<source src="/assets/media/product/hero-ragdoll.mp4" type="video/mp4"/>
</video>
</div>
</section>
<div class="product-marquee" aria-hidden="true">
<div class="product-marquee-track">
<?php for ($copy = 0; $copy < 4; $copy++): ?>
<ul>
<?php foreach ($marqueeItems as $marqueeItem): ?>
<li><?= e($marqueeItem) ?></li>
<?php endforeach; ?>
</ul>
<?php endfor; ?>
</div>
</div>
<section class="product-section" id="products" aria-labelledby="products-title">
<div class="inner">
<h2 class="product-heading" id="products-title" lang="en"><?= e($productCopy['family_heading']) ?></h2>
<div class="product-filters" role="group" aria-label="<?= e(t('제품 분류')) ?>">
<button class="product-filter is-active" type="button" data-filter="all" aria-pressed="true"><?= e(t('전체')) ?></button>
<?php foreach ($content['product_categories'] as $category): ?>
<button class="product-filter" type="button" data-filter="<?= e($category['id']) ?>" aria-pressed="false"><?= e($category['name']) ?></button>
<?php endforeach; ?>
</div>
<ul class="product-grid">
<?php foreach ($products as $index => $product): ?>
<li class="product-card" data-category="<?= e($product['category']) ?>">
<div class="product-card-media"<?= $product['image'] === null ? ' data-media-state="missing" role="img" aria-label="' . e($product['name']) . ' ' . e(t('사진 미확보')) . '"' : '' ?>>
<?php if ($product['image'] !== null): ?>
<img src="<?= e($product['image']['src']) ?>" alt="<?= e($product['image']['alt'] ?? '') ?>" width="800" height="800" loading="lazy" decoding="async"/>
<?php endif; ?>
<span class="product-card-tag"><?= e($categoryNames[$product['category']]) ?></span>
<span class="product-card-number" aria-hidden="true"><?= sprintf('%02d', $index + 1) ?></span>
</div>
<div class="product-card-body">
<h3 class="product-card-name"><?= e($product['name']) ?></h3>
<p class="product-card-detail"><?= e($product['detail']) ?></p>
<?php if ((string)$product['size'] !== ''): ?>
<p class="product-card-size"><?= e($product['size']) ?></p>
<?php endif; ?>
<?php $cardUrl = ($product['url'] ?? '') ?: (($categoryUrls[$product['category']] ?? '') ?: $productCopy['store_url']); ?>
<a class="product-card-link" href="<?= e(site_href($cardUrl)) ?>"<?= site_link_attributes($cardUrl) ?>><span><?= e($productCopy['card_link']) ?></span><i data-lucide="arrow-up-right" class="site-icon" aria-hidden="true" focusable="false"></i></a>
</div>
</li>
<?php endforeach; ?>
</ul>
<p class="product-empty" hidden><?= e(t('준비 중인 상품입니다.')) ?></p>
</div>
</section>
<section class="product-section product-closing" aria-labelledby="product-closing-title">
<div class="inner">
<h2 class="product-heading" id="product-closing-title" lang="en"><?= implode('<br/>', array_map('e', $productCopy['closing_lines'])) ?></h2>
<?php $closingUrl = $productCopy['closing_url'] ?: $productCopy['store_url']; ?>
<a class="product-button" href="<?= e(site_href($closingUrl)) ?>"<?= site_link_attributes($closingUrl) ?>><span><?= e($productCopy['closing_button']) ?></span><i data-lucide="arrow-up-right" class="site-icon" aria-hidden="true" focusable="false"></i></a>
</div>
</section>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
