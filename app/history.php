<?php
declare(strict_types=1);

/**
 * Render explicitly adopted history only. Candidate records never reach HTML.
 *
 * @param array $approved Records with date, text and status = approved.
 * @param array $photos Confirmed local images grouped by year.
 */
function render_history(array $approved, array $photos, ?int $intervalMs): void
{
    $byYear = [];
    foreach ($approved as $record) {
        if (!is_array($record) || ($record['status'] ?? null) !== 'approved') {
            continue;
        }
        $date = $record['date'] ?? null;
        $text = $record['text'] ?? null;
        if (!is_string($date) || !is_string($text) || $text === '' || !preg_match('/^([0-9]{4})(?:\.[0-9]{2}\.[0-9]{2})?$/D', $date, $match)) {
            continue;
        }
        $byYear[$match[1]][] = ['date' => $date, 'text' => $text];
    }
    if ($byYear === []) {
        return;
    }
    krsort($byYear, SORT_STRING);
    foreach ($byYear as &$records) {
        usort($records, static fn(array $left, array $right): int => strcmp($right['date'], $left['date']));
    }
    unset($records);
    $years = array_keys($byYear);
    $interval = $intervalMs !== null && $intervalMs > 0 ? $intervalMs : null;
    ?>
<div data-history>
    <div class="history-tabs" role="tablist">
        <?php foreach ($years as $index => $year): ?>
            <button type="button" id="history-tab-<?= e((string) $year) ?>" role="tab" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="history-panel-<?= e((string) $year) ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= e((string) $year) ?></button>
        <?php endforeach ?>
    </div>
    <?php foreach ($byYear as $year => $records): ?>
        <div class="history-panel" id="history-panel-<?= e((string) $year) ?>" role="tabpanel" aria-labelledby="history-tab-<?= e((string) $year) ?>" tabindex="0"<?= $year !== $years[0] ? ' hidden' : '' ?>>
            <div class="history-photo" data-history-slideshow<?= $interval !== null ? ' data-history-interval="' . e((string) $interval) . '"' : '' ?>>
                <?php
                $slideIndex = 0;
                foreach (($photos[$year] ?? []) as $image) {
                    if (!is_array($image)) {
                        continue;
                    }
                    ob_start();
                    local_image($image);
                    $imageHtml = ob_get_clean();
                    if ($imageHtml === '') {
                        continue;
                    }
                    echo '<div data-history-slide' . ($slideIndex > 0 ? ' hidden' : '') . '>' . $imageHtml . '</div>';
                    $slideIndex++;
                }
                ?>
            </div>
            <ol class="history-events">
                <?php foreach ($records as $record): ?>
                    <li><time datetime="<?= e(str_replace('.', '-', $record['date'])) ?>"><?= e($record['date']) ?></time><span><?= e($record['text']) ?></span></li>
                <?php endforeach ?>
            </ol>
        </div>
    <?php endforeach ?>
</div>
<?php
}
