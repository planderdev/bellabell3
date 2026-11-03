<?php
declare(strict_types=1);
require_once __DIR__ . '/research-slides.php';

$researchSlides = research_slides($content['research_slides']);
// Loop mode needs more slides than fit on a wide screen, so short lists repeat. Repeats are hidden from screen readers.
$researchLoop = count($researchSlides) > 1;
$researchTrack = [];
for ($pass = 0; $pass === 0 || ($researchLoop && count($researchTrack) < 20); $pass++) {
    foreach ($researchSlides as $slide) {
        $researchTrack[] = $slide + ['repeat' => $pass > 0];
    }
}
?>
<section class="research-section" data-section="research" id="research" aria-labelledby="research-title">
<div class="inner">
<div class="section-head" data-fade="">
<div class="section-category"><p lang="en">Research &amp; Technology</p></div>
<h2 class="section-title" id="research-title" data-section-title="research" data-copy="research"><?= e($content['research']) ?></h2>
</div>
<h3 class="research-statement" data-copy="research-statement" data-fade=""><?= nl2br(e($content['research_statement']), false) ?></h3>
</div>
<div class="research-slider" data-fade="">
<div class="research-swiper swiper" id="research-slider" aria-label="<?= e(t('연구·기술 인증 자료')) ?>">
<div class="swiper-wrapper">
<?php foreach ($researchTrack as $slide): ?>
<div class="swiper-slide research-slide"<?= $slide['repeat'] ? ' aria-hidden="true"' : '' ?>>
<figure class="research-card"><img src="<?= e($slide['src']) ?>" alt="<?= $slide['repeat'] ? '' : e($slide['alt']) ?>" width="<?= (int)$slide['width'] ?>" height="<?= (int)$slide['height'] ?>" loading="lazy" decoding="async" draggable="false"/></figure>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
</section>
