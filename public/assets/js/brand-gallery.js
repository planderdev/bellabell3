let brandGallery = null;

function initBrandGallery() {
    const section = document.querySelector(".products-section");
    const slider = section?.querySelector(".products-gallery");
    const caption = section?.querySelector(".products-brand-title");
    const subtitle = section?.querySelector(".products-brand-subtitle");
    const counter = section?.querySelector(".products-slide-count");
    const items = section ? [...section.querySelectorAll(".product-item")] : [];
    const slides = slider ? [...slider.querySelectorAll(".swiper-slide")].map(slide => ({ brand: Number(slide.dataset.brandIndex) })) : [];
    if (!slider || !caption || !subtitle || !counter || items.length !== 2 || !slides.length || typeof Swiper === "undefined") return;
    const brands = items.map(item => {
        const button = item.querySelector(".product-item-product");
        return { name:button.dataset.name, description:button.dataset.description, first:Number(button.dataset.firstSlide) };
    });
    function updateGallery(swiper) {
        const photoIndex = swiper.realIndex;
        const brandIndex = slides[photoIndex]?.brand ?? 0;
        const brand = brands[brandIndex];
        if (caption.textContent !== brand.name) caption.textContent = brand.name;
        if (subtitle.textContent !== brand.description) subtitle.textContent = brand.description;
        caption.dataset.brandIndex = String(brandIndex);
        counter.textContent = (photoIndex + 1) + " / " + slides.length;
        items.forEach((item, index) => {
            const active = index === brandIndex;
            const button = item.querySelector(".product-item-product");
            const details = item.querySelector(".product-item-details");
            if (!active && details.contains(document.activeElement)) items[brandIndex].querySelector(".product-item-product").focus({ preventScroll:true });
            item.classList.toggle("is-active", active);
            button.setAttribute("aria-expanded", String(active));
            details.setAttribute("aria-hidden", String(!active));
            details.inert = !active;
            item.querySelectorAll(".product-angle").forEach(angle => {
                const selected = Number(angle.dataset.slideIndex) === photoIndex;
                angle.classList.toggle("is-active", selected);
                angle.setAttribute("aria-pressed", String(selected));
                angle.tabIndex = active ? 0 : -1;
            });
        });
    }
    brandGallery = new Swiper(slider, {
        slidesPerView:1,
        speed:reducedMotionQuery.matches ? 0 : 800,
        effect:"fade",
        fadeEffect:{ crossFade:true },
        loop:slides.length > 1,
        loopPreventsSliding:false,
        grabCursor:true,
        keyboard:{ enabled:true, onlyInViewport:true },
        navigation:{ prevEl:section.querySelector(".products-prev"), nextEl:section.querySelector(".products-next") },
        a11y:{ prevSlideMessage:"이전 사진", nextSlideMessage:"다음 사진", slideLabelMessage:"{{index}} / {{slidesLength}}" },
        on:{ init:function () { updateGallery(this); }, slideChange:function () { updateGallery(this); } }
    });
    items.forEach((item, index) => {
        item.querySelector(".product-item-product").addEventListener("click", () => brandGallery.slideToLoop(brands[index].first));
        item.querySelectorAll(".product-angle").forEach(angle => {
            angle.addEventListener("click", () => brandGallery.slideToLoop(Number(angle.dataset.slideIndex)));
        });
    });
}

function syncBrandGalleryMotion() {
    if (brandGallery) brandGallery.params.speed = reducedMotionQuery.matches ? 0 : 800;
}

function initProductsSection() {
    const section = document.querySelector(".products-section");
    const visual = section?.querySelector(".products-visual");
    const content = section?.querySelector(".products-content");
    const title = section?.querySelector(".products-bg-title");
    const intro = section?.querySelector(".products-intro-title");
    const heading = section?.querySelector(".products-brand-heading");
    if (!section || !visual || !content || !title || !intro || !heading) return;
    const mobile = () => window.innerWidth <= 970;
    gsap.set(intro, { autoAlpha:1 });
    gsap.set(heading, { autoAlpha:0 });
    const timeline = gsap.timeline({ scrollTrigger: {
        id:"brand-gallery",
        trigger:section,
        start:"top top",
        end:"+=180%",
        pin:true,
        pinSpacing:true,
        scrub:.8,
        anticipatePin:1,
        invalidateOnRefresh:true
    } });
    timeline.fromTo(visual, {
        x:0,
        y:0,
        scale:() => mobile() ? .74 : .5,
        xPercent:() => mobile() ? 0 : -4,
        yPercent:0,
        transformOrigin:() => mobile() ? "50% 100%" : "100% 50%",
        borderRadius:24
    }, {
        scale:1,
        xPercent:0,
        borderRadius:0,
        duration:1.1,
        ease:"none",
        onUpdate:() => { if (brandGallery) brandGallery.params.touchRatio = 1 / (Number(gsap.getProperty(visual,"scaleX")) || 1); }
    }, 0);
    timeline.fromTo(title, { autoAlpha:1 }, { autoAlpha:0, duration:.5 }, 0);
    timeline.fromTo(content, { autoAlpha:1, y:0, color:"var(--text)" }, { autoAlpha:0, y:-18, duration:.2 }, .08);
    timeline.fromTo(visual, { "--brand-shade":0 }, { "--brand-shade":1, duration:.55, ease:"none" }, .55);
    timeline.set(content, { color:"var(--white)", y:18 }, .85);
    timeline.set(intro, { autoAlpha:0 }, .85);
    timeline.set(heading, { autoAlpha:1 }, .85);
    timeline.to(content, { autoAlpha:1, y:0, duration:.35 }, 1.1);
    timeline.to({}, { duration:.75 });
}
