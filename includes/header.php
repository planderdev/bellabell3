<?php
declare(strict_types=1);

$introParts = explode(' / ', $content['intro'], 2);
$introFirst = $introParts[0] . (isset($introParts[1]) ? ' / ' : '');
$introSecond = $introParts[1] ?? '';
?>
<div id="hd">
<h1 id="hd_h1">벨아벨팜</h1>
<header class="header">
<div class="header-inner">
<div class="header-logo">
<a href="#intro">
<img alt="벨아벨팜" src="/assets/bellabelfarm-s.svg"/>
</a>
</div>
<nav class="gnb">
<ul>
<li>
<a href="#research">연구·기술</a>
<div class="gnb-depth">
<a href="#research">Lorem ipsum</a>
<a href="#family">Lorem ipsum</a>
</div>
</li>
<li>
<a href="#social">사회적 가치</a>
<div class="gnb-depth">
<a href="#social">Lorem ipsum</a>
<a href="#history">연혁</a>
<a href="#family">Lorem ipsum</a>
<a href="#global">Lorem ipsum</a>
</div>
</li>
<li>
<a href="#family">브랜드 패밀리</a>
<div class="gnb-depth">
<a href="#family">Lorem ipsum</a>
<a href="#partner">Lorem ipsum</a>
</div>
</li>
<li>
<a href="#global">글로벌</a>
<div class="gnb-depth">
<a href="#global">Lorem ipsum</a>
<a href="#intro">Lorem ipsum</a>
<a href="#family">Lorem ipsum</a>
</div>
</li>
<li>
<a href="#partner">제안 &amp; 협업</a>
<div class="gnb-depth">
<a href="#partner">Lorem ipsum</a>
</div>
</li>
</ul>
</nav>
<div class="header-util">
<div class="language-wrap">
<button class="language" type="button">
<span class="language-current">Lorem</span>
<i data-lucide="chevron-down" class="site-icon" aria-hidden="true" focusable="false"></i>
</button>
<div class="language-list">
<a href="#intro">Lorem</a>
<a href="#global">Lorem</a>
</div>
</div>
<button aria-label="전체 메뉴 열기" class="menu-button" type="button">
<span></span>
<span></span>
</button>
</div>
</div>
</header>
<nav class="all-menu" aria-label="전체 메뉴">
<div class="all-menu-inner">
<div class="all-menu-content">
<div class="all-menu-copy">
<p><?= e($introFirst) ?><br/><?= e($introSecond) ?></p>
<div class="all-menu-image">
<img alt="" aria-hidden="true" src="/assets/gray.svg"/>
</div>
</div>

<div class="all-menu-nav" data-lenis-prevent=""></div>
</div>
</div>
</nav>
</div>
