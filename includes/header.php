<?php
declare(strict_types=1);

$introParts = explode(' / ', $content['intro'], 2);
$introFirst = $introParts[0] . (isset($introParts[1]) ? ' / ' : '');
$introSecond = $introParts[1] ?? '';
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
<li>
<a href="<?= e(page_url('/brand')) ?>"><?= e(t('브랜드')) ?></a>
<div class="gnb-depth">
<?php foreach ($content['brand_tabs'] as $brand): ?>
<a href="<?= e(page_url('/brand#' . $brand['id'])) ?>"><?= e($brand['name']) ?></a>
<?php endforeach; ?>
</div>
</li>
<li>
<a href="<?= e(page_url('/product')) ?>"><?= e(t('상품소개')) ?></a>
<div class="gnb-depth">
<a href="<?= e(page_url('/product#stick')) ?>"><?= e(t('프롬한라스틱')) ?></a>
<a href="<?= e(page_url('/product#biotics')) ?>"><?= e(t('바이오틱스')) ?></a>
<a href="<?= e(page_url('/product#nanotreat')) ?>"><?= e(t('나노트릿')) ?></a>
</div>
</li>
<li>
<a href="#research"><?= e(t('연구기술')) ?></a>
</li>
<li>
<a href="#partner"><?= e(t('제안&협업')) ?></a>
</li>
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
<p><?= e($introFirst) ?><br/><?= e($introSecond) ?></p>
<div class="all-menu-image">
<img alt="" aria-hidden="true" src="/assets/gray.svg"/>
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
