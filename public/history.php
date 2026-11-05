<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = site_content();
$pageTitle = $content['history_title'] . ' | ' . t('벨아벨팜');
// Grouped by the year each date starts with, so correcting a date in the admin moves the entry.
$companyHistory = [];
foreach ($content['history'] as $event) {
    $companyHistory[substr($event['date'], 0, 4)][] = $event;
}
krsort($companyHistory);
?>
<!doctype html><html lang="<?= e(LOCALES[locale()]['html']) ?>">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="sub" lang="<?= e(LOCALES[locale()]['html']) ?>" data-home="<?= e(page_url('/')) ?>">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<main class="product-page research-page" id="main">
<section class="product-section research-page-head" aria-labelledby="history-title">
<div class="inner">
<p class="product-hero-label" lang="en"><?= e($content['labels']['history']) ?></p>
<h2 class="product-hero-title" id="history-title"><?= e($content['history_title']) ?></h2>
</div>
</section>
<section class="history-section" id="history" aria-labelledby="history-title">
<div class="inner">
<div class="history-panels">
<?php foreach ($companyHistory as $year => $events): ?>
<div class="history-panel">
<h3 class="history-year"><?= $year ?></h3>
<ol class="history-events">
<?php foreach ($events as $event): ?>
<li><time class="history-event-date" datetime="<?= e(str_replace('.', '-', $event['date'])) ?>"><?= strlen($event['date']) > 4 ? e(substr($event['date'], 5)) : '' ?></time><span><?= e($event['text']) ?></span></li>
<?php endforeach; ?>
</ol>
</div>
<?php endforeach; ?>
</div>
</div>
</section>
</main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
