function initCompanyHistory() {
    const section = document.querySelector('.history-section');
    if (!section || typeof Swiper === 'undefined') return;
    const tabs = [...section.querySelectorAll('[role=tab]')];
    const panels = [...section.querySelectorAll('.history-panel')];
    const galleries = new Map();
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    let current = 0;
    let inView = false;
    let panelAnimation = null;

    function syncAutoplay(state) {
        const allowed = state.enabled && inView && !document.hidden && !state.panel.hidden && !reduced.matches && !state.hover && !state.focus;
        if (allowed && !state.swiper.autoplay.running) state.swiper.autoplay.start();
        else if (!allowed && state.swiper.autoplay.running) state.swiper.autoplay.stop();
        state.swiper.wrapperEl.setAttribute('aria-live', allowed ? 'off' : 'polite');
    }

    function initGallery(panel) {
        if (galleries.has(panel)) return galleries.get(panel);
        const slider = panel.querySelector('.history-swiper');
        const total = slider.querySelectorAll('.swiper-slide').length;
        const counter = panel.querySelector('.history-count');
        const gallery = panel.querySelector('.history-gallery');
        const state = { panel, enabled:total > 1, hover:false, focus:false, swiper:null };
        state.swiper = new Swiper(slider, {
            slidesPerView:1,
            spaceBetween:24,
            speed:reduced.matches ? 0 : 600,
            loop:total > 1,
            grabCursor:total > 1,
            watchOverflow:true,
            autoplay:{ enabled:total > 1 && !reduced.matches, delay:5000, disableOnInteraction:false },
            navigation:{ prevEl:panel.querySelector('.history-prev'), nextEl:panel.querySelector('.history-next') },
            a11y:{ prevSlideMessage:'이전 연혁 사진', nextSlideMessage:'다음 연혁 사진', slideLabelMessage:'{{index}} / {{slidesLength}}' },
            on:{ init:function () { counter.textContent = (this.realIndex + 1) + ' / ' + total; }, slideChange:function () { counter.textContent = (this.realIndex + 1) + ' / ' + total; } }
        });
        gallery.addEventListener('pointerenter', event => { if (event.pointerType === 'mouse') { state.hover = true; syncAutoplay(state); } });
        gallery.addEventListener('pointerleave', () => { state.hover = false; syncAutoplay(state); });
        gallery.addEventListener('focusin', () => { state.focus = true; syncAutoplay(state); });
        gallery.addEventListener('focusout', event => { state.focus = gallery.contains(event.relatedTarget); syncAutoplay(state); });
        galleries.set(panel, state);
        syncAutoplay(state);
        return state;
    }

    function activate(index, focus = false) {
        panelAnimation?.cancel();
        current = index;
        tabs.forEach((tab, i) => {
            const active = i === index;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', String(active));
            tab.tabIndex = active ? 0 : -1;
            panels[i].hidden = !active;
        });
        const panel = panels[index];
        const state = initGallery(panel);
        state.swiper.update();
        galleries.forEach(syncAutoplay);
        if (focus) tabs[index].focus({ preventScroll:true });
        if (!reduced.matches) panelAnimation = panel.animate([{opacity:0,transform:'translateY(8px)'},{opacity:1,transform:'translateY(0)'}], { duration:350, easing:'cubic-bezier(.2,.7,.2,1)' });
        requestAnimationFrame(() => { if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.refresh(); });
    }

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => { if (index !== current) activate(index); });
        tab.addEventListener('keydown', event => {
            let next;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            else if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
            else if (event.key === 'Home') next = 0;
            else if (event.key === 'End') next = tabs.length - 1;
            else return;
            event.preventDefault();
            activate(next, true);
        });
    });
    new IntersectionObserver(entries => { inView = entries[0].isIntersecting; galleries.forEach(syncAutoplay); }, { threshold:.15 }).observe(section);
    document.addEventListener('visibilitychange', () => galleries.forEach(syncAutoplay));
    reduced.addEventListener('change', () => galleries.forEach(state => {
        state.swiper.params.speed = reduced.matches ? 0 : 600;
        syncAutoplay(state);
    }));
    initGallery(panels[0]);
}

document.addEventListener('DOMContentLoaded', initCompanyHistory);
