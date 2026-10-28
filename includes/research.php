<?php
declare(strict_types=1);
?>
<section class="research-section" data-section="research" id="research" aria-labelledby="research-title">
<div class="inner">
<div class="section-head" data-fade="">
<div class="section-category"><p lang="en">Research &amp; Technology</p></div>
<h2 class="section-title" id="research-title" data-section-title="research" data-copy="research"><?= e($content['research']) ?></h2>
</div>
<div class="research-layout">
<div class="research-copy" data-fade="">
<h3 data-copy="research-statement"><?= e($content['research_statement']) ?></h3>
<?php foreach ($content['research_body'] as $index => $paragraph): ?>
<p data-copy="research-body-<?= $index ?>"><?= nl2br(e($paragraph), false) ?></p>
<?php endforeach; ?>
</div>
<figure class="research-patent" data-fade="">
<div class="research-patent-image">
<img src="/assets/media/patents/vegan-cookie-application.jpg" alt="반려견용 비건 사료 조성물 특허 출원사실증명원" width="1624" height="2195" loading="lazy" decoding="async"/>
</div>
<figcaption>반려견용 비건 사료 조성물 · 출원사실증명원</figcaption>
</figure>
</div>
</div>
<div class="research-partners" role="region" aria-label="함께하는 파트너" tabindex="0">
<div class="research-partner-track">
<?php for ($copy = 0; $copy < 2; $copy++): ?>
<ul class="research-partner-group"<?= $copy === 1 ? ' aria-hidden="true"' : ' aria-label="파트너 로고"' ?>>
<?php foreach ($content['research_partners'] as $partner): ?>
<li class="research-partner-logo"><img src="<?= e($partner['src']) ?>" alt="<?= $copy === 0 ? e($partner['alt']) : '' ?>" width="190" height="52" loading="eager" fetchpriority="low" decoding="async"/></li>
<?php endforeach; ?>
</ul>
<?php endfor; ?>
</div>
</div>
</section>
