'use strict';

(() => {
  const header = document.querySelector('[data-site-header]');
  const toggle = header?.querySelector('.site-menu-toggle');
  const dialog = document.querySelector('#site-menu-dialog');
  if (!header || !toggle || !dialog) return;

  const closeButton = dialog.querySelector('.site-menu-close');
  let opener = null;
  let scrollQueued = false;
  let publishedOpen = false;

  const updateHeader = () => {
    header.classList.toggle('is-scrolled', window.scrollY > 80);
    scrollQueued = false;
  };
  const publishMenuState = () => {
    const open = dialog.open;
    toggle.setAttribute('aria-expanded', String(open));
    document.body.classList.toggle('site-menu-open', open);
    if (publishedOpen === open) return;
    publishedOpen = open;
    document.dispatchEvent(new CustomEvent('company:menu', { detail: { open } }));
  };
  const closeMenu = () => {
    if (!dialog.open) return;
    dialog.close();
    publishMenuState();
  };

  toggle.addEventListener('click', () => {
    if (dialog.open) {
      closeMenu();
      return;
    }
    opener = document.activeElement;
    dialog.showModal();
    publishMenuState();
    closeButton?.focus({ preventScroll: true });
  });
  closeButton?.addEventListener('click', closeMenu);
  dialog.addEventListener('cancel', event => {
    event.preventDefault();
    closeMenu();
  });
  dialog.addEventListener('close', () => {
    publishMenuState();
    if (opener instanceof HTMLElement && opener.isConnected) opener.focus({ preventScroll: true });
    opener = null;
  });
  dialog.addEventListener('click', event => {
    const target = event.target instanceof Element ? event.target : null;
    if (target?.closest('a[href^="#"]')) {
      closeMenu();
      return;
    }
    if (event.target !== dialog) return;
    const bounds = dialog.getBoundingClientRect();
    if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) closeMenu();
  });
  window.addEventListener('scroll', () => {
    if (scrollQueued) return;
    scrollQueued = true;
    window.requestAnimationFrame(updateHeader);
  }, { passive: true });
  updateHeader();
})();
