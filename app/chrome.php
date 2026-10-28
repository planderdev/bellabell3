<?php
declare(strict_types=1);

function chrome_menu_icon(string $name): string
{
    $paths = $name === 'x'
        ? '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>'
        : '<path d="M4 9h16"/><path d="M4 15h16"/>';
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-' . e($name) . '" aria-hidden="true">' . $paths . '</svg>';
}

function render_site_header(array $titles): void
{
    $copy = require __DIR__ . '/content.php';
    $navigation = ['meaning', 'mission', 'research', 'social', 'family', 'partner'];
    $identity = ['src' => '/assets/bellabelfarm.svg', 'alt' => '벨아벨팜'];
    ?>
<header class="site-header" data-site-header>
    <div class="site-header-inner">
        <a class="site-header-logo" href="#intro"><?php local_image($identity, '', true); ?></a>
        <nav class="site-nav" aria-label="주 메뉴">
            <?php foreach ($navigation as $key): if (!isset($titles[$key])) continue; ?>
                <a href="#<?= e($key) ?>"><?= e($titles[$key]) ?></a>
            <?php endforeach ?>
        </nav>
        <button class="site-menu-toggle" type="button" aria-label="전체 메뉴 열기" aria-controls="site-menu-dialog" aria-expanded="false"><?= chrome_menu_icon('equal') ?></button>
    </div>
</header>
<dialog class="site-menu-dialog" id="site-menu-dialog" aria-label="전체 메뉴" data-lenis-prevent>
    <div class="site-menu-heading">
        <a class="site-menu-logo" href="#intro"><?php local_image($identity); ?></a>
        <button class="site-menu-close" type="button" aria-label="전체 메뉴 닫기" autofocus><?= chrome_menu_icon('x') ?></button>
    </div>
    <div class="site-menu-layout">
        <div class="site-menu-aside">
            <p><?= e($copy['intro']) ?></p>
            <?php local_image(['src' => '/assets/media/hero-haenyeo.webp', 'alt' => '제주 해녀'], 'site-menu-photo'); ?>
        </div>
        <nav class="site-menu-links" aria-label="전체 메뉴">
            <?php foreach ($titles as $key => $title): ?>
                <a href="#<?= e((string) $key) ?>"><?= e($title) ?></a>
            <?php endforeach ?>
        </nav>
    </div>
</dialog>
<?php
}

function render_site_footer(array $titles, string $lorem): void
{
    $sentences = preg_split('/(?<=[.!?])\s+/u', trim($lorem), 2);
    $shortLorem = $sentences[0] ?? $lorem;
    ?>
<footer class="site-footer">
    <div class="site-footer-top">
        <a class="site-footer-logo" href="#intro"><?php local_image(['src' => '/assets/bellabelfarm.svg', 'alt' => '벨아벨팜']); ?></a>
    </div>
    <div class="site-footer-bottom">
        <div class="site-footer-left">
            <nav class="site-footer-nav" aria-label="하단 메뉴">
                <?php foreach (['meaning', 'research', 'partner'] as $key): if (!isset($titles[$key])) continue; ?>
                    <a href="#<?= e($key) ?>"><?= e($titles[$key]) ?></a>
                <?php endforeach ?>
            </nav>
            <p class="site-footer-lorem" data-chrome-text="lorem" lang="la"><?= e($lorem) ?></p>
        </div>
        <div class="site-footer-contact">
            <p>partner@jejubellabell.com</p>
            <p>064-803-0060</p>
            <p class="site-footer-contact-lorem" data-chrome-text="lorem" lang="la"><?= e($shortLorem) ?></p>
        </div>
    </div>
</footer>
<?php
}
