function initBusinessContactForm() {
    const form = document.getElementById('business-contact-form');
    if (!form) return;
    const inquiryGroup = document.getElementById('business-inquiry-type');
    const inquiryTypes = [...inquiryGroup.querySelectorAll('input[type="checkbox"]')];
    const inquiryError = document.getElementById('business-inquiry-type-error');
    const fields = [...form.querySelectorAll('input, select, textarea')].filter(field => !inquiryTypes.includes(field));
    const message = form.querySelector('[name="message"]');
    const counter = document.getElementById('business-message-count');

    function validateInquiryTypes() {
        const feedback = inquiryTypes.some(field => field.checked) ? '' : '문의 유형을 하나 이상 선택해 주세요.';
        inquiryError.textContent = feedback;
        inquiryError.hidden = !feedback;
        inquiryTypes.forEach(field => {
            if (feedback) field.setAttribute('aria-invalid', 'true');
            else field.removeAttribute('aria-invalid');
        });
        if (feedback) inquiryGroup.setAttribute('aria-invalid', 'true');
        else inquiryGroup.removeAttribute('aria-invalid');
    }

    function updateInquiryValidity() {
        // Requiring each checkbox would require every option to be selected.
        inquiryTypes[0].setCustomValidity(inquiryTypes.some(field => field.checked) ? '' : '문의 유형을 하나 이상 선택해 주세요.');
    }

    updateInquiryValidity();
    inquiryGroup.addEventListener('change', () => {
        updateInquiryValidity();
        validateInquiryTypes();
    });
    inquiryGroup.addEventListener('focusout', event => {
        if (!inquiryGroup.contains(event.relatedTarget)) validateInquiryTypes();
    });

    function validate(field) {
        const error = document.getElementById(field.id + '-error');
        if (!error) return true;
        let feedback = '';
        const value = field.value.trim();
        if (field.type === 'checkbox' && field.required && !field.checked) {
            feedback = '개인정보 수집·이용 동의를 확인해 주세요.';
        } else if (field.type !== 'checkbox' && field.required && !value) {
            feedback = field.tagName === 'SELECT' ? '문의 유형을 선택해 주세요.' : '필수 항목을 입력해 주세요.';
        } else if (value && field.validity.typeMismatch) {
            feedback = field.type === 'email' ? '이메일 주소를 확인해 주세요.' : 'https://를 포함한 홈페이지 주소를 입력해 주세요.';
        }
        error.textContent = feedback;
        error.hidden = !feedback;
        if (feedback) field.setAttribute('aria-invalid', 'true');
        else field.removeAttribute('aria-invalid');
        return !feedback;
    }

    fields.forEach(field => {
        field.addEventListener('blur', () => validate(field));
        field.addEventListener('change', () => validate(field));
        field.addEventListener('input', () => {
            if (field.hasAttribute('aria-invalid')) validate(field);
        });
    });
    message.addEventListener('input', () => {
        counter.textContent = message.value.length.toLocaleString('ko-KR') + ' / 3,000';
    });

    // UI preview only: do not transmit, store, or show a successful submission.
    form.addEventListener('submit', event => {
        event.preventDefault();
    });
}

document.addEventListener('DOMContentLoaded', initBusinessContactForm);
