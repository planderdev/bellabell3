<?php
declare(strict_types=1);

$pagePath = $_SERVER['LOCALE_PAGE'] ?? '/';
?>
<div id="hd">
<h1 id="hd_h1"><?= e(t('벨아벨팜')) ?></h1>
<header class="header">
<div class="header-inner">
<div class="header-logo">
<a href="#intro">
<img alt="<?= e(t('벨아벨팜')) ?>" src="/assets/bellabelfarm-s.svg"/>
</a>
</div>
<nav class="gnb">
<ul>
<?php foreach (site_menu($content) as $menuItem): ?>
<li>
<a href="<?= e(site_href($menuItem['href'])) ?>"<?= site_link_attributes($menuItem['href']) ?>><?= e($menuItem['label']) ?></a>
<?php if ($menuItem['children']): ?>
<div class="gnb-depth">
<?php foreach ($menuItem['children'] as $menuChild): ?>
<a href="<?= e(site_href($menuChild['href'])) ?>"<?= site_link_attributes($menuChild['href']) ?>><?= e($menuChild['label']) ?></a>
<?php endforeach; ?>
</div>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
</nav>
<div class="header-util">
<div class="language-wrap">
<button class="language" type="button" aria-label="<?= e(t('언어 선택')) ?>">
<i data-lucide="globe" class="site-icon language-globe" aria-hidden="true" focusable="false"></i>
<span class="language-current"><?= e(LOCALES[locale()]['label']) ?></span>
<i data-lucide="chevron-down" class="site-icon language-chevron" aria-hidden="true" focusable="false"></i>
</button>
<div class="language-list">
<?php foreach (LOCALES as $code => $language): ?>
<a href="<?= e(page_url($pagePath, $code)) ?>" hreflang="<?= e($language['html']) ?>"<?= $code === locale() ? ' aria-current="true"' : '' ?>><?= e($language['label']) ?></a>
<?php endforeach; ?>
</div>
</div>
<button aria-label="<?= e(t('전체 메뉴 열기')) ?>" data-label-open="<?= e(t('전체 메뉴 열기')) ?>" data-label-close="<?= e(t('전체 메뉴 닫기')) ?>" class="menu-button" type="button">
<span></span>
<span></span>
</button>
</div>
</div>
</header>
<nav class="all-menu" aria-label="<?= e(t('전체 메뉴')) ?>">
<div class="all-menu-inner">
<div class="all-menu-content">
<div class="all-menu-copy">
<p><?= e($content['intro_title']) ?> / <br/><?= e($content['intro_body']) ?></p>
<div class="all-menu-image">
<img alt="<?= e(t('프롬한라')) ?>" src="/assets/fromhalla.svg" width="750" height="351"/>
</div>
</div>

<div class="all-menu-nav" data-lenis-prevent=""></div>
<div class="all-menu-languages">
<?php foreach (LOCALES as $code => $language): ?>
<a href="<?= e(page_url($pagePath, $code)) ?>" hreflang="<?= e($language['html']) ?>"<?= $code === locale() ? ' aria-current="true"' : '' ?>><?= e($language['label']) ?></a>
<?php endforeach; ?>
</div>
</div>
</div>
</nav>
</div>
