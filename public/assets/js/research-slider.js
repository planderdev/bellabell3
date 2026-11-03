function initResearchSlider() {
    const slider = document.querySelector("#research-slider");
    const slideCount = slider ? slider.querySelectorAll(".swiper-slide").length : 0;
    if (!slider || !slideCount || typeof Swiper === "undefined") return;
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const looping = slideCount > 1;
    const swiper = new Swiper(slider, {
        slidesPerView:"auto",
        centeredSlides:true,
        loop:looping,
        loopAdditionalSlides:2,
        spaceBetween:16,
        breakpoints:{ 971:{ spaceBetween:36 } },
        speed:reducedMotion ? 0 : 600,
        grabCursor:true,
        slideToClickedSlide:true,
        keyboard:{ enabled:true, onlyInViewport:true },
        autoplay:looping && !reducedMotion ? { delay:3500, disableOnInteraction:false, pauseOnMouseEnter:true } : false
    });
    if (!swiper.autoplay?.enabled || !("IntersectionObserver" in window)) return;
    // Only advance while the section is on screen.
    new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) swiper.autoplay.start();
        else swiper.autoplay.stop();
    }).observe(slider);
}

document.addEventListener("DOMContentLoaded", initResearchSlider);
