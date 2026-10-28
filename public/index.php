<?php
declare(strict_types=1);
require __DIR__ . '/../includes/common.php';
$content = require __DIR__ . '/../includes/content.php';
$heroCopy = explode(' / ', $content['intro'], 2);
?>
<!doctype html><html lang="en">
<?php require __DIR__ . '/../includes/head.php'; ?>
<body class="main" lang="ko">
<?php require __DIR__ . '/../includes/header.php'; ?>
<div id="wrapper">
<div class="main-page" id="main" role="main">
<section class="main-visual" data-section="intro" id="intro">
<div class="main-slider">
<div class="swiper">
<div class="swiper-wrapper">
<?php for ($heroSlide = 1; $heroSlide <= 2; $heroSlide++): ?>
<div class="swiper-slide" data-hero-slide="<?= $heroSlide ?>">
<div class="main-slide-media">
<img alt="<?= $heroSlide === 1 ? '제주 해녀' : '농장에서 반려견과 함께한 두 사람' ?>" src="/assets/media/<?= $heroSlide === 1 ? 'hero-haenyeo.webp' : 'photo/farm/farm6.jpg' ?>" fetchpriority="<?= $heroSlide === 1 ? 'high' : 'auto' ?>" decoding="async"/>
</div>
<div class="hero-content inner">
<?php if ($heroSlide === 1): ?>
<div class="hero-logos">
<img class="hero-logo-fromhalla" alt="프롬한라" src="/assets/fromhalla.svg" width="750" height="351"/>
<img class="hero-logo-bellabelfarm" alt="벨아벨팜" src="/assets/bellabelfarm.svg" width="1155" height="506"/>
</div>
<?php endif; ?>
<h2 class="hero-title" data-copy="intro-title"><?= e($heroCopy[0]) ?></h2>
<p class="hero-description" data-copy="intro-body"><?= e($heroCopy[1]) ?></p>
</div>
</div>
<?php endfor; ?>
</div>
</div>
</div>
<div class="main-slide-info inner">
<div class="slide-progress"></div>
</div>
</section>
<section class="about-reveal-section" data-section="about-reveal" id="about-reveal" aria-labelledby="about-reveal-title">
<div class="about-reveal-stage">
<h2 class="about-reveal-title" id="about-reveal-title" data-copy="about-title"><?php foreach (explode("\n", $content['about_title']) as $lineIndex => $line): ?><?= $lineIndex > 0 ? "<br>\n" : '' ?><span class="about-reveal-line"><?= e($line) ?></span><?php endforeach; ?></h2>
<div class="about-reveal-body">
<p data-copy="about-body"><?= e($content['about_body']) ?></p>
<a class="about-reveal-link" href="#family">
<span>브랜드 소개</span>
<i data-lucide="arrow-right" class="site-icon" width="16" height="16" stroke-width="2" aria-hidden="true" focusable="false"></i>
</a>
</div>
</div>
</section>
<section class="bongbong-story-section" data-section="bongbong-story" id="bongbong-story" aria-labelledby="bongbong-story-title">
<div class="bongbong-story-stage">
<div class="bongbong-story-cards">
<figure class="bongbong-story-card bongbong-story-photo bongbong-story-photo-first">
<img src="/assets/bongbong.webp" alt="나무 곁에 있는 봉봉이" width="1600" height="2000" loading="lazy" decoding="async"/>
</figure>
<figure class="bongbong-story-card bongbong-story-photo bongbong-story-photo-second">
<img src="/assets/media/mission-bongbong.webp" alt="제주 바닷가에 앉아 있는 봉봉이" width="1536" height="1920" loading="lazy" decoding="async"/>
</figure>
<figure class="bongbong-story-card bongbong-story-photo bongbong-story-photo-third">
<img src="/assets/media/mission-beach.webp" alt="제주 모래사장을 걷는 봉봉이" width="1536" height="1920" loading="lazy" decoding="async"/>
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
<h2 class="section-title" data-fade="" data-section-title="research-legacy">연구·기술</h2>
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
<div class="section-head inner">
<div class="section-category" data-fade="">
<p lang="en">Social Value</p>
</div>
<h2 class="section-title" data-fade="" data-section-title="social" data-copy="social"><?= e($content['social']) ?></h2>
</div>
<div class="care-process inner">
<a class="care-item" href="#partner">
<div class="care-icon" data-fade="">
<i data-lucide="shield-check" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content" data-fade="">
<h3 data-copy="value-0"><?= e($content['values'][0]) ?></h3>
<p data-copy="value-body-0"><?= nl2br(e($content['value_descriptions'][0]), false) ?></p>
</div>
</a>
<a class="care-item" href="#partner">
<div class="care-icon" data-fade="">
<i data-lucide="users" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content" data-fade="">
<h3 data-copy="value-1"><?= e($content['values'][1]) ?></h3>
<p data-copy="value-body-1"><?= nl2br(e($content['value_descriptions'][1]), false) ?></p>
</div>
</a>
<a class="care-item" href="#partner">
<div class="care-icon" data-fade="">
<i data-lucide="leaf" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content" data-fade="">
<h3 data-copy="value-2"><?= e($content['values'][2]) ?></h3>
<p data-copy="value-body-2"><?= nl2br(e($content['value_descriptions'][2]), false) ?></p>
</div>
</a>
<a class="care-item" href="#partner">
<div class="care-icon" data-fade="">
<i data-lucide="plus" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content" data-fade="">
<h3 data-copy="value-3"><?= e($content['values'][3]) ?></h3>
<p data-copy="value-body-3"><?= nl2br(e($content['value_descriptions'][3]), false) ?></p>
</div>
</a>
<a class="care-item" href="#partner">
<div class="care-icon" data-fade="">
<i data-lucide="heart" class="site-icon" width="24" height="24" stroke-width="2" aria-hidden="true" focusable="false"></i>
</div>
<div class="care-content" data-fade="">
<h3 data-copy="value-4"><?= e($content['values'][4]) ?></h3>
<p data-copy="value-body-4"><?= nl2br(e($content['value_descriptions'][4]), false) ?></p>
</div>
</a>
</div>
</section>
<?php require __DIR__ . '/../includes/company-history.php'; ?>
<section class="products-section" data-section="family" id="family" aria-label="브랜드 패밀리">
<div class="products-stage">
<div class="products-bg-title" aria-hidden="true" lang="en">BRAND FAMILY</div>
<div class="products-visual">
<div class="products-gallery swiper" id="brand-photo-slider" aria-label="브랜드 사진">
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
<div class="products-thumbs" role="group" aria-label="브랜드 및 사진 선택">
<?php $brandSlideIndex = 0; foreach ($content['brands'] as $brandIndex => $brand): ?>
<div class="product-item<?= $brandIndex === 0 ? ' is-active' : '' ?>">
<button class="product-item-product" type="button" aria-label="<?= e($brand['name']) ?>" aria-controls="brand-details-<?= $brandIndex ?> brand-photo-slider" aria-expanded="<?= $brandIndex === 0 ? 'true' : 'false' ?>" data-name="<?= e($brand['name']) ?>" data-description="<?= e($brand['description']) ?>" data-first-slide="<?= $brandSlideIndex ?>">
<span class="product-item-name"><?= e($brand['name']) ?></span>
</button>
<div class="product-item-details" id="brand-details-<?= $brandIndex ?>" aria-hidden="<?= $brandIndex === 0 ? 'false' : 'true' ?>"<?= $brandIndex === 0 ? '' : ' inert' ?>>
<div class="accordion-content">
<div class="product-angle-list">
<?php foreach ($brand['photos'] as $photoIndex => $photo): ?>
<button class="product-angle<?= $brandIndex === 0 && $photoIndex === 0 ? ' is-active' : '' ?>" type="button" aria-label="<?= e($brand['name']) ?> 사진 <?= $photoIndex + 1 ?>" aria-pressed="<?= $brandIndex === 0 && $photoIndex === 0 ? 'true' : 'false' ?>" tabindex="<?= $brandIndex === 0 ? '0' : '-1' ?>" data-slide-index="<?= $brandSlideIndex + $photoIndex ?>">
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
<button class="products-prev" type="button" aria-label="이전 사진"><i data-lucide="chevron-left" class="site-icon" width="20" height="20" stroke-width="2" aria-hidden="true" focusable="false"></i></button>
<span class="products-slide-count" aria-label="사진 순서">1 / <?= $brandSlideIndex ?></span>
<button class="products-next" type="button" aria-label="다음 사진"><i data-lucide="chevron-right" class="site-icon" width="20" height="20" stroke-width="2" aria-hidden="true" focusable="false"></i></button>
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
<video aria-hidden="true" autoplay="" data-media-state="ready" loop="" muted="" playsinline="" poster="/assets/media/global-gallery.webp">

