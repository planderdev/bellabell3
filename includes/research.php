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
</section>
