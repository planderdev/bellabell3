// Counts each number up from zero whenever its slide comes into view.
function runBrandCounters(slide, reducedMotion) {
    if (reducedMotion) return;
    slide.querySelectorAll('.brand-stat-number').forEach(number => {
        const target = Number(number.dataset.count.replace(/,/g, ''));
        if (!Number.isFinite(target)) return;
        cancelAnimationFrame(number.countFrame);
        const finalText = number.dataset.count;
        const started = performance.now() + 350;
        number.textContent = '0';
        const tick = now => {
            const progress = Math.min(Math.max((now - started) / 1800, 0), 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            number.textContent = progress === 1 ? finalText : Math.round(target * eased).toLocaleString('en-US');
            if (progress < 1) number.countFrame = requestAnimationFrame(tick);
        };
        number.countFrame = requestAnimationFrame(tick);
    });
}

function initBrandSlider(panel) {
    const slider = panel.querySelector('.brand-slider');
    const dots = [...panel.querySelectorAll('.brand-slide-dot')];
    const prev = panel.querySelector('[data-move="prev"]');
    const next = panel.querySelector('[data-move="next"]');
    if (slider.swiper || !dots.length || typeof Swiper === 'undefined') return;

    const slides = [...slider.querySelectorAll('.brand-slide')];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const syncSlide = swiper => {
        slides.forEach((slide, index) => { slide.inert = index !== swiper.activeIndex; });
        dots.forEach((dot, index) => {
            const active = index === swiper.activeIndex;
            dot.classList.toggle('is-active', active);
            if (active) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
        prev.disabled = swiper.isBeginning;
        next.disabled = swiper.isEnd;
        runBrandCounters(slides[swiper.activeIndex], reducedMotion);
    };
    const swiper = new Swiper(slider, {
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: reducedMotion ? 0 : 1000,
        on: { init: syncSlide, slideChange: syncSlide }
    });

    dots.forEach((dot, index) => dot.addEventListener('click', () => swiper.slideTo(index)));
    prev.addEventListener('click', () => swiper.slidePrev());
    next.addEventListener('click', () => swiper.slideNext());
    slider.querySelectorAll('.brand-slide-group').forEach(button => {
        button.addEventListener('click', () => swiper.slideTo(Number(button.dataset.slide)));
    });

    // The wheel changes slides while the stage fills the screen, then hands over to page scrolling.
    let lockedUntil = 0;
    panel.addEventListener('wheel', event => {
        if (window.scrollY > 10 || Math.abs(event.deltaY) < 10) return;
        const forward = event.deltaY > 0;
        if (performance.now() < lockedUntil) {
            event.preventDefault();
            return;
        }
        if (forward ? swiper.isEnd : swiper.isBeginning) return;
        event.preventDefault();
        lockedUntil = performance.now() + (reducedMotion ? 300 : 1200);
        if (forward) swiper.slideNext();
        else swiper.slidePrev();
    }, { passive: false });
}

// Photo gallery on a slide: thumbnails pick the large photo, the arrow steps through and loops.
function initBrandGalleries() {
    // One large certificate; the small row always offers the others.
    document.querySelectorAll('[data-cert-gallery]').forEach(gallery => {
        const main = gallery.querySelector('.brand-cert-main img');
        const caption = gallery.querySelector('.brand-cert-main figcaption');
        const thumbs = [...gallery.querySelectorAll('.brand-cert-thumb')];
        thumbs.forEach(thumb => thumb.addEventListener('click', () => {
            main.src = thumb.querySelector('img').getAttribute('src');
            main.alt = caption.textContent = thumb.dataset.caption;
            thumbs.forEach(other => { other.hidden = other === thumb; });
            thumbs.find(other => !other.hidden)?.focus({ preventScroll:true });
        }));
    });

    document.querySelectorAll('[data-brand-gallery]').forEach(gallery => {
        const main = gallery.querySelector('.brand-gallery-main');
        const strip = gallery.querySelector('.brand-gallery-thumbs');
        const thumbs = [...strip.querySelectorAll('.brand-gallery-thumb')];
        const label = main.alt.replace(/\s*\d+$/, '');
        let current = 0;
        const show = index => {
            current = index;
            main.src = thumbs[index].querySelector('img').getAttribute('src');
            main.alt = `${label} ${index + 1}`;
            thumbs.forEach((thumb, i) => {
                thumb.classList.toggle('is-active', i === index);
                if (i === index) thumb.setAttribute('aria-current', 'true');
                else thumb.removeAttribute('aria-current');
            });
            // Scroll the strip itself; scrollIntoView would also move the page and the slider.
            const thumb = thumbs[index];
            if (thumb.offsetLeft < strip.scrollLeft) strip.scrollLeft = thumb.offsetLeft;
            else if (thumb.offsetLeft + thumb.offsetWidth > strip.scrollLeft + strip.clientWidth) strip.scrollLeft = thumb.offsetLeft + thumb.offsetWidth - strip.clientWidth;
        };
        thumbs.forEach((thumb, index) => thumb.addEventListener('click', () => show(index)));
        gallery.querySelector('.brand-gallery-next')?.addEventListener('click', () => show((current + 1) % thumbs.length));
    });
}

function initBrandPage() {
    initBrandGalleries();
    const tabs = [...document.querySelectorAll('.brand-tab')];
    if (!tabs.length) return;

    function activate(index, focus = false) {
        tabs.forEach((tab, i) => {
            const active = i === index;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', String(active));
            tab.tabIndex = active ? 0 : -1;
            const panel = document.getElementById(tab.getAttribute('aria-controls'));
            panel.hidden = !active;
            if (active) initBrandSlider(panel);
        });
        if (focus) tabs[index].focus();
    }

    function select(index, focus = false) {
        activate(index, focus);
        history.replaceState(null, '', '#' + tabs[index].dataset.brand);
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => select(index));
        tab.addEventListener('keydown', event => {
            let next;
            if (event.key === 'ArrowRight' || event.key === 'ArrowDown') next = (index + 1) % tabs.length;
            else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') next = (index + tabs.length - 1) % tabs.length;
            else if (event.key === 'Home') next = 0;
            else if (event.key === 'End') next = tabs.length - 1;
            else return;
            event.preventDefault();
            select(next, true);
        });
    });

    const showHashTab = () => {
        const index = tabs.findIndex(tab => '#' + tab.dataset.brand === location.hash);
        if (index >= 0) activate(index);
    };
    window.addEventListener('hashchange', showHashTab);
    activate(Math.max(0, tabs.findIndex(tab => '#' + tab.dataset.brand === location.hash)));
}

document.addEventListener('DOMContentLoaded', initBrandPage);