</video>
</div>
</div>
</div>
</div>
<div class="solution-content inner">
<div class="section-head">
<div class="section-category">
<p lang="en">Global</p>
</div>
<h2 class="section-title" id="global-title" data-section-title="global" data-copy="global-title"><?= e($content['global_title']) ?></h2>
</div>
<a class="solution-card" data-index="0" href="#partner">
<div class="card-info">
<strong class="card-title">Lorem ipsum dolor.</strong>
<p class="card-desc">Lorem ipsum dolor sit.<br/>Lorem ipsum dolor sit amet, consectetur.</p>
</div>
<div class="solution-card-image">
<img alt="" aria-hidden="true" class="solution-card-img" data-media-state="missing" src="/assets/gray.svg"/>
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
<div data-desc="Lorem ipsum dolor sit amet, consectetur adipiscing elit." data-href="#partner" data-image="/assets/gray.svg" data-title="Lorem ipsum"></div>
<div data-desc="Lorem ipsum dolor sit amet, consectetur adipiscing elit." data-href="#partner" data-image="/assets/gray.svg" data-title="Lorem ipsum"></div>
<div data-desc="Lorem ipsum dolor sit amet, consectetur adipiscing elit." data-href="#partner" data-image="/assets/gray.svg" data-title="Lorem ipsum"></div>
<div data-desc="Lorem ipsum dolor sit amet, consectetur adipiscing elit." data-href="#partner" data-image="/assets/gray.svg" data-title="Lorem ipsum"></div>
</div>
</div>
</div>
</section>
</div>
<section class="contact-section" data-section="partner" id="partner">
<div class="inner">
<div class="section-head">
<div class="section-category" data-fade="">
<p lang="en">Partnership</p>
</div>
<h2 class="section-title" data-fade="" data-section-title="partner">제안 &amp; 협업</h2>
<p class="section-desc" data-fade="" data-copy="partner"><?= e($content['partner']) ?></p>
<p class="contact-intro">유통·입점, 공동 연구, 해외 비즈니스까지<br/>벨아벨팜과의 협업을 제안해 주세요.</p>
<p class="contact-direct" data-copy="contact"><a href="mailto:partner@jejubellabell.com">partner@jejubellabell.com</a> · <a href="tel:0648030060">064-803-0060</a></p>
</div>
<?php require __DIR__ . '/../includes/contact-form.php'; ?>
</div>
</section>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body></html>
