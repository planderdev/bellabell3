<?php
declare(strict_types=1);

// Grouped by the year each date starts with, so correcting a date in the admin moves the entry.
$companyHistory = [];
foreach ($content['history'] as $event) {
    $companyHistory[substr($event['date'], 0, 4)][] = $event;
}
krsort($companyHistory);
?>
<section class="history-section" data-section="history" id="history" aria-labelledby="history-title">
<div class="inner">
<div class="section-head">
<div class="section-category"><p lang="en"><?= e($content['labels']['history']) ?></p></div>
<h2 class="section-title" id="history-title" data-section-title="history"><?= e($content['history_title']) ?></h2>
</div>
<div class="history-stage">
<div class="history-tabs" role="tablist" aria-label="<?= e(t('연혁 연도')) ?>">
<?php foreach (array_keys($companyHistory) as $index => $year): ?>
<button class="history-tab<?= $index === 0 ? ' is-active' : '' ?>" type="button" role="tab" id="history-tab-<?= $year ?>" aria-controls="history-panel-<?= $year ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>" data-history-year="<?= $year ?>"><?= $year ?></button>
<?php endforeach; ?>
</div>
<div class="history-panels">
<?php foreach ($companyHistory as $year => $events): ?>
<div class="history-panel<?= $year === array_key_first($companyHistory) ? ' is-active' : '' ?>" id="history-panel-<?= $year ?>" role="tabpanel" aria-labelledby="history-tab-<?= $year ?>">
<h3 class="history-year"><?= $year ?></h3>
<ol class="history-events">
<?php foreach ($events as $event): ?>
<li><time class="history-event-date" datetime="<?= e(str_replace('.', '-', $event['date'])) ?>"><?= strlen($event['date']) > 4 ? e(substr($event['date'], 5)) : '' ?></time><span><?= e(($event['text'])) ?></span></li>
<?php endforeach; ?>
</ol>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
</section>
