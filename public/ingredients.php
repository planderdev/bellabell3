<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = site_content();
$ingredients = site_ingredients($content);
$pageTitle = $content['ingredients_title'] . ' | ' . t('벨아벨팜');
?>
<!doctype html><html lang="<?= e(LOCALES[locale()]['html']) ?>">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="sub" lang="<?= e(LOCALES[locale()]['html']) ?>" data-home="<?= e(page_url('/')) ?>">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<main class="ingredients-page" id="main">
<div class="ingredients-side">
<h2 class="ingredients-title"><?= e($content['ingredients_title']) ?></h2>
<ul class="ingredients-nav">
<?php foreach ($ingredients as $item): ?>
<li><a href="#<?= e($item['id']) ?>"><?= e($item['name']) ?></a></li>
<?php endforeach; ?>
</ul>
</div>
<section class="ingredients-intro">
<video class="ingredients-video" id="ingredients-video" autoplay muted loop playsinline preload="metadata" poster="/assets/media/pillar-jeju-poster.webp" aria-label="<?= e(t('보호자와 강아지가 한라산과 제주 바다를 함께 걷는 영상')) ?>">
<source src="/assets/media/pillar-jeju.mp4" type="video/mp4"/>
</video>
<h3><?= e($content['ingredients_intro_title']) ?></h3>
<p><?= nl2br(e($content['ingredients_intro_body']), false) ?></p>
</section>
<ol class="ingredients-list">
<?php foreach ($ingredients as $index => $item): ?>
<li class="ingredient-item" id="<?= e($item['id']) ?>">
<div class="ingredient-photo"<?= $item['image'] === '' ? ' data-media-state="missing"' : '' ?>>
<?php if ($item['image'] !== ''): ?>
<img src="<?= e($item['image']) ?>" alt="" loading="lazy" decoding="async"/>
<?php endif; ?>
</div>
<div class="ingredient-copy">
<p class="ingredient-number" aria-hidden="true"><?= sprintf('%02d', $index + 1) ?></p>
<h3><?= e($item['name']) ?></h3>
<?php if ($item['body'] !== ''): ?>
<p class="ingredient-body"><?= nl2br(e($item['body']), false) ?></p>
<?php endif; ?>
</div>
</li>
<?php endforeach; ?>
</ol>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
