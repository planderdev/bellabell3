<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = site_content();
$pageTitle = t('연구기술') . ' | ' . t('벨아벨팜');
$researchItems = site_research_items($content);
$researchTags = array_values(array_unique(array_filter(array_column($researchItems, 'tag'), 'strlen')));
?>
<!doctype html><html lang="<?= e(LOCALES[locale()]['html']) ?>">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="sub" lang="<?= e(LOCALES[locale()]['html']) ?>" data-home="<?= e(page_url('/')) ?>">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<main class="product-page research-page" id="main">
<section class="product-section research-page-head" aria-labelledby="research-page-title">
<div class="inner">
<p class="product-hero-label" lang="en"><?= e($content['labels']['research']) ?></p>
<h2 class="product-hero-title" id="research-page-title"><?= e($content['research']) ?></h2>
<p class="product-hero-description"><?= nl2br(e($content['research_statement']), false) ?></p>
</div>
</section>
<section class="product-section" id="products" aria-labelledby="research-page-title">
<div class="inner">
<?php if (count($researchTags) > 1): ?>
<div class="product-filters" role="group" aria-label="<?= e(t('자료 분류')) ?>">
<button class="product-filter is-active" type="button" data-filter="all" aria-pressed="true"><?= e(t('전체')) ?></button>
<?php foreach ($researchTags as $tagIndex => $tag): ?>
<button class="product-filter" type="button" data-filter="tag-<?= $tagIndex + 1 ?>" aria-pressed="false"><?= e($tag) ?></button>
<?php endforeach; ?>
</div>
<?php endif; ?>
<ul class="product-grid">
<?php foreach ($researchItems as $index => $item): $tagIndex = array_search($item['tag'], $researchTags, true); ?>
<li class="product-card"<?= $tagIndex === false ? '' : ' data-category="tag-' . ($tagIndex + 1) . '"' ?>>
<div class="product-card-media"<?= $item['image'] === '' ? ' data-media-state="missing"' : '' ?>>
<?php if ($item['image'] !== ''): ?>
<img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>" loading="lazy" decoding="async"/>
<?php endif; ?>
<?php if ($item['tag'] !== ''): ?><span class="product-card-tag"><?= e($item['tag']) ?></span><?php endif; ?>
<span class="product-card-number" aria-hidden="true"><?= sprintf('%02d', $index + 1) ?></span>
</div>
<div class="product-card-body">
<h3 class="product-card-name"><?= e($item['title']) ?></h3>
<?php if ($item['body'] !== ''): ?><p class="product-card-detail"><?= nl2br(e($item['body']), false) ?></p><?php endif; ?>
</div>
</li>
<?php endforeach; ?>
</ul>
</div>
</section>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
