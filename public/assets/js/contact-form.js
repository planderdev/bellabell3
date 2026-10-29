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

    const submit = form.querySelector('.contact-submit');
    const notice = document.getElementById('business-contact-notice');

    function showNotice(text, state) {
        notice.replaceChildren(Object.assign(document.createElement('p'), { textContent: text }));
        notice.dataset.state = state;
    }

    function showServerErrors(errors) {
        Object.entries(errors).forEach(([id, feedback]) => {
            const error = document.getElementById(id + '-error');
            if (!error) return;
            error.textContent = feedback;
            error.hidden = false;
        });
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submit.disabled) return;
        validateInquiryTypes();
        const valid = fields.map(validate).every(Boolean) && inquiryTypes.some(field => field.checked);
        if (!valid) {
            form.querySelector('[aria-invalid="true"]')?.focus();
            return;
        }
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
            const result = await response.json().catch(() => ({}));
            if (response.ok && result.ok) {
                form.reset();
                counter.textContent = '0 / 3,000';
                showNotice(result.message, 'success');
                // The token is single-use; a new inquiry needs a fresh page.
                return;
            }
            if (result.errors) showServerErrors(result.errors);
            showNotice(result.message || '접수 중 문제가 생겼습니다. partner@jejubellabell.com으로 보내주세요.', 'error');
            submit.disabled = false;
        } catch {
            showNotice('네트워크 연결을 확인한 뒤 다시 시도해 주세요.', 'error');
            submit.disabled = false;
        } finally {
            submit.removeAttribute('aria-busy');
        }
    });
}

document.addEventListener('DOMContentLoaded', initBusinessContactForm);
