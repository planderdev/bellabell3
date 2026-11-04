<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = site_content();
?>
<!doctype html><html lang="<?= e(LOCALES[locale()]['html']) ?>">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="main" lang="<?= e(LOCALES[locale()]['html']) ?>" data-home="<?= e(page_url('/')) ?>">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<div class="main-page" id="main" role="main">
<section class="main-visual" data-section="intro" id="intro">
<div class="main-slider">
<div class="main-slide-media">
<video id="hero-video" autoplay muted loop playsinline preload="auto" poster="/assets/media/hero-brand-poster.jpg" aria-hidden="true">
<source src="/assets/media/hero-brand-720.mp4" type="video/mp4" media="(max-width: 970px)"/>
<source src="/assets/media/hero-brand-1080.mp4" type="video/mp4"/>
</video>
</div>
<div class="hero-content inner">
<div class="hero-logos">
<img class="hero-logo-fromhalla" alt="<?= e(t('프롬한라')) ?>" src="/assets/fromhalla.svg" width="750" height="351"/>
<img class="hero-logo-bellabelfarm" alt="<?= e(t('벨아벨팜')) ?>" src="/assets/bellabelfarm.svg" width="1155" height="506"/>
</div>
<h2 class="hero-title" data-copy="intro-title"><?= e($content['intro_title']) ?></h2>
<p class="hero-description" data-copy="intro-body"><?= e($content['intro_body']) ?></p>
</div>
</div>
</section>
<section class="about-reveal-section" data-section="about-reveal" id="about-reveal" aria-labelledby="about-reveal-title">
<img class="about-reveal-art" src="<?= e($content['about_image']) ?>" alt="<?= e(t('잠수 안경을 쓴 흰 고양이와 테왁, 물고기 떼가 있는 제주 바다')) ?>" decoding="async"/>
<div class="about-reveal-stage">
<h2 class="about-reveal-title" id="about-reveal-title" data-copy="about-title"><?php foreach (explode("\n", $content['about_title']) as $lineIndex => $line): ?><?= $lineIndex > 0 ? "<br>\n" : '' ?><span class="about-reveal-line"><?= str_replace(array_map('e', $content['about_accents']), array_map(fn($accent) => '<strong class="about-reveal-accent">' . e($accent) . '</strong>', $content['about_accents']), e($line)) ?></span><?php endforeach; ?><br>
<span class="about-reveal-line about-reveal-highlight" lang="en"><?= e($content['about_highlight']) ?></span></h2>
<div class="about-reveal-links">
<?php foreach ($content['about_links'] as $aboutLink): ?>
<a class="about-reveal-link" href="<?= e(str_starts_with($aboutLink['href'], '/') ? page_url($aboutLink['href']) : $aboutLink['href']) ?>"<?= str_starts_with($aboutLink['href'], 'http') ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
<span><?= e($aboutLink['label']) ?></span>
<i data-lucide="arrow-right" class="site-icon" width="16" height="16" stroke-width="2" aria-hidden="true" focusable="false"></i>
</a>
<?php endforeach; ?>
</div>
</div>
</section>
<section class="pillars-section" data-section="pillars" id="pillars" aria-label="<?= e(t('벨아벨팜의 사업 분야')) ?>">
<div class="pillars-media">
<video class="pillars-panel is-active" id="pillar-video" muted loop playsinline preload="metadata" poster="/assets/media/pillar-ragdoll-poster.webp" width="1280" height="660" aria-label="<?= e(t('랙돌 고양이가 프롬한라스틱 앞에서 입맛을 다시는 영상')) ?>">
<source src="/assets/media/pillar-ragdoll.mp4" type="video/mp4"/>
</video>
<video class="pillars-panel" id="pillar-skin-video" muted loop playsinline preload="none" poster="/assets/media/pillar-skincare-poster.webp" aria-label="<?= e(t('보호자가 비숑의 볼을 티티패드로 닦아 주는 영상')) ?>">
<source src="/assets/media/pillar-skincare.mp4" type="video/mp4"/>
</video>
<video class="pillars-panel" id="pillar-sustain-video" muted loop playsinline preload="none" poster="/assets/media/pillar-jeju-poster.webp" aria-label="<?= e(t('보호자와 강아지가 한라산과 제주 바다를 함께 걷는 영상')) ?>">
<source src="/assets/media/pillar-jeju.mp4" type="video/mp4"/>
</video>
</div>
<div class="pillars-labels" lang="en">
<button class="pillars-label pillars-label-food is-active" id="pillar-video-toggle" type="button" aria-controls="pillar-video" aria-pressed="true">
<span><?= e($content['pillars'][0]) ?></span>
<i data-lucide="play" class="site-icon pillars-icon-play" aria-hidden="true" focusable="false"></i>
<i data-lucide="pause" class="site-icon pillars-icon-pause" aria-hidden="true" focusable="false"></i>
</button>
<button class="pillars-label pillars-label-skin" type="button" aria-controls="pillar-skin-video" aria-pressed="false">
<span><?= e($content['pillars'][1]) ?></span>
<i data-lucide="play" class="site-icon pillars-icon-play" aria-hidden="true" focusable="false"></i>
<i data-lucide="pause" class="site-icon pillars-icon-pause" aria-hidden="true" focusable="false"></i>
</button>
<button class="pillars-label pillars-label-sustain" type="button" aria-controls="pillar-sustain-video" aria-pressed="false">
<span><?= e($content['pillars'][2]) ?></span>
<i data-lucide="play" class="site-icon pillars-icon-play" aria-hidden="true" focusable="false"></i>
<i data-lucide="pause" class="site-icon pillars-icon-pause" aria-hidden="true" focusable="false"></i>
</button>
</div>
</section>
<section class="bongbong-story-section" data-section="bongbong-story" id="bongbong-story" aria-labelledby="bongbong-story-title">
<div class="bongbong-story-stage">
<svg class="bongbong-story-word" viewBox="0 0 1000 170" aria-hidden="true" focusable="false"><text x="500" y="150" textLength="980" lengthAdjust="spacingAndGlyphs" text-anchor="middle">BONGBONG</text></svg>
<svg class="bongbong-story-hello" viewBox="0 0 1000 170" aria-hidden="true" focusable="false"><text x="10" y="150" textLength="515" lengthAdjust="spacingAndGlyphs">HELLO</text></svg>
<div class="bongbong-story-cat" aria-hidden="true">
<div class="bongbong-story-cat-hop">
<?php foreach (['drum', 'arm', 'body', 'head'] as $catPart): ?>
<img class="bongbong-story-cat-<?= $catPart ?>" src="/assets/media/bongbong/cat-<?= $catPart ?>.webp" alt="" width="1100" height="1111" decoding="async"/>
<?php endforeach; ?>
</div>
</div>
<div class="bongbong-story-pops" aria-hidden="true">
<?php foreach (['blue', 'orange', 'green', 'green-open'] as $stick): ?>
<img class="bongbong-story-pop bongbong-story-pop-<?= $stick ?>" src="/assets/media/bongbong/stick-<?= $stick ?>.webp" alt="" decoding="async"/>
<?php endforeach; ?>
<?php for ($spark = 0; $spark < 14; $spark++): ?>
<span class="bongbong-story-spark"></span>
<?php endfor; ?>
</div>
<div class="bongbong-story-waves" aria-hidden="true">
<?php foreach (['far', 'mid', 'near', 'shore'] as $wave): ?>
<svg class="bongbong-story-wave bongbong-story-wave-<?= $wave ?>" viewBox="0 0 2880 200" preserveAspectRatio="none" focusable="false"><path d="M0 100C120 40 240 40 360 100S600 160 720 100 960 40 1080 100 1320 160 1440 100 1680 40 1800 100 2040 160 2160 100 2400 40 2520 100 2760 160 2880 100V200H0Z"/></svg>
<?php endforeach; ?>
</div>
<div class="bongbong-story-cards">
<figure class="bongbong-story-card bongbong-story-photo bongbong-story-photo-first">
<img src="<?= e($content['bongbong_photos'][0]['src']) ?>" alt="<?= e($content['bongbong_photos'][0]['alt']) ?>" width="1600" height="2000" loading="lazy" decoding="async"/>
</figure>
<figure class="bongbong-story-card bongbong-story-photo bongbong-story-photo-second">
<img src="<?= e($content['bongbong_photos'][1]['src']) ?>" alt="<?= e($content['bongbong_photos'][1]['alt']) ?>" width="1536" height="1920" loading="lazy" decoding="async"/>
</figure>
<figure class="bongbong-story-card bongbong-story-photo bongbong-story-photo-third">
<img src="<?= e($content['bongbong_photos'][2]['src']) ?>" alt="<?= e($content['bongbong_photos'][2]['alt']) ?>" width="1536" height="1920" loading="lazy" decoding="async"/>
</figure>
<article class="bongbong-story-card bongbong-story-copy">
<h2 id="bongbong-story-title" data-copy="bongbong-title"><?= nl2br(e($content['bongbong_title']), false) ?></h2>
<p data-copy="bongbong-body"><?= nl2br(e($content['bongbong_body']), false) ?></p>
</article>
</div>
</div>
</section>
<section class="standard-section" data-section="research-legacy" id="research-legacy" hidden inert aria-hidden="true">
<div class="section-head inner">
<div class="section-category" data-fade="">
<p lang="en">Research &amp; Technology</p>
</div>
<h2 class="section-title" data-fade="" data-section-title="research-legacy"><?= e(t('연구·기술')) ?></h2>
<p class="section-desc" data-fade=""><span data-copy="research"><?= e($content['research']) ?></span><br/><span data-lorem="research" lang="la"><?= e($content['lorem']) ?></span></p>
</div>
<div class="standard-slider">
<div class="standard-track">
<a class="standard-card" href="#partner">
<div class="standard-card-image">
<img alt="" aria-hidden="true" data-media-state="missing" src="/assets/gray.svg"/>
</div>
<div class="card-info">
<span class="more-btn"><i data-lucide="plus" class="site-icon" aria-hidden="true" focusable="false"></i></span>
<h3 class="card-title">Lorem ipsum dolor.</h3>
<p class="card-desc">Lorem ipsum dolor sit.<br/>Lorem ipsum dolor sit amet, consectetur.</p>
</div>
</a>
<a class="standard-card" href="#partner">
<div class="standard-card-image">
<img alt="" aria-hidden="true" data-media-state="missing" src="/assets/gray.svg"/>
</div>
<div class="card-info">
<span class="more-btn"><i data-lucide="plus" class="site-icon" aria-hidden="true" focusable="false"></i></span>
<h3 class="card-title">Lorem ipsum dolor.</h3>
<p class="card-desc">Lorem ipsum dolor sit amet, consectetur.<br/>Lorem ipsum dolor sit.</p>
</div>
</a>
<a class="standard-card" href="#partner">
<div class="standard-card-image">
<img alt="" aria-hidden="true" data-media-state="missing" src="/assets/gray.svg"/>
</div>
<div class="card-info">
<span class="more-btn"><i data-lucide="plus" class="site-icon" aria-hidden="true" focusable="false"></i></span>
<h3 class="card-title">Lorem ipsum.</h3>
<p class="card-desc">Lorem ipsum dolor sit amet.<br/>Lorem ipsum dolor sit.</p>
</div>
</a>
<a class="standard-card" href="#partner">
<div class="standard-card-image">
<img alt="" aria-hidden="true" data-media-state="missing" src="/assets/gray.svg"/>
</div>
<div class="card-info">
<span class="more-btn"><i data-lucide="plus" class="site-icon" aria-hidden="true" focusable="false"></i></span>
<h3 class="card-title">Lorem ipsum.</h3>
<p class="card-desc">Lorem ipsum dolor sit amet, consectetur.<br/>Lorem ipsum dolor sit amet, consectetur.</p>
</div>
</a>
</div>
</div>
</section>
<?php require __DIR__ . '/../includes/research.php'; ?>
<section class="care-section" data-section="social" id="social">
<img class="care-backdrop" src="<?= e($content['social_image']) ?>" alt="" loading="lazy" decoding="async"/>
<div class="section-head inner">
<div class="section-category" data-fade="">
<p lang="en"><?= e($content['labels']['social']) ?></p>
</div>
<h2 class="section-title" data-fade="" data-section-title="social" data-copy="social"><?= e($content['social']) ?></h2>
<p class="section-desc" data-fade="" data-copy="social-body"><?= nl2br(e($content['social_body']), false) ?></p>
</div>
<div class="care-process inner">
<a class="care-item" href="#partner" data-fade="" data-fade-delay="0.00">
<div class="care-icon">
<i data-lucide="shield-check" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content">
<h3 data-copy="value-0"><?= e($content['values'][0]) ?></h3>
<p data-copy="value-body-0"><?= nl2br(e($content['value_descriptions'][0]), false) ?></p>
</div>
</a>
<a class="care-item" href="#partner" data-fade="" data-fade-delay="0.18">
<div class="care-icon">
<i data-lucide="users" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content">
<h3 data-copy="value-1"><?= e($content['values'][1]) ?></h3>
<p data-copy="value-body-1"><?= nl2br(e($content['value_descriptions'][1]), false) ?></p>
</div>
</a>
<a class="care-item" href="#partner" data-fade="" data-fade-delay="0.36">
<div class="care-icon">
<i data-lucide="leaf" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content">
<h3 data-copy="value-2"><?= e($content['values'][2]) ?></h3>
<p data-copy="value-body-2"><?= nl2br(e($content['value_descriptions'][2]), false) ?></p>
</div>
</a>
<a class="care-item" href="#partner" data-fade="" data-fade-delay="0.54">
<div class="care-icon">
<i data-lucide="plus" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content">
<h3 data-copy="value-3"><?= e($content['values'][3]) ?></h3>
<p data-copy="value-body-3"><?= nl2br(e($content['value_descriptions'][3]), false) ?></p>
</div>
</a>
<a class="care-item" href="#partner" data-fade="" data-fade-delay="0.72">
<div class="care-icon">
<i data-lucide="heart" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content">
<h3 data-copy="value-4"><?= e($content['values'][4]) ?></h3>
<p data-copy="value-body-4"><?= nl2br(e($content['value_descriptions'][4]), false) ?></p>
</div>
</a>
</div>
</section>
<?php require __DIR__ . '/../includes/company-history.php'; ?>
<section class="products-section" data-section="family" id="family" aria-label="<?= e(t('브랜드 패밀리')) ?>">
<div class="products-stage">
<div class="products-bg-title" aria-hidden="true" lang="en">BRAND FAMILY</div>
<div class="products-visual">
<div class="products-gallery swiper" id="brand-photo-slider" aria-label="<?= e(t('브랜드 사진')) ?>">
<div class="swiper-wrapper">
<?php $brandSlideIndex = 0; foreach ($content['brands'] as $brandIndex => $brand): ?>
<?php foreach ($brand['photos'] as $photoIndex => $photo): ?>
<div class="swiper-slide products-photo-slide" data-brand-index="<?= $brandIndex ?>" data-photo-index="<?= $photoIndex ?>" data-slide-index="<?= $brandSlideIndex++ ?>">
<img src="<?= e($photo['src']) ?>" alt="<?= e($photo['alt']) ?>" loading="lazy" decoding="async"/>
</div>
<?php endforeach; endforeach; ?>
</div>
</div>
</div>
<div class="products-content inner">
<div class="section-head">
<h2 class="section-title products-intro-title" data-section-title="family" data-copy="family"><?= e($content['family']) ?></h2>
<div class="products-brand-heading" aria-live="polite" aria-atomic="true">
<p class="products-brand-subtitle" data-copy="brand-subtitle"><?= e($content['brands'][0]['description']) ?></p>
<h2 class="section-title products-brand-title" data-copy="brand-label"><?= e($content['brands'][0]['name']) ?></h2>
</div>
</div>
</div>
<div class="products-thumbs" role="group" aria-label="<?= e(t('브랜드 및 사진 선택')) ?>">
<?php $brandSlideIndex = 0; foreach ($content['brands'] as $brandIndex => $brand): ?>
<div class="product-item<?= $brandIndex === 0 ? ' is-active' : '' ?>">
<button class="product-item-product" type="button" aria-label="<?= e($brand['name']) ?>" aria-controls="brand-details-<?= $brandIndex ?> brand-photo-slider" aria-expanded="<?= $brandIndex === 0 ? 'true' : 'false' ?>" data-name="<?= e($brand['name']) ?>" data-description="<?= e($brand['description']) ?>" data-first-slide="<?= $brandSlideIndex ?>">
<span class="product-item-name"><?= e($brand['name']) ?></span>
</button>
<div class="product-item-details" id="brand-details-<?= $brandIndex ?>" aria-hidden="<?= $brandIndex === 0 ? 'false' : 'true' ?>"<?= $brandIndex === 0 ? '' : ' inert' ?>>
<div class="accordion-content">
<div class="product-angle-list">
<?php foreach ($brand['photos'] as $photoIndex => $photo): ?>
<button class="product-angle<?= $brandIndex === 0 && $photoIndex === 0 ? ' is-active' : '' ?>" type="button" aria-label="<?= e($brand['name']) ?> <?= e(t('사진')) ?> <?= $photoIndex + 1 ?>" aria-pressed="<?= $brandIndex === 0 && $photoIndex === 0 ? 'true' : 'false' ?>" tabindex="<?= $brandIndex === 0 ? '0' : '-1' ?>" data-slide-index="<?= $brandSlideIndex + $photoIndex ?>">
<img src="<?= e($photo['src']) ?>" alt="" loading="lazy" decoding="async"/>
</button>
<?php endforeach; ?>
</div>
</div>
</div>
</div>
<?php $brandSlideIndex += count($brand['photos']); endforeach; ?>
</div>
<div class="products-gallery-controls">
<button class="products-prev" type="button" aria-label="<?= e(t('이전 사진')) ?>"><i data-lucide="chevron-left" class="site-icon" width="20" height="20" stroke-width="2" aria-hidden="true" focusable="false"></i></button>
<span class="products-slide-count" aria-label="<?= e(t('사진 순서')) ?>">1 / <?= $brandSlideIndex ?></span>
<button class="products-next" type="button" aria-label="<?= e(t('다음 사진')) ?>"><i data-lucide="chevron-right" class="site-icon" width="20" height="20" stroke-width="2" aria-hidden="true" focusable="false"></i></button>
</div>
</div>
</section>
<section class="solution-section" data-section="global" id="global" aria-labelledby="global-title">
<div class="solution-sticky">
<div class="solution-intro">
<div class="solution-media-wrap">
<svg aria-hidden="true" class="solution-media-svg">
<defs>
<clipPath clipPathUnits="userSpaceOnUse" id="solutionClip" class="solution-clip-shape" data-mask-width="233" data-mask-height="141">
<!-- Mountain mark from /assets/bellabelfarm-s.svg. -->
<rect x="99.65" y="9.83" width="33.43" height="33.43" rx="7.88" ry="7.88" transform="translate(15.31 90.06) rotate(-45)"/>
<path d="M178.38,66.33l50.44,51.3c8.4,8.54,2.35,22.97-9.63,22.97H13.53c-11.98,0-18.03-14.43-9.63-22.97l50.44-51.3-.2.2c3.11-3.38,6.15-6.95,12.38-6.95,12.45,0,12.45,13.5,24.91,13.5s12.46-13.5,24.92-13.5,12.45,13.5,24.91,13.5,12.46-13.5,24.93-13.5c6.15,0,9.13,3.42,12.2,6.75Z"/>
</clipPath>
</defs>
</svg>
<div class="solution-media-vid">
<div class="thumb">
<video aria-hidden="true" autoplay="" data-media-state="ready" loop="" muted="" playsinline="" poster="<?= e($content['global_image']) ?>">

