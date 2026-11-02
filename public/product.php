<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = t_deep(require __DIR__ . '/../includes/content.php');
$pageTitle = 'Product | ' . t('벨아벨팜');
$categoryNames = array_column($content['product_categories'], 'name', 'id');
$marqueeItems = ['From HALLA, For PET', ...array_column($content['product_categories'], 'menu')];
$lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
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
<p class="product-hero-label" lang="en">Product</p>
<h2 class="product-hero-title" id="product-title"><?= e(t('먹는 건')) ?><br/><?= e(t('귀엽게,')) ?><br/><?= e(t('만드는 건')) ?><br/><?= e(t('진지하게!')) ?></h2>
<p class="product-hero-description"><?= e(t('잘 먹을까? 매일 써도 좋을까?')) ?><br/><?= e(t('보호자의 질문에서 시작해, 제주의 원료와 쓰임에 맞는 제형을 연구합니다.')) ?></p>
<a class="product-button" href="#products"><span><?= e(t('제품 라인업 보기')) ?></span><i data-lucide="arrow-down" class="site-icon" aria-hidden="true" focusable="false"></i></a>
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
<h2 class="product-heading" id="products-title" lang="en">Product Family</h2>
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
<img src="<?= e($product['image']['src']) ?>" alt="<?= e($product['image']['alt']) ?>" width="800" height="800" loading="lazy" decoding="async"/>
<?php endif; ?>
<span class="product-card-tag"><?= e($categoryNames[$product['category']]) ?></span>
<span class="product-card-number" aria-hidden="true"><?= sprintf('%02d', $index + 1) ?></span>
</div>
<div class="product-card-body">
<h3 class="product-card-name"><?= e($product['name']) ?></h3>
<p class="product-card-detail"><?= e($product['detail']) ?></p>
<?php if ($product['size'] !== null): ?>
<p class="product-card-size"><?= e($product['size']) ?></p>
<?php endif; ?>
<a class="product-card-link" href="https://fromhalla.com" target="_blank" rel="noopener noreferrer"><span><?= e(t('상품 보기')) ?></span><i data-lucide="arrow-up-right" class="site-icon" aria-hidden="true" focusable="false"></i></a>
</div>
</li>
<?php endforeach; ?>
</ul>
</div>
</section>
<section class="product-section is-tinted" aria-labelledby="product-notes-title">
<div class="inner">
<h2 class="product-heading" id="product-notes-title" lang="en">Lorem ipsum</h2>
<div class="product-notes">
<?php for ($note = 1; $note <= 4; $note++): ?>
<article class="product-note">
<p class="product-note-number"><?= sprintf('%02d', $note) ?></p>
<h3 lang="en">Lorem ipsum</h3>
<p lang="en"><?= e($lorem) ?></p>
</article>
<?php endfor; ?>
</div>
</div>
</section>
<section class="product-section" aria-labelledby="product-stories-title">
<div class="inner">
<h2 class="product-heading" id="product-stories-title" lang="en">Lorem ipsum</h2>
<div class="product-story-tabs" role="tablist" aria-label="<?= e(t('제품 이야기')) ?>">
<?php foreach ($content['product_stories'] as $index => $story): ?>
<button class="product-filter product-story-tab<?= $index === 0 ? ' is-active' : '' ?>" type="button" role="tab" id="product-story-tab-<?= $index ?>" aria-controls="product-story-<?= $index ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>" lang="en"><?= sprintf('%02d', $index + 1) ?> Lorem ipsum</button>
<?php endforeach; ?>
</div>
<?php foreach ($content['product_stories'] as $index => $story): ?>
<div class="product-story-panel" id="product-story-<?= $index ?>" role="tabpanel" aria-labelledby="product-story-tab-<?= $index ?>"<?= $index === 0 ? '' : ' hidden' ?>>
<img src="<?= e($story['image']['src']) ?>" alt="<?= e($story['image']['alt']) ?>" loading="lazy" decoding="async"/>
<div class="product-story-copy">
<h3 lang="en">Lorem ipsum dolor sit amet</h3>
<p lang="en"><?= e($lorem) ?></p>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<section class="product-section is-tinted" aria-labelledby="product-points-title">
<div class="inner">
<h2 class="product-heading" id="product-points-title" lang="en">Lorem ipsum</h2>
<div class="product-points">
<?php for ($point = 1; $point <= 3; $point++): ?>
<article class="product-point">
<h3 lang="en">Lorem ipsum</h3>
<p lang="en"><?= e($lorem) ?></p>
</article>
<?php endfor; ?>
</div>
</div>
</section>
<section class="product-section product-closing" aria-labelledby="product-closing-title">
<div class="inner">
<h2 class="product-heading" id="product-closing-title" lang="en">From HALLA,<br/>For PET</h2>
<a class="product-button" href="https://fromhalla.com" target="_blank" rel="noopener noreferrer"><span><?= e(t('프롬한라 상품 보기')) ?></span><i data-lucide="arrow-up-right" class="site-icon" aria-hidden="true" focusable="false"></i></a>
</div>
</section>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
