<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = require __DIR__ . '/../includes/content.php';
$pageTitle = '브랜드 소개 | 벨아벨팜';
?>
<!doctype html><html lang="en">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="sub" lang="ko">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<main class="brand-page" id="main">
<div class="inner">
<h2 class="brand-page-title">브랜드 소개</h2>
<div class="brand-tabs" role="tablist" aria-label="브랜드">
<?php foreach ($content['brand_tabs'] as $index => $brand): ?>
<button class="brand-tab<?= $index === 0 ? ' is-active' : '' ?>" type="button" role="tab" id="brand-tab-<?= e($brand['id']) ?>" aria-controls="brand-panel-<?= e($brand['id']) ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>" data-brand="<?= e($brand['id']) ?>"><?= e($brand['name']) ?></button>
<?php endforeach; ?>
</div>
<div class="brand-panels">
<?php foreach ($content['brand_tabs'] as $index => $brand): ?>
<section class="brand-panel" id="brand-panel-<?= e($brand['id']) ?>" role="tabpanel" aria-labelledby="brand-tab-<?= e($brand['id']) ?>"<?= $index === 0 ? '' : ' hidden' ?>>
<?php if ($brand['description'] !== null): ?>
<p class="brand-panel-description"><?= e($brand['description']) ?></p>
<?php endif; ?>
<h3 class="brand-panel-title"><?= e($brand['name']) ?></h3>
<div class="brand-panel-body" data-media-state="missing"></div>
</section>
<?php endforeach; ?>
</div>
</div>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
