<?php
declare(strict_types=1);

// Full-screen tabbed slides shared by /brand and /ingredients: $slideTitle, $slideTabs, $slideSets, $slideGroups.
?>
<main class="brand-page" id="main">
<div class="brand-side">
<h2 class="brand-page-title"><?= e($slideTitle) ?></h2>
<div class="brand-tabs" role="tablist" aria-label="<?= e(t('브랜드')) ?>">
<?php foreach ($slideTabs as $index => $brand): ?>
<button class="brand-tab<?= $index === 0 ? ' is-active' : '' ?>" type="button" role="tab" id="brand-tab-<?= e($brand['id']) ?>" aria-controls="brand-panel-<?= e($brand['id']) ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>" data-brand="<?= e($brand['id']) ?>"><?= e($brand['name']) ?></button>
<?php endforeach; ?>
</div>
</div>
<?php foreach ($slideTabs as $index => $brand): $slides = $slideSets[$brand['id']]; ?>
<section class="brand-panel" id="brand-panel-<?= e($brand['id']) ?>" role="tabpanel" aria-labelledby="brand-tab-<?= e($brand['id']) ?>" data-lenis-prevent=""<?= $index === 0 ? '' : ' hidden' ?>>
<div class="swiper brand-slider">
<div class="swiper-wrapper">
<?php foreach ($slides as $slideIndex => $slide): ?>
<article class="swiper-slide brand-slide<?= isset($slide['group']) ? ' has-groups' : '' ?>">
<?php if ($slide['image'] !== ''): ?>
<img class="brand-slide-media" src="<?= e($slide['image']) ?>" alt="" <?= $index === 0 && $slideIndex === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async"/>
<?php endif; ?>
<div class="brand-slide-copy">
<?php if (isset($slide['group'])): ?>
<ul class="brand-slide-groups">
<?php foreach ($slideGroups as $groupIndex => $group): ?>
<li><button class="brand-slide-group<?= $groupIndex === $slide['group'] ? ' is-active' : '' ?>" type="button" data-slide="<?= $groupIndex + 1 ?>"<?= $groupIndex === $slide['group'] ? ' aria-current="true"' : '' ?>><?= e($group) ?></button></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<div class="brand-slide-text">
<?php if (!empty($slide['stats'])): ?>
<ul class="brand-stats">
<?php foreach ($slide['stats'] as $stat): ?>
<li class="brand-stat"><strong><span class="brand-stat-number" data-count="<?= e($stat['value']) ?>"><?= e($stat['value']) ?></span><?php if ($stat['unit'] !== ''): ?><small><?= e($stat['unit']) ?></small><?php endif; ?></strong><span class="brand-stat-label"><?= e($stat['label']) ?></span></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<h3 class="brand-slide-title"><?= e($slide['title']) ?></h3>
<?php if ($slide['body'] !== ''): ?>
<p class="brand-slide-body"><?= e($slide['body']) ?></p>
<?php endif; ?>
<?php if (($slide['body_after'] ?? '') !== ''): ?>
<p class="brand-slide-body brand-slide-body--after"><?= e($slide['body_after']) ?></p>
<?php endif; ?>
</div>
</div>
</article>
<?php endforeach; ?>
</div>
</div>
<?php if (count($slides) > 1): ?>
<div class="brand-slide-arrows">
<button class="brand-slide-arrow" type="button" data-move="prev" aria-label="<?= e(t('이전 슬라이드')) ?>" disabled><i data-lucide="chevron-left" class="site-icon" aria-hidden="true" focusable="false"></i></button>
<button class="brand-slide-arrow" type="button" data-move="next" aria-label="<?= e(t('다음 슬라이드')) ?>"><i data-lucide="chevron-right" class="site-icon" aria-hidden="true" focusable="false"></i></button>
</div>
<div class="brand-slide-nav">
<?php foreach ($slides as $slideIndex => $slide): ?>
<button class="brand-slide-dot<?= $slideIndex === 0 ? ' is-active' : '' ?>" type="button" data-slide="<?= $slideIndex ?>" aria-label="<?= e($brand['name']) ?> <?= $slideIndex + 1 ?>"<?= $slideIndex === 0 ? ' aria-current="true"' : '' ?>></button>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
<?php endforeach; ?>
</main>
