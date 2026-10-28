<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/history.php';
require __DIR__ . '/../app/chrome.php';
$content = require __DIR__ . '/../app/content.php';
$materials = require __DIR__ . '/../config/materials.php';
$titles = $content['titles'];
$lorem = $content['lorem'];
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>벨아벨팜</title>
    <link rel="preload" href="/assets/fonts/PretendardVariable.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/vendor/lenis.css">
    <link rel="stylesheet" href="/assets/vendor/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/vendor/aos.css">
    <link rel="stylesheet" href="/assets/site.css">
    <link rel="stylesheet" href="/assets/motion.css">
    <link rel="stylesheet" href="/assets/sections.css">
    <link rel="stylesheet" href="/assets/chrome.css">
    <script src="/assets/vendor/gsap.min.js" defer></script>
    <script src="/assets/vendor/ScrollTrigger.min.js" defer></script>
    <script src="/assets/vendor/lenis.min.js" defer></script>
    <script src="/assets/vendor/swiper-bundle.min.js" defer></script>
    <script src="/assets/vendor/aos.js" defer></script>
    <script src="/assets/history.js" defer></script>
    <script src="/assets/motion.js" defer></script>
    <script src="/assets/chrome.js" defer></script>
</head>
<body>
<?php render_site_header($titles); ?>
<main id="main">
    <section id="intro" class="intro" data-section="intro" aria-labelledby="intro-title">
        <div class="intro-media">
            <div class="swiper hero-swiper" data-photo-slider="hero"><div class="swiper-wrapper">
                <?php foreach ($materials['hero_images'] as $image): ?><div class="swiper-slide"><?php media_frame($image, '', true); ?></div><?php endforeach ?>
            </div></div>
        </div>
        <div class="intro-copy">
        <div class="identity-row">
            <?php local_image(['src'=>'/assets/fromhalla.svg','alt'=>'프롬한라'], 'brand-logo', true); ?>
            <?php local_image(['src'=>'/assets/bellabelfarm.svg','alt'=>'벨아벨팜'], 'company-ci', true); ?>
        </div>
            <h1 id="intro-title" data-section-title="intro"><?= e($titles['intro']) ?></h1>
            <div class="section-text"><p data-copy="intro"><?= e($content['intro']) ?></p><p class="section-desc" data-lorem="intro" lang="la"><?= e($lorem) ?></p></div>
        </div>
        <div class="hero-progress" aria-hidden="true"><?php foreach ($materials['hero_images'] as $i=>$image): ?><span<?= $i===0?' class="active"':'' ?>></span><?php endforeach ?></div>
    </section>

    <section id="meaning" class="meaning" data-section="meaning" aria-labelledby="meaning-title">
        <svg class="clip-defs" width="0" height="0" aria-hidden="true"><defs><clipPath id="natural-window" clipPathUnits="userSpaceOnUse"><rect class="natural-window-rect" x="0" y="0" width="430" height="430" rx="92" ry="92"/></clipPath></defs></svg>
        <div class="meaning-media"><?php media_frame($materials['meaning_image']); ?></div>
        <div class="meaning-content"><h2 id="meaning-title" data-section-title="meaning"><?= e($titles['meaning']) ?></h2><div class="section-text"><p data-copy="meaning"><?= e($content['meaning']) ?></p><p class="section-desc" data-lorem="meaning" lang="la"><?= e($lorem) ?></p></div></div>
    </section>

    <section id="mission" class="mission section" data-section="mission" aria-labelledby="mission-title">
        <div class="section-head" data-fade><h2 id="mission-title" data-section-title="mission"><?= e($titles['mission']) ?></h2><div class="section-text"><p data-copy="mission"><?= e($content['mission']) ?></p></div></div>
        <div class="mission-layout">
            <figure class="mission-photo"><div class="swiper mission-swiper" data-photo-slider="mission"><div class="swiper-wrapper"><?php foreach ($materials['mission_images'] as $image): ?><div class="swiper-slide"><?php media_frame($image); ?></div><?php endforeach ?></div></div></figure>
            <div class="mission-story"><?php if ($materials['mission_story'] !== null && $materials['mission_story'] !== ''): ?><p data-copy="mission_story"><?= e($materials['mission_story']) ?></p><?php endif ?><p class="section-desc" data-lorem="mission" lang="la"><?= e($lorem) ?></p></div>
        </div>
    </section>

    <section id="research" class="research section" data-section="research" aria-labelledby="research-title">
        <div class="section-head inner" data-fade><h2 id="research-title" data-section-title="research"><?= e($titles['research']) ?></h2><div class="section-text"><p data-copy="research"><?= e($content['research']) ?></p><p class="section-desc" data-lorem="research" lang="la"><?= e($lorem) ?></p></div></div>
        <div class="document-window"><div class="document-grid">
            <div class="document-slot"><?php if ($materials['patents']): foreach ($materials['patents'] as $patent): media_frame($patent); endforeach; else: media_frame(null); endif ?></div>
            <div class="document-slot"><?php media_frame($materials['agreement']['image']); ?><?php if ($materials['agreement']['copy'] !== null && $materials['agreement']['copy'] !== ''): ?><p data-copy="agreement"><?= e($materials['agreement']['copy']) ?></p><?php endif ?></div>
        </div></div>
    </section>

    <section id="social" class="social section inner" data-section="social" aria-labelledby="social-title">
        <div class="section-head" data-fade><h2 id="social-title" data-section-title="social"><?= e($titles['social']) ?></h2><div class="section-text"><p data-copy="social"><?= e($content['social']) ?></p><p class="section-desc" data-lorem="social" lang="la"><?= e($lorem) ?></p></div></div>
        <ul class="value-list"><?php foreach ($content['values'] as $i=>$value): ?><li data-aos="fade-up"><h3 data-copy="value-<?= $i ?>"><?= e($value) ?></h3></li><?php endforeach ?></ul>
        <div class="social-proof" data-fade><?php if ($materials['social_assets']): foreach ($materials['social_assets'] as $asset): media_frame($asset); endforeach; else: media_frame(null); endif ?></div>
    </section>

    <section id="history" class="history inner" data-section="history" aria-labelledby="history-title">
        <div class="section-head" data-fade><h2 id="history-title" data-section-title="history"><?= e($titles['history']) ?></h2><div class="section-text"><p class="section-desc" data-lorem="history" lang="la"><?= e($lorem) ?></p></div></div>
        <?php if (!$materials['history_approved']): ?><div class="history-empty" data-fade><?php media_frame(null); ?></div><?php endif ?>
        <?php render_history($materials['history_approved'], $materials['history_photos'], $materials['history_interval_ms']); ?>
    </section>

    <section id="family" class="family section inner" data-section="family" aria-labelledby="family-title">
        <div class="family-focus" aria-hidden="true"><?php media_frame($materials['brand_image']); ?></div>
        <div class="section-head"><h2 id="family-title" data-section-title="family"><?= e($titles['family']) ?></h2><div class="section-text"><p data-copy="family"><?= e($content['family']) ?></p><p class="section-desc" data-lorem="family" lang="la"><?= e($lorem) ?></p></div></div>
        <div class="swiper family-swiper"><div class="family-grid swiper-wrapper">
            <?php foreach ($content['brands'] as $i=>$brand): ?>
                <button type="button" class="brand-card swiper-slide" data-brand-index="<?= $i ?>" aria-pressed="<?= $i===0?'true':'false' ?>">
                    <div class="brand-card-logo"><?php media_frame($brand['logo']); ?></div>
                    <div><h3 data-copy="brand-<?= $i ?>-name"><?= e($brand['name']) ?></h3><p data-copy="brand-<?= $i ?>-description"><?= e($brand['description']) ?></p></div>
                </button>
            <?php endforeach ?>
        </div></div>
    </section>

    <section id="global" class="global" data-section="global" aria-labelledby="global-title">
        <div class="global-stage"><div class="global-blue" aria-hidden="true"></div><div class="global-layout inner">
            <div class="global-copy"><h2 id="global-title" data-section-title="global"><?= e($titles['global']) ?></h2><div class="section-text"><p data-copy="global"><?= e($content['global']) ?></p><p class="section-desc" data-lorem="global" lang="la"><?= e($lorem) ?></p></div><?php if ($materials['global_copy'] !== null && $materials['global_copy'] !== ''): ?><p data-copy="global_result"><?= e($materials['global_copy']) ?></p><?php endif ?></div>
            <div class="global-media"><?php if ($materials['global_images']): foreach ($materials['global_images'] as $image): media_frame($image); endforeach; else: media_frame(null); endif ?></div>
        </div></div>
    </section>

    <section id="partner" class="partner section inner" data-section="partner" aria-labelledby="partner-title">
        <div class="partner-layout">
            <div class="partner-heading" data-fade><h2 id="partner-title" data-section-title="partner"><?= e($titles['partner']) ?></h2><div class="section-text"><p data-copy="partner"><?= e($content['partner']) ?></p><p class="section-desc" data-lorem="partner" lang="la"><?= e($lorem) ?></p></div></div>
            <div class="partner-right">
                <div class="contact-card" data-fade><span class="contact-icon" aria-hidden="true"><?php readfile(__DIR__ . '/assets/icons/mail.svg'); ?></span><p data-copy="contact"><?= e($content['contact']) ?></p></div>
                <div class="partner-form-area"></div>
            </div>
        </div>
    </section>
</main>
<?php render_site_footer($titles, $lorem); ?>
</body>
</html>
