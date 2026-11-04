<?php
declare(strict_types=1);

$metaTitle = $pageTitle ?? t('벨아벨팜');
$metaDescription = $content['intro_title'] . ' / ' . $content['intro_body'];
$metaPath = $_SERVER['LOCALE_PAGE'] ?? '/';
?>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($metaTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<link rel="canonical" href="<?= e(SITE_URL . page_url($metaPath)) ?>">
<?php foreach (LOCALES as $code => $language): ?>
<link rel="alternate" hreflang="<?= e($language['html']) ?>" href="<?= e(SITE_URL . page_url($metaPath, $code)) ?>">
<?php endforeach; ?>
<link rel="alternate" hreflang="x-default" href="<?= e(SITE_URL . page_url($metaPath, 'ko')) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(t('벨아벨팜')) ?>">
<meta property="og:title" content="<?= e($metaTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:url" content="<?= e(SITE_URL . page_url($metaPath)) ?>">
<meta property="og:image" content="<?= e(SITE_URL) ?>/assets/media/hero-brand-poster.jpg">
<meta property="og:image:width" content="1920"><meta property="og:image:height" content="1080">
<meta name="twitter:card" content="summary_large_image">
<link rel="preload" href="/assets/fonts/PretendardVariable.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/GodoB.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/BackGroove.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/fonts.css"><link rel="stylesheet" href="/assets/css/base.css">
<link rel="stylesheet" href="/assets/css/vendor/swiper-bundle.min.css">
<link rel="stylesheet" href="/assets/css/layout.css"><link rel="stylesheet" href="/assets/css/main.css">
<script defer src="/assets/js/vendor/gsap.min.js"></script><script defer src="/assets/js/vendor/ScrollTrigger.min.js"></script>
<script defer src="/assets/js/vendor/ScrollToPlugin.min.js"></script><script defer src="/assets/js/vendor/lenis.min.js"></script>
<script defer src="/assets/js/vendor/swiper-bundle.min.js"></script>
<script defer src="https://unpkg.com/lucide@1.63.0/dist/umd/lucide.min.js" integrity="sha384-kqSVAzIdvqiedZsb49zLXfAO7CRNm8lsCSTdNjJoKfkO3jyWSO75XMdRIvOzn3H0" crossorigin="anonymous"></script>
<script defer src="/assets/js/interaction.js"></script>
<script defer src="/assets/js/brand-gallery.js"></script>
<script defer src="/assets/js/research-slider.js"></script>
<script defer src="/assets/js/company-history.js"></script>
<script defer src="/assets/js/contact-form.js"></script>
<script defer src="/assets/js/brand-page.js"></script>
<script defer src="/assets/js/product-page.js"></script>
</head>
