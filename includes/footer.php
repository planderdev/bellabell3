<?php
declare(strict_types=1);
?>
<footer id="footer" class="footer">
<div class="footer-inner inner">

<div class="footer-top">
<a class="footer-logo" href="#intro">
<img alt="<?= e(t('벨아벨팜')) ?>" src="/assets/bellabelfarm.svg"/>
</a>
<nav class="footer-certifications" aria-label="<?= e(t('SNS 채널')) ?>">
<a class="footer-sns-link" href="https://www.instagram.com/from_halla/" target="_blank" rel="noopener noreferrer" aria-label="<?= e(t('인스타그램 (새 창)')) ?>">
<img src="/assets/icons/insta.svg" alt="" aria-hidden="true" width="28" height="28"/>
</a>
<a class="footer-sns-link" href="https://blog.naver.com/jejubellabell" target="_blank" rel="noopener noreferrer" aria-label="<?= e(t('네이버 블로그 (새 창)')) ?>">
<img src="/assets/icons/blog.svg" alt="" aria-hidden="true" width="28" height="28"/>
</a>
<a class="footer-sns-link" href="https://pf.kakao.com/_JTnxks" target="_blank" rel="noopener noreferrer" aria-label="<?= e(t('카카오톡 채널 (새 창)')) ?>">
<img src="/assets/icons/kakaoch.svg" alt="" aria-hidden="true" width="28" height="28"/>
</a>
</nav>
</div>

<div class="footer-bottom">
<div class="footer-left">
<nav class="footer-nav" aria-label="<?= e(t('푸터 메뉴')) ?>">
<a href="#research"><?= e(t('연구·기술')) ?></a>
<a href="#global"><?= e(t('글로벌')) ?></a>
<a href="#partner"><?= e(t('제안 & 협업')) ?></a>
</nav>
<div class="footer-info">
<p><span><?= e($content['footer']['company']) ?></span><span class="footer-info-separator" aria-hidden="true">|</span><span><?= e($content['footer']['ceo']) ?></span></p>
<p><?= e($content['footer']['privacy']) ?></p>
<p><?= e($content['footer']['business_number']) ?> <a href="https://m.fromhalla.com/article/%EB%B3%B4%EB%8F%84%EC%9E%90%EB%A3%8C/2/13641/#" target="_blank" rel="noopener noreferrer"><?= e(t('사업자정보 확인')) ?></a></p>
<p><?= e($content['footer']['mail_order']) ?></p>
<p><?= e($content['footer']['address']) ?></p>
<p><span><?= e(t('연락처:')) ?> <a href="<?= e($content['footer']['tel']) ?>"><?= e($content['footer']['phone']) ?></a></span><span class="footer-info-separator" aria-hidden="true">|</span><span><?= e(t('입점문의 및 제휴문의:')) ?> <a href="mailto:<?= e($content['footer']['email']) ?>"><?= e($content['footer']['email']) ?></a></span></p>
</div>
</div>
<div class="footer-right">
<div class="footer-as">
<strong><?= e($content['footer']['hours_title']) ?></strong>
<p class="footer-operating-hours"><?= e($content['footer']['hours']) ?></p>
<p><?= e($content['footer']['lunch']) ?><br/><?= e($content['footer']['holiday']) ?></p>
</div>
<div class="footer-social">
<p><?= e(t('카카오톡')) ?> <a href="https://pf.kakao.com/_JTnxks" target="_blank" rel="noopener noreferrer"><strong><?= e(t('@프롬한라')) ?></strong><i data-lucide="arrow-up-right" class="site-icon" aria-hidden="true" focusable="false"></i></a></p>
<p><?= e($content['footer']['kakao_note']) ?></p>
</div>
</div>
</div>
<p class="footer-copyright"><?= e($content['footer']['copyright']) ?></p>
</div>
</footer>
