function initBrandSlider(panel) {
    const slider = panel.querySelector('.brand-slider');
    const dots = [...panel.querySelectorAll('.brand-slide-dot')];
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
    };
    const swiper = new Swiper(slider, {
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: reducedMotion ? 0 : 1000,
        on: { init: syncSlide, slideChange: syncSlide }
    });

    dots.forEach((dot, index) => dot.addEventListener('click', () => swiper.slideTo(index)));
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

function initBrandPage() {
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
