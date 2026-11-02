<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = require __DIR__ . '/../includes/content.php';
$pageTitle = 'Product | 벨아벨팜';
?>
<!doctype html><html lang="en">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="sub" lang="ko">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<main class="product-page" id="main">
<div class="inner">
<h2 class="product-page-title" lang="en">Product</h2>
<nav class="product-page-nav" aria-label="제품군">
<?php foreach ($content['product_groups'] as $group): ?>
<a href="#<?= e($group['id']) ?>"><?= e($group['name']) ?></a>
<?php endforeach; ?>
</nav>
<?php foreach ($content['product_groups'] as $group): ?>
<section class="product-group" id="<?= e($group['id']) ?>" aria-labelledby="<?= e($group['id']) ?>-title">
<p class="product-group-label"><?= e($group['label']) ?></p>
<h3 class="product-group-title" id="<?= e($group['id']) ?>-title"><?= e($group['name']) ?></h3>
<?php if ($group['lead'] !== null): ?>
<img class="product-group-lead" src="<?= e($group['lead']['src']) ?>" alt="<?= e($group['lead']['alt']) ?>" width="<?= $group['lead']['width'] ?>" height="<?= $group['lead']['height'] ?>" decoding="async"/>
<?php endif; ?>
<ul class="product-grid">
<?php foreach ($group['items'] as $item): ?>
<li class="product-card">
<div class="product-card-media"<?= $item['image'] === null ? ' data-media-state="missing" role="img" aria-label="' . e($item['name']) . ' 사진 미확보"' : '' ?>>
<?php if ($item['image'] !== null): ?>
<img src="<?= e($item['image']['src']) ?>" alt="<?= e($item['image']['alt']) ?>" width="800" height="800" loading="lazy" decoding="async"/>
<?php endif; ?>
</div>
<h4 class="product-card-name"><?= e($item['name']) ?></h4>
<?php if ($item['variant'] !== null): ?>
<p class="product-card-variant"><?= e($item['variant']) ?></p>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
</section>
<?php endforeach; ?>
</div>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
