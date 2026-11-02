<?php
declare(strict_types=1);
require_once __DIR__ . '/contact-handler.php';
$inquiryReady = inquiry_ready();
?>
<form class="business-contact-form" id="business-contact-form" method="post" action="/api/contact" aria-labelledby="business-contact-title" aria-describedby="business-contact-notice" novalidate>
<div class="contact-form-head">
<h3 id="business-contact-title">비즈니스 문의</h3>
<p><span aria-hidden="true">*</span> 필수 입력 항목</p>
</div>
<div class="contact-fields">
<fieldset class="contact-field contact-field--wide contact-inquiry-types" id="business-inquiry-type" aria-describedby="business-inquiry-type-help business-inquiry-type-error">
<legend>문의 유형 <span class="contact-required" aria-hidden="true">*</span></legend>
<p class="contact-field-help" id="business-inquiry-type-help">하나 이상 선택해 주세요. (복수 선택 가능)</p>
<div class="contact-checkboxes">
<label class="contact-checkbox" for="business-inquiry-distribution"><input id="business-inquiry-distribution" name="inquiry_type[]" type="checkbox" value="distribution" aria-describedby="business-inquiry-type-help business-inquiry-type-error"/><span>유통·입점 제안</span></label>
<label class="contact-checkbox" for="business-inquiry-export"><input id="business-inquiry-export" name="inquiry_type[]" type="checkbox" value="export" aria-describedby="business-inquiry-type-help business-inquiry-type-error"/><span>해외 수출·수입</span></label>
<label class="contact-checkbox" for="business-inquiry-bulk"><input id="business-inquiry-bulk" name="inquiry_type[]" type="checkbox" value="bulk" aria-describedby="business-inquiry-type-help business-inquiry-type-error"/><span>대량 구매·단체 주문</span></label>
<label class="contact-checkbox" for="business-inquiry-research"><input id="business-inquiry-research" name="inquiry_type[]" type="checkbox" value="research" aria-describedby="business-inquiry-type-help business-inquiry-type-error"/><span>공동 연구·제품 개발</span></label>
<label class="contact-checkbox" for="business-inquiry-partnership"><input id="business-inquiry-partnership" name="inquiry_type[]" type="checkbox" value="partnership" aria-describedby="business-inquiry-type-help business-inquiry-type-error"/><span>브랜드·사업 제휴</span></label>
<label class="contact-checkbox" for="business-inquiry-other"><input id="business-inquiry-other" name="inquiry_type[]" type="checkbox" value="other" aria-describedby="business-inquiry-type-help business-inquiry-type-error"/><span>기타 비즈니스 문의</span></label>
</div>
<p class="contact-field-error" aria-live="polite" id="business-inquiry-type-error" hidden></p>
</fieldset>
<div class="contact-field contact-field--wide">
<label for="business-company">회사·기관명 <span aria-hidden="true">*</span></label>
<input id="business-company" name="company" type="text" autocomplete="organization" maxlength="100" placeholder="회사 또는 기관명을 입력해 주세요" required aria-describedby="business-company-error"/>
<p class="contact-field-error" aria-live="polite" id="business-company-error" hidden></p>
</div>
<div class="contact-field">
<label for="business-name">담당자명 <span aria-hidden="true">*</span></label>
<input id="business-name" name="contact_name" type="text" autocomplete="name" maxlength="50" placeholder="성함을 입력해 주세요" required aria-describedby="business-name-error"/>
<p class="contact-field-error" aria-live="polite" id="business-name-error" hidden></p>
</div>
<div class="contact-field">
<label for="business-email">업무용 이메일 <span aria-hidden="true">*</span></label>
<input id="business-email" name="email" type="email" autocomplete="email" maxlength="254" placeholder="name@company.com" required aria-describedby="business-email-error"/>
<p class="contact-field-error" aria-live="polite" id="business-email-error" hidden></p>
</div>
<div class="contact-field">
<label for="business-phone">연락처 <small>선택</small></label>
<input id="business-phone" name="phone" type="tel" autocomplete="tel" maxlength="30" placeholder="연락 가능한 번호"/>
</div>
<div class="contact-field">
<label for="business-website">회사 홈페이지 <small>선택</small></label>
<input id="business-website" name="website" type="url" autocomplete="url" maxlength="2048" placeholder="https://" aria-describedby="business-website-error"/>
<p class="contact-field-error" aria-live="polite" id="business-website-error" hidden></p>
</div>
<div class="contact-field contact-field--wide">
<label for="business-subject">문의 제목 <span aria-hidden="true">*</span></label>
<input id="business-subject" name="subject" type="text" maxlength="150" placeholder="제안의 핵심 내용을 적어 주세요" required aria-describedby="business-subject-error"/>
<p class="contact-field-error" aria-live="polite" id="business-subject-error" hidden></p>
</div>
<div class="contact-field contact-field--wide">
<label for="business-message">제안 내용 <span aria-hidden="true">*</span></label>
<textarea id="business-message" name="message" rows="7" maxlength="3000" placeholder="사업 소개와 협업 제안을 자유롭게 작성해 주세요." required aria-describedby="business-message-help business-message-error business-message-count"></textarea>
<div class="contact-message-help">
<p id="business-message-help">관심 제품·예상 수량, 제안 목적, 희망 일정 등을 함께 알려주세요.</p>
<output id="business-message-count" for="business-message" aria-live="off" aria-label="제안 내용 글자 수">0 / 3,000</output>
</div>
<p class="contact-field-error" aria-live="polite" id="business-message-error" hidden></p>
</div>
</div>
<input type="hidden" name="form_token" value="<?= e($inquiryReady ? issue_inquiry_token() : '') ?>"/>
<div class="contact-honeypot" aria-hidden="true"><label for="business-fax">팩스</label><input id="business-fax" name="fax" type="text" tabindex="-1" autocomplete="off"/></div>
<div class="contact-consent">
<!-- Retention follows the V1 operating standard; confirm with the client before launch. -->
<details class="contact-privacy">
<summary>개인정보 수집·이용 안내</summary>
<dl>
<div><dt>수집 항목</dt><dd>회사·기관명, 담당자명, 업무용 이메일, 연락처(선택), 회사 홈페이지(선택), 문의 내용</dd></div>
<div><dt>이용 목적</dt><dd>비즈니스 문의 검토 및 회신</dd></div>
<div><dt>보유 기간</dt><dd>문의 응대 완료 후 1년 이내 삭제. 법령에 따른 보관 의무가 있으면 해당 기간을 따릅니다.</dd></div>
<div><dt>동의 거부</dt><dd>동의하지 않을 수 있으며, 이 경우 온라인 문의 접수가 제한됩니다.</dd></div>
</dl>
</details>
<label for="business-consent"><input id="business-consent" name="privacy_consent" type="checkbox" value="1" required aria-describedby="business-consent-error"/><span>문의 검토와 회신을 위한 개인정보 수집·이용에 동의합니다. <span class="contact-required">*</span></span></label>
<p class="contact-field-error" aria-live="polite" id="business-consent-error" hidden></p>
</div>
<div class="contact-form-notice" id="business-contact-notice" role="status" aria-live="polite">
<?php if ($inquiryReady): ?>
<p>보내주신 문의는 담당자가 검토 후 입력하신 이메일로 회신드립니다.<br/>메일로 보내실 경우 <a href="mailto:partner@jejubellabell.com">partner@jejubellabell.com</a>을 이용해 주세요.</p>
<?php else: ?>
<p>온라인 문의 접수를 준비 중입니다.<br/>입점·제휴 제안은 <a href="mailto:partner@jejubellabell.com">partner@jejubellabell.com</a>으로 보내주세요.</p>
<?php endif; ?>
</div>
<button class="contact-submit" type="submit"<?= $inquiryReady ? '' : ' disabled' ?>>문의 보내기<i data-lucide="arrow-up-right" class="site-icon" aria-hidden="true" focusable="false"></i></button>
</form>
