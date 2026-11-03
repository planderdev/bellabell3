<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = site_content();
$pageTitle = $content['brand_title'] . ' | ' . t('벨아벨팜');
$brandSlides = $content['brand_slides'];
$brandGroups = $content['brand_groups'];
?>
<!doctype html><html lang="<?= e(LOCALES[locale()]['html']) ?>">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="sub has-hero-header" lang="<?= e(LOCALES[locale()]['html']) ?>" data-home="<?= e(page_url('/')) ?>">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<main class="brand-page" id="main">
<div class="brand-side">
<h2 class="brand-page-title"><?= e($content['brand_title']) ?></h2>
<div class="brand-tabs" role="tablist" aria-label="<?= e(t('브랜드')) ?>">
<?php foreach ($content['brand_tabs'] as $index => $brand): ?>
<button class="brand-tab<?= $index === 0 ? ' is-active' : '' ?>" type="button" role="tab" id="brand-tab-<?= e($brand['id']) ?>" aria-controls="brand-panel-<?= e($brand['id']) ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>" data-brand="<?= e($brand['id']) ?>"><?= e($brand['name']) ?></button>
<?php endforeach; ?>
</div>
</div>
<?php foreach ($content['brand_tabs'] as $index => $brand): $slides = $brandSlides[$brand['id']]; ?>
<section class="brand-panel" id="brand-panel-<?= e($brand['id']) ?>" role="tabpanel" aria-labelledby="brand-tab-<?= e($brand['id']) ?>" data-lenis-prevent=""<?= $index === 0 ? '' : ' hidden' ?>>
<div class="swiper brand-slider">
<div class="swiper-wrapper">
<?php foreach ($slides as $slideIndex => $slide): ?>
<article class="swiper-slide brand-slide<?= isset($slide['group']) ? ' has-groups' : '' ?>">
<img class="brand-slide-media" src="<?= e($slide['image']) ?>" alt="" <?= $index === 0 && $slideIndex === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async"/>
<div class="brand-slide-copy">
<?php if (isset($slide['group'])): ?>
<ul class="brand-slide-groups">
<?php foreach ($brandGroups as $groupIndex => $group): ?>
<li><button class="brand-slide-group<?= $groupIndex === $slide['group'] ? ' is-active' : '' ?>" type="button" data-slide="<?= $groupIndex + 1 ?>"<?= $groupIndex === $slide['group'] ? ' aria-current="true"' : '' ?>><?= e($group) ?></button></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<div class="brand-slide-text">
<h3 class="brand-slide-title"><?= e($slide['title']) ?></h3>
<p class="brand-slide-body"><?= e($slide['body']) ?></p>
</div>
</div>
</article>
<?php endforeach; ?>
</div>
</div>
<?php if (count($slides) > 1): ?>
<div class="brand-slide-nav">
<?php foreach ($slides as $slideIndex => $slide): ?>
<button class="brand-slide-dot<?= $slideIndex === 0 ? ' is-active' : '' ?>" type="button" data-slide="<?= $slideIndex ?>" aria-label="<?= e($brand['name']) ?> <?= $slideIndex + 1 ?>"<?= $slideIndex === 0 ? ' aria-current="true"' : '' ?>></button>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
<?php endforeach; ?>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
