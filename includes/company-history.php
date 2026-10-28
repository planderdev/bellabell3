<?php
declare(strict_types=1);

$companyHistory = [
    '2025' => [
        ['date' => '2025.12.17', 'text' => 'SVI 우수등급', 'photo' => null],
        ['date' => '2025.11.05', 'text' => '농심과 제주 펫 산업 협력', 'photo' => null],
        ['date' => '2025.10.28', 'text' => '제주대학교와 반려동물용 기능성 천연물 원료·제품 개발 업무협약', 'photo' => null],
        ['date' => '2025.06.16', 'text' => '대만 30만 달러 수출 계약', 'photo' => null],
        ['date' => '2025.05.08', 'text' => '싱가포르 박람회 100만 달러 수출 상담', 'photo' => null],
        ['date' => '2025.04.09', 'text' => '펫 샴푸바 싱가포르 수출', 'photo' => null],
        ['date' => '2025.02.12', 'text' => '개랑과자 싱가포르 수출', 'photo' => null],
        ['date' => '2025.01.16', 'text' => '농촌융복합산업 인증', 'photo' => null],
    ],
    '2024' => [
        ['date' => '2024.11.13', 'text' => '애월아빠들 MOU', 'photo' => null],
        ['date' => '2024.10.17', 'text' => '대만 Meow Expo 참가', 'photo' => null],
    ],
    '2022' => [
        ['date' => '2022', 'text' => '제주형 예비사회적기업 선정', 'photo' => null],
    ],
];
?>
<section class="history-section" data-section="history" id="history" aria-labelledby="history-title">
<div class="inner">
<div class="section-head">
<div class="section-category"><p lang="en">History</p></div>
<h2 class="section-title" id="history-title" data-section-title="history">연혁</h2>
</div>
<div class="history-tabs" role="tablist" aria-label="연혁 연도">
<?php foreach (array_keys($companyHistory) as $index => $year): ?>
<button class="history-tab<?= $index === 0 ? ' is-active' : '' ?>" type="button" role="tab" id="history-tab-<?= $year ?>" aria-controls="history-panel-<?= $year ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>" data-history-year="<?= $year ?>"><?= $year ?></button>
<?php endforeach; ?>
</div>
<div class="history-panels">
<?php foreach ($companyHistory as $year => $events): ?>
<div class="history-panel" id="history-panel-<?= $year ?>" role="tabpanel" aria-labelledby="history-tab-<?= $year ?>" tabindex="0"<?= $year === 2025 ? '' : ' hidden' ?>>
<div class="history-grid">
<div class="history-timeline">
<h3 class="history-year"><?= $year ?></h3>
<ol class="history-events">
<?php foreach ($events as $event): ?>
<li><time class="history-event-date" datetime="<?= e(str_replace('.', '-', $event['date'])) ?>"><?= strlen($event['date']) > 4 ? e(substr($event['date'], 5)) : '' ?></time><span><?= e($event['text']) ?></span></li>
<?php endforeach; ?>
</ol>
</div>
<div class="history-gallery" role="region" aria-label="<?= $year ?>년 연혁 사진">
<div class="history-swiper swiper" id="history-photos-<?= $year ?>">
<div class="swiper-wrapper">
<?php foreach ($events as $event): ?>
<figure class="swiper-slide history-slide">
<div class="history-photo"<?= $event['photo'] === null ? ' data-media-state="missing" role="img" aria-label="' . e($event['text']) . ' 사진 미확보"' : '' ?>>
<?php if ($event['photo'] !== null): ?>
<img src="<?= e($event['photo']['src']) ?>" alt="<?= e($event['photo']['alt']) ?>" loading="lazy" decoding="async"/>
<?php else: ?>
<i data-lucide="image" class="site-icon" width="24" height="24" stroke-width="1.5" aria-hidden="true" focusable="false"></i>
<?php endif; ?>
</div>
<figcaption class="history-caption">
<h4><?= e($event['text']) ?></h4>
<time datetime="<?= e(str_replace('.', '-', $event['date'])) ?>"><?= e($event['date']) ?></time>
</figcaption>
</figure>
<?php endforeach; ?>
</div>
</div>
<div class="history-controls">
<div class="history-navigation">
<span class="history-count" aria-label="사진 순서">1 / <?= count($events) ?></span>
<button class="history-prev" type="button" aria-label="이전 연혁 사진" aria-controls="history-photos-<?= $year ?>"<?= count($events) < 2 ? ' disabled' : '' ?>><i data-lucide="chevron-left" class="site-icon" width="20" height="20" stroke-width="1.5" aria-hidden="true" focusable="false"></i></button>
<button class="history-next" type="button" aria-label="다음 연혁 사진" aria-controls="history-photos-<?= $year ?>"<?= count($events) < 2 ? ' disabled' : '' ?>><i data-lucide="chevron-right" class="site-icon" width="20" height="20" stroke-width="1.5" aria-hidden="true" focusable="false"></i></button>
</div>
</div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</section>
