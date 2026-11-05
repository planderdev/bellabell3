<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = site_content();
$pageTitle = t('연구기술') . ' | ' . t('벨아벨팜');
$researchItems = site_research_items($content);
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
<ul class="product-grid research-images">
<?php foreach ($researchItems as $item): ?>
<?php if ($item['image'] !== ''): ?>
<li class="product-card"><div class="product-card-media"><img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>" loading="lazy" decoding="async"/></div></li>
<?php endif; ?>
<?php endforeach; ?>
</ul>
</div>
</section>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
