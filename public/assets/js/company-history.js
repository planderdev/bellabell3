const HISTORY_SCROLL_CLASS = 'is-scroll-stage';

function historyStageOffset() {
    return window.innerWidth > 970 ? 100 : 70;
}

// The stage only pins when its tallest year fits below the header; a pinned overflow could never be scrolled into view.
function companyHistoryFits() {
    const section = document.querySelector('.history-section');
    const stage = section?.querySelector('.history-stage');
    if (!stage) return false;
    const applied = section.classList.contains(HISTORY_SCROLL_CLASS);
    section.classList.add(HISTORY_SCROLL_CLASS);
    const fits = stage.offsetHeight + historyStageOffset() <= window.innerHeight;
    if (!applied) section.classList.remove(HISTORY_SCROLL_CLASS);
    return fits;
}

function initCompanyHistory() {
    const section = document.querySelector('.history-section');
    const stage = section?.querySelector('.history-stage');
    if (!stage || !companyHistoryFits()) return;
    const tabs = [...stage.querySelectorAll('[role=tab]')];
    const panels = [...stage.querySelectorAll('.history-panel')];
    const parts = panels.map(panel => [panel.querySelector('.history-year'), ...panel.querySelectorAll('.history-events li')]);
    let current = 0;

    function show(index) {
        if (index === current) return;
        current = index;
        tabs.forEach((tab, i) => {
            const active = i === index;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', String(active));
            tab.tabIndex = active ? 0 : -1;
            panels[i].classList.toggle('is-active', active);
        });
        gsap.fromTo(parts[index], { autoAlpha:0, y:16 }, { autoAlpha:1, y:0, duration:.45, stagger:.04, ease:'power2.out', overwrite:true });
    }

    section.classList.add(HISTORY_SCROLL_CLASS);
    const trigger = ScrollTrigger.create({
        id: 'company-history',
        trigger: stage,
        start: () => 'top ' + historyStageOffset(),
        end: () => '+=' + Math.round(window.innerHeight * 2.4),
        pin: true,
        pinSpacing: true,
        anticipatePin: 1,
        invalidateOnRefresh: true,
        onUpdate: self => show(Math.min(panels.length - 1, Math.floor(self.progress * panels.length)))
    });

    function scrollToYear(index) {
        const top = trigger.start + (trigger.end - trigger.start) * (index + .5) / panels.length;
        if (window.lenis) window.lenis.scrollTo(top);
        else window.scrollTo(0, top);
    }

    const controller = new AbortController();
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => scrollToYear(index), { signal:controller.signal });
        tab.addEventListener('keydown', event => {
            let next;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            else if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
            else if (event.key === 'Home') next = 0;
            else if (event.key === 'End') next = tabs.length - 1;
            else return;
            event.preventDefault();
            tabs[next].focus({ preventScroll:true });
            scrollToYear(next);
        }, { signal:controller.signal });
    });

    motionResizeCleanups.push(() => {
        controller.abort();
        section.classList.remove(HISTORY_SCROLL_CLASS);
        gsap.killTweensOf(parts.flat());
        gsap.set(parts.flat(), { clearProps:'opacity,visibility,transform' });
    });
}
