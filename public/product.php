<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = site_content();
$productCopy = $content['product'];
$pageTitle = 'Product | ' . t('벨아벨팜');
$categoryNames = array_column($content['product_categories'], 'name', 'id');
$marqueeItems = ['From HALLA, For PET', ...array_column($content['product_categories'], 'menu')];
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
<a class="product-button" href="#products"><span><?= e($productCopy['hero_button']) ?></span><i data-lucide="arrow-down" class="site-icon" aria-hidden="true" focusable="false"></i></a>
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
<?php foreach ($content['products'] as $index => $product): ?>
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
<a class="product-card-link" href="<?= e($productCopy['store_url']) ?>" target="_blank" rel="noopener noreferrer"><span><?= e($productCopy['card_link']) ?></span><i data-lucide="arrow-up-right" class="site-icon" aria-hidden="true" focusable="false"></i></a>
</div>
</li>
<?php endforeach; ?>
</ul>
</div>
</section>
<section class="product-section is-tinted" aria-labelledby="product-notes-title">
<div class="inner">
<h2 class="product-heading" id="product-notes-title"><?= e($productCopy['notes_heading']) ?></h2>
<div class="product-notes">
<?php foreach ($productCopy['notes'] as $noteIndex => $note): ?>
<article class="product-note">
<p class="product-note-number"><?= sprintf('%02d', $noteIndex + 1) ?></p>
<h3><?= e($note['title']) ?></h3>
<p><?= nl2br(e($note['body']), false) ?></p>
</article>
<?php endforeach; ?>
</div>
</div>
</section>
<section class="product-section" aria-labelledby="product-stories-title">
<div class="inner">
<h2 class="product-heading" id="product-stories-title"><?= e($productCopy['stories_heading']) ?></h2>
<div class="product-story-tabs" role="tablist" aria-label="<?= e(t('제품 이야기')) ?>">
<?php foreach ($content['product_stories'] as $index => $story): ?>
<button class="product-filter product-story-tab<?= $index === 0 ? ' is-active' : '' ?>" type="button" role="tab" id="product-story-tab-<?= $index ?>" aria-controls="product-story-<?= $index ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= sprintf('%02d', $index + 1) ?> <?= e($story['tab']) ?></button>
<?php endforeach; ?>
</div>
<?php foreach ($content['product_stories'] as $index => $story): ?>
<div class="product-story-panel" id="product-story-<?= $index ?>" role="tabpanel" aria-labelledby="product-story-tab-<?= $index ?>"<?= $index === 0 ? '' : ' hidden' ?>>
<img src="<?= e($story['image']['src']) ?>" alt="<?= e($story['image']['alt']) ?>" loading="lazy" decoding="async"/>
<div class="product-story-copy">
<h3><?= e($story['title']) ?></h3>
<p><?= nl2br(e($story['body']), false) ?></p>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<section class="product-section is-tinted" aria-labelledby="product-points-title">
<div class="inner">
<h2 class="product-heading" id="product-points-title"><?= e($productCopy['points_heading']) ?></h2>
<div class="product-points">
<?php foreach ($productCopy['points'] as $point): ?>
<article class="product-point">
<h3><?= e($point['title']) ?></h3>
<p><?= nl2br(e($point['body']), false) ?></p>
</article>
<?php endforeach; ?>
</div>
</div>
</section>
<section class="product-section product-closing" aria-labelledby="product-closing-title">
<div class="inner">
<h2 class="product-heading" id="product-closing-title" lang="en"><?= implode('<br/>', array_map('e', $productCopy['closing_lines'])) ?></h2>
<a class="product-button" href="<?= e($productCopy['store_url']) ?>" target="_blank" rel="noopener noreferrer"><span><?= e($productCopy['closing_button']) ?></span><i data-lucide="arrow-up-right" class="site-icon" aria-hidden="true" focusable="false"></i></a>
</div>
</section>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
