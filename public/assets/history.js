'use strict';

document.querySelectorAll('[data-history]').forEach(history => {
  const tabs = [...history.querySelectorAll('[role="tab"]')];
  const panels = [...history.querySelectorAll('[role="tabpanel"]')];
  const select = tab => {
    tabs.forEach(item => {
      const selected = item === tab;
      item.setAttribute('aria-selected', String(selected));
      item.tabIndex = selected ? 0 : -1;
      const panel = panels.find(candidate => candidate.id === item.getAttribute('aria-controls'));
      if (panel) panel.hidden = !selected;
    });
  };

  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => select(tab));
    tab.addEventListener('keydown', event => {
      let target = null;
      if (event.key === 'ArrowRight') target = tabs[(index + 1) % tabs.length];
      if (event.key === 'ArrowLeft') target = tabs[(index - 1 + tabs.length) % tabs.length];
      if (event.key === 'Home') target = tabs[0];
      if (event.key === 'End') target = tabs[tabs.length - 1];
      if (target) {
        event.preventDefault();
        select(target);
        target.focus();
      }
    });
  });

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  history.querySelectorAll('[data-history-slideshow]').forEach(slideshow => {
    const slides = [...slideshow.querySelectorAll('[data-history-slide]')];
    const interval = Number(slideshow.dataset.historyInterval);
    if (!Number.isSafeInteger(interval) || interval <= 0 || slides.length < 2) return;

    let index = 0;
    let interactionPaused = false;
    slideshow.addEventListener('mouseenter', () => { interactionPaused = true; });
    slideshow.addEventListener('mouseleave', () => { interactionPaused = false; });
    history.addEventListener('focusin', () => { interactionPaused = true; });
    history.addEventListener('focusout', event => {
      if (!history.contains(event.relatedTarget)) interactionPaused = false;
    });
    window.setInterval(() => {
      if (reducedMotion.matches || document.hidden || interactionPaused || !slideshow.getClientRects().length) return;
      index = (index + 1) % slides.length;
      slides.forEach((slide, slideIndex) => { slide.hidden = slideIndex !== index; });
    }, interval);
  });
});
