<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = site_content();
$pageTitle = $content['brand_title'] . ' | ' . t('벨아벨팜');
$slideTitle = $content['brand_title'];
$slideTabs = $content['brand_tabs'];
$slideSets = $content['brand_slides'];
$slideGroups = $content['brand_groups'];
?>
<!doctype html><html lang="<?= e(LOCALES[locale()]['html']) ?>">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="sub has-hero-header" lang="<?= e(LOCALES[locale()]['html']) ?>" data-home="<?= e(page_url('/')) ?>">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<?php require __DIR__ . '/../includes/slide-page.php'; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
