(() => {
  'use strict';
  const root = document.documentElement;
  const reduced = matchMedia('(prefers-reduced-motion: reduce)');
  const unit = () => root.style.setProperty('--unit', `${root.clientWidth / 100}px`);
  unit();
  if (!window.gsap || !window.ScrollTrigger || !window.Swiper || !window.Lenis || !window.AOS) {
    root.dataset.motion = 'unavailable';
    return;
  }
  gsap.registerPlugin(ScrollTrigger);
  const swipers = [];
  const hero = document.querySelector('.hero-swiper');
  const heroSwiper = new Swiper(hero, {
    slidesPerView: 1, speed: reduced.matches ? 0 : 1200, effect: 'fade',
    fadeEffect: { crossFade: true }, loop: true, allowTouchMove: true,
    autoplay: reduced.matches ? false : { delay: 5000, disableOnInteraction: false },
    a11y: false,
    on: { slideChange() {
      hero.dataset.activeSlide = String(this.realIndex);
      document.querySelectorAll('.hero-progress span').forEach((span, index) => span.classList.toggle('active', index === this.realIndex));
    } },
  });
  swipers.push(heroSwiper);
  document.querySelectorAll('.mission-swiper').forEach(element => {
    const slider = new Swiper(element, {
      slidesPerView: 1, speed: reduced.matches ? 0 : 1200, effect: 'fade',
      fadeEffect: { crossFade: true }, loop: true, allowTouchMove: true,
      autoplay: reduced.matches ? false : { delay: 5000, disableOnInteraction: false }, a11y: false,
      on: { slideChange() { element.dataset.activeSlide = String(this.realIndex); } },
    });
    swipers.push(slider);
  });

  const focus = document.querySelector('.family-focus');
  const focusFrame = focus.querySelector('.media-frame');
  const originalImage = focusFrame.querySelector('img');
  const original = originalImage ? { src: originalImage.getAttribute('src'), alt: originalImage.alt } : null;
  const brandButtons = [...document.querySelectorAll('[data-brand-index]')];
  let selectedBrand = 0;
  let imageTween;
  function replaceBrandImage(index) {
    focusFrame.replaceChildren();
    focusFrame.classList.toggle('missing-image', index !== 0 || !original);
    focusFrame.dataset.mediaState = index === 0 && original ? 'ready' : 'missing';
    if (index === 0 && original) {
      const image = new Image(); image.src = original.src; image.alt = original.alt;
      focusFrame.append(image);
    }
  }
  function selectBrand(index) {
    if (index === selectedBrand) return;
    selectedBrand = index;
    brandButtons.forEach((button, i) => button.setAttribute('aria-pressed', String(i === index)));
    if (imageTween) imageTween.kill();
    const replace = () => replaceBrandImage(index);
    if (reduced.matches) { replace(); focusFrame.style.opacity = '1'; }
    else imageTween = gsap.to(focusFrame, { opacity: 0, duration: 0.18, ease: 'power2.in', onComplete() {
      replace(); imageTween = gsap.to(focusFrame, { opacity: 1, duration: 0.3, ease: 'power2.out' });
    } });
  }
  const familySwiper = new Swiper('.family-swiper', {
    slidesPerView: 1.12, spaceBetween: 16, speed: reduced.matches ? 0 : 1200,
    allowTouchMove: true, a11y: false,
    breakpoints: { 971: { slidesPerView: 2 } },
    on: { slideChange() { selectBrand(this.activeIndex); } },
  });
  brandButtons.forEach((button, index) => button.addEventListener('click', () => {
    selectBrand(index); familySwiper.slideTo(index);
  }));
  swipers.push(familySwiper);

  AOS.init({ offset: Math.round(innerHeight * 0.15), duration: 1200, easing: 'ease-out-quart', once: false, mirror: false, disable: false });
  const fades = [...document.querySelectorAll('[data-fade]')];
  let introPlayed = false;
  const modes = gsap.matchMedia();
  modes.add({ animate: '(prefers-reduced-motion: no-preference)', desktop: '(min-width: 971px)' }, context => {
    unit();
    if (!context.conditions.animate) {
      root.dataset.motion = 'reduced';
      return;
    }
    const lenis = new Lenis({ autoRaf: false, lerp: 0.08, smoothWheel: true, syncTouch: false });
    const menuState = event => {
      if (event.detail.open) lenis.stop();
      else if (root.dataset.intro !== 'playing') lenis.start();
    };
    document.addEventListener('company:menu', menuState);
    if (document.querySelector('#site-menu-dialog[open]')) lenis.stop();
    const navigate = event => {
      if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      const link = event.target.closest('a[href^="#"]');
      if (!link) return;
      const hash = link.getAttribute('href');
      if (!hash || hash.length < 2) return;
      const target = document.getElementById(hash.slice(1));
      if (!target) return;
      event.preventDefault();
      const pinId = { meaning: 'natural', family: 'brands', global: 'global' }[target.id];
      const pin = pinId ? ScrollTrigger.getById(pinId) : null;
      const offset = innerWidth > 970 ? 116 : 96;
      const destination = pin ? pin.start + (target.id === 'meaning' ? 0 : (pin.end - pin.start) * 0.82) : target.getBoundingClientRect().top + scrollY - (target.id === 'intro' ? 0 : offset);
      lenis.scrollTo(Math.max(0, destination), { duration: 1.2, force: true, onComplete() {
        history.pushState(null, '', hash);
        const heading = target.querySelector('[data-section-title]');
        if (heading) { heading.setAttribute('tabindex', '-1'); heading.focus({ preventScroll: true }); }
      } });
    };
    document.addEventListener('click', navigate);
    const tick = time => lenis.raf(time * 1000);
    const updateFades = () => {
      const threshold = innerHeight * 0.85;
      fades.forEach(element => {
        const rect = element.getBoundingClientRect();
        const shown = element.classList.contains('is-fade-up');
        if (rect.top < threshold && rect.bottom > 0 && !shown) {
          element.classList.add('is-fade-up');
          gsap.to(element, { opacity: 1, y: 0, duration: 1.2, ease: 'power3.out', overwrite: true });
        } else if (rect.top >= threshold && shown) {
          element.classList.remove('is-fade-up');
          gsap.to(element, { opacity: 0, y: 30, duration: 0.8, ease: 'power2.out', overwrite: true });
        }
      });
    };
    const scroll = () => { ScrollTrigger.update(); updateFades(); };
    gsap.set(fades, { opacity: 0, y: 30 });
    lenis.on('scroll', scroll);
    gsap.ticker.add(tick); gsap.ticker.lagSmoothing(0);
    root.classList.add('motion-ready');

    const frame = document.querySelector('.intro-media');
    const copy = document.querySelector('.intro-copy');
    const identity = document.querySelector('.identity-row');
    const progress = document.querySelector('.hero-progress');
    if (!introPlayed && scrollY < 50) {
      introPlayed = true; lenis.stop(); root.dataset.intro = 'playing';
      gsap.set([identity, progress], { opacity: 0, y: 20 });
      const opening = gsap.timeline({ delay: 1, onComplete() {
        gsap.set(frame, { width: '100%', height: '100svh' });
        if (!document.querySelector('#site-menu-dialog[open]')) lenis.start();
        heroSwiper.update(); root.dataset.intro = 'complete';
      } });
      opening.to(copy, { opacity: 0, y: 30, duration: 0.7, ease: 'power1.inOut' }, 0)
        .to(frame, { left: 0, top: 0, width: '100%', height: '100svh', borderRadius: 0, duration: 1.4, ease: 'power3.inOut' }, 0.5)
        .to(identity, { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, 1.8)
        .to([copy, progress], { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, 2.0);
    } else {
      gsap.set(frame, { left: 0, top: 0, width: '100%', height: '100svh', borderRadius: 0 });
      gsap.set([copy, identity, progress], { opacity: 1, y: 0 }); root.dataset.intro = 'complete';
    }

    if (context.conditions.desktop) {
      const section = document.querySelector('.meaning');
      const rectangle = section.querySelector('.natural-window-rect');
      const picture = section.querySelector('.meaning-media .media-frame');
      const image = section.querySelector('.meaning-media img');
      const heading = section.querySelector('.meaning-content');
      const dimensions = () => {
        const width = section.clientWidth, height = section.clientHeight;
        return { width, height, size: Math.min(430, Math.max(180, innerWidth * 0.3)), expanded: Math.max(width, height) * 1.5 };
      };
      const center = () => { const d = dimensions(); rectangle.setAttribute('transform', `rotate(45 ${d.width / 2} ${d.height / 2})`); };
      center(); ScrollTrigger.addEventListener('refreshInit', center);
      gsap.set(picture, { clipPath: 'url(#natural-window)' });
      const natural = gsap.timeline({ scrollTrigger: {
        id: 'natural', trigger: section, start: 'top top', end: '+=600%', pin: true, scrub: 1, anticipatePin: 1, invalidateOnRefresh: true,
        onUpdate(self) { section.dataset.pinProgress = self.progress.toFixed(3); },
      } });
      natural.fromTo(rectangle, { attr: {
        x: () => (dimensions().width - dimensions().size) / 2, y: () => (dimensions().height - dimensions().size) / 2,
        width: () => dimensions().size, height: () => dimensions().size, rx: 92, ry: 92,
      } }, { attr: {
        x: () => (dimensions().width - dimensions().expanded) / 2, y: () => (dimensions().height - dimensions().expanded) / 2,
        width: () => dimensions().expanded, height: () => dimensions().expanded, rx: () => dimensions().expanded * 0.28, ry: () => dimensions().expanded * 0.28,
      }, duration: 0.8, ease: 'power1.out' }, 0)
        .to(heading, { opacity: 0, y: -30, duration: 0.15 }, 0);
      if (image) natural.fromTo(image, { scale: 1.3, filter: 'brightness(1)' }, { scale: 1, filter: 'brightness(.7)', duration: 0.2, ease: 'power1.out' }, 0.05);
      natural.set(heading, { top: 'auto', bottom: '3.33vw', right: 'auto', maxWidth: '43%', color: '#fff', y: 30 }, 0.22)
        .to(heading, { opacity: 1, y: 0, duration: 0.15 }, 0.23)
        .to({}, { duration: 0.4 });
      context.add(() => () => ScrollTrigger.removeEventListener('refreshInit', center));
    }

    const mission = document.querySelector('.mission-layout');
    gsap.fromTo('.mission-photo', { clipPath: 'inset(0 100% 0 0)' }, { clipPath: 'inset(0 0% 0 0)', ease: 'none', scrollTrigger: {
      trigger: mission, start: 'top bottom', end: 'top center', scrub: true, invalidateOnRefresh: true,
    } });

    const viewport = document.querySelector('.document-window');
    const track = viewport.querySelector('.document-grid');
    const distance = () => Math.max(0, track.scrollWidth - viewport.clientWidth);
    if (distance() > 1) gsap.to(track, { x: () => -distance(), ease: 'none', scrollTrigger: {
      id: 'documents', trigger: viewport, start: 'center center', end: () => `+=${distance()}`, pin: true, scrub: 1, anticipatePin: 1, invalidateOnRefresh: true,
      onUpdate(self) { viewport.dataset.pinProgress = self.progress.toFixed(3); },
    } });

    const family = document.querySelector('.family');
    const familyHeading = family.querySelector('.section-head');
    const cards = family.querySelector('.family-swiper');
    gsap.set(focus, { xPercent: -50, yPercent: -50, x: 0, y: 0 });
    gsap.set([familyHeading, cards], { autoAlpha: 0, y: 30 });
    gsap.timeline({ scrollTrigger: {
      id: 'brands', trigger: family, start: 'top top', end: '+=150%', pin: true, scrub: 1, anticipatePin: 1,
      onUpdate(self) { family.dataset.pinProgress = self.progress.toFixed(3); },
    } }).to(focus, { scale: 0.8, top: '60%', duration: 1, ease: 'power1.out' }, 0)
      .to(familyHeading, { autoAlpha: 1, y: 0, duration: 1 }, 1.1)
      .to(focus, { opacity: 0.15, duration: 1 }, 1.1)
      .to(cards, { autoAlpha: 1, y: 0, duration: 1 }, 2.1)
      .to({}, { duration: 1 });

    const global = document.querySelector('.global');
    const photo = global.querySelector('.global-media');
    const heading = global.querySelector('.global-copy');
    gsap.timeline({ scrollTrigger: {
      id: 'global', trigger: global, start: 'top top', end: '+=350%', pin: true, scrub: 1, anticipatePin: 1, invalidateOnRefresh: true,
      onUpdate(self) { global.dataset.pinProgress = self.progress.toFixed(3); },
    } }).to(heading, { opacity: 0, y: -30, duration: 0.2, ease: 'power2.inOut' }, 0)
      .to('.global-blue', { opacity: 1, duration: 0.45, ease: 'power2.inOut' }, 0.12)
      .to(photo, { left: () => root.clientWidth * 0.033, bottom: 64, width: () => root.clientWidth * 0.165, height: () => innerHeight * 0.27, borderRadius: 20, duration: 0.7, ease: 'power3.inOut' }, 0.08)
      .set(heading, { top: 'auto', bottom: '22%', left: '34%', width: '58%', color: '#fff', y: 30 }, 0.48)
      .to(heading, { opacity: 1, y: 0, duration: 0.25, ease: 'power3.out' }, 0.50)
      .to({}, { duration: 0.3 });

    root.dataset.motion = 'ready';
    const initialFrame = requestAnimationFrame(() => { ScrollTrigger.refresh(); updateFades(); AOS.refresh(); });
    return () => {
      cancelAnimationFrame(initialFrame);
      gsap.killTweensOf(fades);
      gsap.set(fades, { clearProps: 'opacity,transform' });
      lenis.destroy(); gsap.ticker.remove(tick);
      document.removeEventListener('click', navigate);
      document.removeEventListener('company:menu', menuState);
      fades.forEach(element => element.classList.remove('is-fade-up'));
    };
  });

  let resizeFrame;
  window.addEventListener('resize', () => {
    cancelAnimationFrame(resizeFrame);
    resizeFrame = requestAnimationFrame(() => { unit(); ScrollTrigger.refresh(); AOS.refresh(); });
  });
  const refresh = () => { unit(); ScrollTrigger.refresh(); AOS.refresh(); };
  window.addEventListener('load', refresh, { once: true });
  document.fonts.ready.then(refresh);
  document.querySelectorAll('img').forEach(image => { if (!image.complete) image.addEventListener('load', refresh, { once: true }); });
  reduced.addEventListener('change', event => {
    if (imageTween) imageTween.kill();
    replaceBrandImage(selectedBrand); focusFrame.style.opacity = '1';
    swipers.forEach(slider => { slider.params.speed = event.matches ? 0 : 1200; if (event.matches) slider.autoplay?.stop(); else if (slider.params.autoplay) slider.autoplay?.start(); });
    AOS.refresh();
    root.dataset.motion = event.matches ? 'reduced' : 'ready';
  });
})();