</video>
</div>
</div>
</div>
</div>
<div class="solution-content inner">
<div class="section-head">
<div class="section-category">
<p lang="en"><?= e($content['labels']['global']) ?></p>
</div>
<h2 class="section-title" id="global-title" data-section-title="global" data-copy="global-title"><?= e($content['global_title']) ?></h2>
</div>
<a class="solution-card" data-index="0" href="#partner">
<div class="card-info">
<strong class="card-title"><?= e($content['global_cards'][0]['title']) ?></strong>
<p class="card-desc"><?= nl2br(e($content['global_cards'][0]['desc']), false) ?></p>
</div>
<div class="solution-card-image">
<img alt="" aria-hidden="true" class="solution-card-img" data-media-state="<?= $content['global_cards'][0]['image'] === '/assets/gray.svg' ? 'missing' : 'ready' ?>" src="<?= e($content['global_cards'][0]['image']) ?>"/>
<span class="more-btn"><i data-lucide="plus" class="site-icon" aria-hidden="true" focusable="false"></i></span>
</div>
<div class="solution-card-pagination">
<span class="active"></span>
<span></span>
<span></span>
<span></span>
</div>
</a>
<div class="solution-card-data">
<?php foreach ($content['global_cards'] as $globalCard): ?>
<div data-desc="<?= e($globalCard['desc']) ?>" data-href="#partner" data-image="<?= e($globalCard['image']) ?>" data-title="<?= e($globalCard['title']) ?>"></div>
<?php endforeach; ?>
</div>
</div>
</div>
</section>
</div>
<section class="contact-section" data-section="partner" id="partner">
<div class="inner">
<div class="section-head">
<div class="section-category" data-fade="">
<p lang="en"><?= e($content['labels']['partner']) ?></p>
</div>
<h2 class="section-title" data-fade="" data-section-title="partner"><?= e($content['partner_title']) ?></h2>
<p class="section-desc" data-fade="" data-copy="partner"><?= e($content['partner']) ?></p>
<p class="contact-intro"><?= e($content['contact_intro'][0]) ?><br/><?= e($content['contact_intro'][1]) ?></p>
<p class="contact-direct" data-copy="contact"><a href="mailto:<?= e($content['footer']['email']) ?>"><?= e($content['footer']['email']) ?></a> · <a href="<?= e($content['footer']['tel']) ?>"><?= e($content['footer']['phone']) ?></a></p>
</div>
<?php require __DIR__ . '/../includes/contact-form.php'; ?>
</div>
</section>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
