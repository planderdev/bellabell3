
/* ==================================================
	LENIS SETTING
================================================== */

let fadeItems=[];
const localGray = "/assets/gray.svg";
const localLorem = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";
const reducedMotionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
let motionContext = null;
let lenisTicker = null;
let fadeTimer = null;
let introPlayed = false;
let closeLocalMenu = function () {};
const motionResizeCleanups = [];

function initSiteIcons(root = document) {
    if (!window.lucide || !root.querySelector('i[data-lucide]')) return;
    window.lucide.createIcons({ root, attrs:{ 'aria-hidden':'true', focusable:'false' } });
}

function createSiteIcon(name, className = '') {
    const icon = document.createElement('i');
    icon.dataset.lucide = name;
    icon.className = 'site-icon' + (className ? ' ' + className : '');
    icon.setAttribute('aria-hidden', 'true');
    icon.setAttribute('focusable', 'false');
    return icon;
}

function localHref(value, fallback = "#family") {
    if (["tel:0648030060", "mailto:partner@jejubellabell.com"].includes(value)) return value;
    if (typeof value === "string" && value.startsWith("#") && value.length > 1) {
        try {
            if (document.getElementById(decodeURIComponent(value.slice(1)))) return value;
        } catch (error) {}
    }
    return document.getElementById(fallback.slice(1)) ? fallback : "#main";
}

function navigateLocal(value) {
    const href = localHref(value);
    const target = document.getElementById(decodeURIComponent(href.slice(1)));
    if (!target) return;
    closeLocalMenu(false);
    const pin = typeof ScrollTrigger !== "undefined"
        ? ScrollTrigger.getAll().find(trigger => trigger.pin && trigger.trigger === target)
        : null;
    const destination = pin ? pin.start : target;
    if (window.lenis) window.lenis.scrollTo(destination, { immediate: reducedMotionQuery.matches });
    else if (pin) window.scrollTo({ top: pin.start, behavior: reducedMotionQuery.matches ? "instant" : "smooth" });
    else target.scrollIntoView({ behavior: reducedMotionQuery.matches ? "instant" : "smooth" });
}

function motionResize(callback) {
    window.addEventListener("resize", callback);
    motionResizeCleanups.push(() => window.removeEventListener("resize", callback));
}

function visibleFocusable(container) {
    return [...container.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]')]
        .filter(item => !item.closest("[inert]") && item.getClientRects().length && getComputedStyle(item).visibility !== "hidden");
}


function initCommon(){

	if(typeof gsap==="undefined") return;
	if(typeof ScrollTrigger==="undefined") return;
	if(typeof Lenis==="undefined") return;

	gsap.registerPlugin(ScrollTrigger);
    if (typeof ScrollToPlugin !== "undefined") gsap.registerPlugin(ScrollToPlugin);

	const lenis=new Lenis({
		autoRaf:false,
		lerp:0.08,
		smoothWheel:true,
		syncTouch:false
	});

	lenis.on("scroll",function(){
		ScrollTrigger.update();
		checkFade();
	});

	lenisTicker = function(time){ lenis.raf(time*1000); };
	gsap.ticker.add(lenisTicker);

	gsap.ticker.lagSmoothing(0);

	window.lenis=lenis;
 
}

 


function initFadeUp(){

	fadeItems=document.querySelectorAll("[data-fade]");

	if(!fadeItems.length) return;

	gsap.set(fadeItems,{
		opacity:0,
		y:30
	});

	fadeTimer = setTimeout(function(){

		checkFade();

	},100);

}


function checkFade(){
    if (reducedMotionQuery.matches) return;

	const start=window.innerHeight*.85;

	fadeItems.forEach(function(item){

		const rect=item.getBoundingClientRect();

		if(rect.top<start&&rect.bottom>0){

			if(item.classList.contains("is-fade-up")) return;

			item.classList.add("is-fade-up");

			gsap.killTweensOf(item);

			gsap.to(item,{
				opacity:1,
				y:0,
				duration:1.2,
				ease:"power3.out",
				overwrite:true
			});

		}else if(rect.top>=start){

			if(!item.classList.contains("is-fade-up")) return;

			item.classList.remove("is-fade-up");

			gsap.killTweensOf(item);

			gsap.to(item,{
				opacity:0,
				y:30,
				duration:.8,
				ease:"power2.out",
				overwrite:true
			});

		}

	});

}


/* ==================================================
	HEADER
================================================== */

function initHeaderMenu() {
    const gnbList = document.querySelector(".gnb > ul");
    const allMenu = document.querySelector(".all-menu");
    const allMenuNav = document.querySelector(".all-menu-nav");
    const menuButton = document.querySelector(".menu-button");
    const header = document.querySelector(".header");
    if (!gnbList || !allMenu || !allMenuNav || !menuButton || !header) return;
    const clone = gnbList.cloneNode(true);
    clone.classList.add("all-menu-list");
    clone.querySelectorAll("[id]").forEach(item => item.removeAttribute("id"));
    allMenuNav.appendChild(clone);
    allMenu.id ||= "all-menu";
    allMenu.setAttribute("aria-hidden", "true");
    allMenu.setAttribute("data-lenis-prevent", "");
    allMenu.inert = true;
    menuButton.setAttribute("aria-controls", allMenu.id);
    menuButton.setAttribute("aria-expanded", "false");
    let previousFocus = null;
    let overflowBefore = "";
    let wasLenisStopped = false;
    let closeVisualTimer = null;
    const inertBefore = new Map();
    const background = [
        ...[...document.body.children].filter(item => item !== allMenu && !item.contains(allMenu) && !["SCRIPT", "STYLE", "LINK"].includes(item.tagName)),
        ...header.querySelectorAll(".gnb,.language-wrap")
    ];
    function openMenu() {
        if (allMenu.classList.contains("is-open")) return;
        clearTimeout(closeVisualTimer);
        document.body.classList.remove("menu-closing");
        previousFocus = menuButton;
        wasLenisStopped = !!window.lenis?.isStopped;
        window.lenis?.stop();
        overflowBefore = document.body.style.overflow;
        document.body.style.overflow = "hidden";
        allMenu.inert = false;
        allMenu.classList.add("is-open");
        document.body.classList.add("menu-open");
        allMenu.setAttribute("aria-hidden", "false");
        menuButton.setAttribute("aria-expanded", "true");
        menuButton.setAttribute("aria-label", "전체 메뉴 닫기");
        background.forEach(item => { inertBefore.set(item, item.inert); item.inert = true; });
        menuButton.focus({ preventScroll: true });
    }
    closeLocalMenu = function (restoreFocus = true) {
        if (!allMenu.classList.contains("is-open")) return;
        allMenu.classList.remove("is-open");
        document.body.classList.remove("menu-open");
        document.body.classList.add("menu-closing");
        clearTimeout(closeVisualTimer);
        closeVisualTimer = setTimeout(() => document.body.classList.remove("menu-closing"), reducedMotionQuery.matches ? 0 : 600);
        document.body.style.overflow = overflowBefore;
        background.forEach(item => { item.inert = inertBefore.get(item) || false; });
        if (restoreFocus && previousFocus?.isConnected) previousFocus.focus({ preventScroll: true });
        else if (allMenu.contains(document.activeElement)) document.activeElement.blur();
        allMenu.setAttribute("aria-hidden", "true");
        allMenu.inert = true;
        menuButton.setAttribute("aria-expanded", "false");
        menuButton.setAttribute("aria-label", "전체 메뉴 열기");
        if (!wasLenisStopped) window.lenis?.start();
    };
    menuButton.addEventListener("click", () => {
        if (allMenu.classList.contains("is-open")) closeLocalMenu();
        else openMenu();
    });
    document.addEventListener("keydown", event => {
        if (!allMenu.classList.contains("is-open")) return;
        if (event.key === "Escape") { event.preventDefault(); closeLocalMenu(); }
        if (event.key !== "Tab") return;
        const focusable = [...visibleFocusable(header), ...visibleFocusable(allMenu)];
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (!first) { event.preventDefault(); return; }
        if (!focusable.includes(document.activeElement)) { event.preventDefault(); menuButton.focus(); return; }
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
    const menuItems = clone.querySelectorAll(":scope > li");
    const mobileMenuQuery = window.matchMedia("(max-width: 1024px)");
    function setMenuDepth(item, open) {
        const link = item.querySelector(":scope > a");
        const depth = item.querySelector(":scope > .gnb-depth");
        if (!link || !depth) return;
        const visible = !mobileMenuQuery.matches || open;
        if (!visible && depth.contains(document.activeElement)) link.focus({ preventScroll:true });
        item.classList.toggle("is-open", open);
        link.setAttribute("aria-expanded", String(visible));
        depth.setAttribute("aria-hidden", String(!visible));
        depth.inert = !visible;
    }
    menuItems.forEach((item, index) => {
        const link = item.querySelector(":scope > a");
        const depth = item.querySelector(":scope > .gnb-depth");
        if (!link || !depth) return;
        const inner = document.createElement("div");
        inner.className = "accordion-content";
        while (depth.firstChild) inner.appendChild(depth.firstChild);
        depth.appendChild(inner);
        depth.id = "all-menu-depth-" + index;
        link.setAttribute("aria-controls", depth.id);
        const depthIcon = document.createElement('span');
        depthIcon.className = 'menu-depth-icon';
        depthIcon.setAttribute('aria-hidden', 'true');
        depthIcon.append(createSiteIcon('plus', 'menu-expand-icon'), createSiteIcon('minus', 'menu-collapse-icon'));
        link.appendChild(depthIcon);
        setMenuDepth(item, false);
        link.addEventListener("click", event => {
            if (!mobileMenuQuery.matches) return;
            event.preventDefault();
            const isOpen = item.classList.contains("is-open");
            menuItems.forEach(other => { if (other !== item) setMenuDepth(other, false); });
            setMenuDepth(item, !isOpen);
        });
    });
    mobileMenuQuery.addEventListener("change", () => menuItems.forEach(item => setMenuDepth(item, item.classList.contains("is-open"))));
}

function initHeaderHover() {
    const header = document.querySelector(".header");
    const gnb = document.querySelector(".gnb");
    if (!header || !gnb) return;
    gnb.addEventListener("mouseenter", () => { if (window.scrollY <= 10) header.classList.add("is-hover"); });
    gnb.addEventListener("mouseleave", () => { if (window.scrollY <= 10 && !gnb.contains(document.activeElement)) header.classList.remove("is-hover"); });
    gnb.addEventListener("focusin", event => {
        header.classList.remove("is-hide");
        if (window.scrollY <= 10) header.classList.add("is-hover");
        gnb.querySelectorAll(":scope > ul > li").forEach(item => {
            const depth = item.querySelector(":scope > .gnb-depth");
            if (!depth) return;
            if (item.contains(event.target)) Object.assign(depth.style, { opacity:"1", visibility:"visible" });
            else { depth.style.removeProperty("opacity"); depth.style.removeProperty("visibility"); }
        });
    });
    gnb.addEventListener("focusout", () => setTimeout(() => {
        if (gnb.contains(document.activeElement)) return;
        gnb.querySelectorAll(".gnb-depth").forEach(depth => { depth.style.removeProperty("opacity"); depth.style.removeProperty("visibility"); });
        if (!gnb.matches(":hover")) header.classList.remove("is-hover");
    }, 0));
    gnb.addEventListener("keydown", event => {
        if (event.key !== "Escape") return;
        const item = event.target.closest(".gnb > ul > li");
        const link = item?.querySelector(":scope > a");
        const depth = item?.querySelector(":scope > .gnb-depth");
        if (link && depth) {
            event.preventDefault();
            link.focus();
            depth.style.opacity = "0";
            depth.style.visibility = "hidden";
        }
    });
}

function initHeaderScroll(){

	const header=document.querySelector(".header");

	if(!header) return;

	const isMain=document.body.classList.contains("main");

	let lastScroll=0;


	function updateHeader(current){

		/* 메인 최상단 */

		if(isMain&&current<=10){
            lastScroll = current;

			header.classList.remove("is-hide");
			header.classList.add("is-transparent");

			return;

		}


		/* 서브페이지 또는 메인 스크롤 */

		header.classList.remove("is-transparent");
		header.classList.remove("is-hover");


		if(current>lastScroll&&current>10){

			header.classList.add("is-hide");

		}else{

			header.classList.remove("is-hide");

		}


		lastScroll=current;

	}


    window.addEventListener("scroll", function () {
        updateHeader(window.scrollY);
    }, { passive: true });

	updateHeader(window.scrollY);

}

function initLanguage() {
    const wrap = document.querySelector(".language-wrap");
    const button = wrap?.querySelector(".language");
    const links = wrap ? [...wrap.querySelectorAll(".language-list a, .language-list button")] : [];
    if (!wrap || !button) return;
    button.setAttribute("aria-expanded", "false");
    const setOpen = open => {
        const list = wrap.querySelector(".language-list");
        if (!open && list?.contains(document.activeElement)) button.focus({ preventScroll:true });
        wrap.classList.toggle("is-open", open);
        button.setAttribute("aria-expanded", String(open));
        list?.setAttribute("aria-hidden", String(!open));
        if (list) list.inert = !open;
        links.forEach(link => { link.tabIndex = open ? 0 : -1; });
    };
    setOpen(false);
    button.addEventListener("click", event => { event.stopPropagation(); setOpen(!wrap.classList.contains("is-open")); });
    button.addEventListener("keydown", event => {
        if (event.key === "ArrowDown") { event.preventDefault(); setOpen(true); links[0]?.focus(); }
    });
    wrap.addEventListener("keydown", event => {
        if (event.key === "Escape") { event.preventDefault(); setOpen(false); button.focus(); }
        if (!["ArrowDown", "ArrowUp", "Home", "End"].includes(event.key) || event.target === button) return;
        event.preventDefault();
        let index = links.indexOf(document.activeElement);
        index = event.key === "Home" ? 0 : event.key === "End" ? links.length - 1 : (index + (event.key === "ArrowDown" ? 1 : -1) + links.length) % links.length;
        links[index]?.focus();
    });
    links.forEach(link => link.addEventListener("click", () => setOpen(false)));
    document.addEventListener("click", event => { if (!wrap.contains(event.target)) setOpen(false); });
    wrap.addEventListener("focusout", () => setTimeout(() => { if (!wrap.contains(document.activeElement)) setOpen(false); }, 0));
}

function initMainIntro(lenis) {

    const visual = document.querySelector(".main-visual");
    if (!visual) return;

    const visualSlider = visual.querySelector(".main-slider");
    const heroContent = visual.querySelector(".hero-content");
    const header = document.querySelector(".header");
    const slideInfo = visual.querySelector(".main-slide-info");

    if (!visualSlider) return;

    if (lenis) lenis.stop();

    // 등장 요소 초기 상태
    gsap.set([header, slideInfo, heroContent].filter(Boolean), {
        autoAlpha: 0,
        y: 20
    });

    const introTl = gsap.timeline({
        delay: 1,
        onComplete: function () {

            visual.classList.add("is-ready");

            // 인트로 종료 후 스크롤 활성화
            if (lenis) lenis.start();
        }
    });

    // 비주얼 확대 후 사진 위 로고와 카피를 계속 표시한다.
    introTl.to(visualSlider, {
            width: "100vw",
            left: 0,
            top: 0,
            height: "100dvh",
            borderRadius: 0,
            duration: 1.4,
            ease: "power3.inOut"
        }, .5 );

    // 비주얼 확대 완료 후 순서대로 등장
    if (header) {
        introTl.to(header, {
            autoAlpha: 1,
            y: 0,
            duration: 0.6,
            ease: "power2.out"
        }, "-=0.1");
    }

    if (slideInfo) {
        introTl.to(slideInfo, {
            autoAlpha: 1,
            y: 0,
            duration: 0.6,
            ease: "power2.out"
        }, "-=0.3");
    }

    if (heroContent) {
        introTl.to(heroContent, {
            autoAlpha: 1,
            y: 0,
            duration: 0.6,
            ease: "power2.out"
        }, 1.8);
    }
}



function initMainSwiper(){

	const slider=document.querySelector(".main-slider .swiper");
	const progress=document.querySelector(".slide-progress");

	if(!slider||!progress||typeof Swiper==="undefined") return;

	const slides=slider.querySelectorAll(".swiper-slide");

	slides.forEach(function(){
		const span=document.createElement("span");
		progress.appendChild(span);
	});

	const mainSwiper=new Swiper(slider,{
		slidesPerView:1,
		speed:reducedMotionQuery.matches ? 0 : 1200,
		effect:"fade",
		fadeEffect:{
			crossFade:true
		},
		loop:slides.length > 1,
		allowTouchMove:true,
		autoplay: reducedMotionQuery.matches ? false : {
			delay:5000,
			disableOnInteraction:false
		},
		on:{
			init:function(){
				updateProgress(this);
			},
			slideChange:function(){
				updateProgress(this);
			}
		}
	});

	function updateProgress(swiper){

		const items=progress.querySelectorAll("span");

		items.forEach(function(item,index){
			item.classList.toggle("active",index===swiper.realIndex);
		});

	}

	window.mainSwiper=mainSwiper;

} 


function initSolutionSection() {
    const section = document.querySelector(".solution-section");
    if (!section) return;

    const content = section.querySelector(".solution-content");
    if (!content) return;

    const cardData = [...section.querySelectorAll(".solution-card-data > div")].map(function (item) {
        return {
            title: item.dataset.title || "Lorem ipsum",
            desc: item.dataset.desc || localLorem,
            image: localGray,
            href: localHref(item.dataset.href)
        };
    });

    const solutionMediaWrap = section.querySelector(".solution-media-wrap");
    const solutionSvg = section.querySelector(".solution-media-svg");
    const solutionClipShape = section.querySelector(".solution-clip-shape");
    const solutionMediaVideo = section.querySelector(".solution-media-vid");
    if (!solutionMediaWrap || !solutionSvg || !solutionClipShape || !solutionMediaVideo) return;
    const maskBounds = {
        x: 0,
        y: 0,
        width: Number(solutionClipShape.dataset.maskWidth),
        height: Number(solutionClipShape.dataset.maskHeight)
    };

    function getMaskTransform(expanded = false) {
        const width = solutionMediaWrap.clientWidth;
        const height = solutionMediaWrap.clientHeight;
        const initialWidth = Math.min(540, Math.max(260, width * .36));
        // The expanded viewport stays inside the solid base of the mountain.
        const scale = expanded
            ? Math.max(width / (maskBounds.width * .5), height / (maskBounds.height * .3)) * 1.12
            : initialWidth / maskBounds.width;
        const anchorX = maskBounds.x + maskBounds.width / 2;
        const anchorY = maskBounds.y + maskBounds.height * (expanded ? .76 : .5);
        return `matrix(${scale} 0 0 ${scale} ${width / 2 - anchorX * scale} ${height / 2 - anchorY * scale})`;
    }

    function setSolutionMedia() {
        solutionSvg.setAttribute("viewBox", `0 0 ${solutionMediaWrap.clientWidth} ${solutionMediaWrap.clientHeight}`);
        solutionClipShape.setAttribute("transform", getMaskTransform());
    }
    setSolutionMedia();

    /*
        모바일 카드 생성
        970px 이하에서는 카드만 출력하고 종료
    */
    if (window.innerWidth <= 970 || reducedMotionQuery.matches) {
        if (!content.querySelector(".solution-mobile-list")) {
            const list = document.createElement("div");
            list.className = "solution-mobile-list";

            cardData.forEach(function (data) {
                const card = document.createElement("a");
                card.className = "solution-card";
                card.href = localHref(data.href);

                const info = document.createElement("div");
                info.className = "card-info";

                const title = document.createElement("strong");
                title.className = "card-title";
                title.textContent = data.title || "";

                const desc = document.createElement("p");
                desc.className = "card-desc";
                desc.textContent = data.desc || "";

                info.appendChild(title);
                info.appendChild(desc);

                const imageWrap = document.createElement("div");
                imageWrap.className = "solution-card-image";

                const img = document.createElement("img");
                img.src = localGray;
                img.alt = "";

                const more = document.createElement("span");
                more.className = "more-btn";
                more.appendChild(createSiteIcon('plus'));

                imageWrap.appendChild(img);
                imageWrap.appendChild(more);

                card.appendChild(info);
                card.appendChild(imageWrap);
                list.appendChild(card);
            });

            content.appendChild(list);
            initSiteIcons(list);
        }

        motionResize(setSolutionMedia);
        return;
    }

    /*
        여기부터 기존 PC 전용 코드
    */


    const intro = section.querySelector(".solution-intro");

    const contentHead = content.querySelector(".section-head");

    const card = section.querySelector(".solution-card");
    const cardTitle = card?.querySelector(".card-title");
    const cardDesc = card?.querySelector(".card-desc");
    const cardImg = card?.querySelector(".solution-card-img");
    const pagination = section.querySelectorAll(".solution-card-pagination span");

    if (
        !solutionMediaWrap ||
        !solutionSvg ||
        !solutionClipShape ||
        !solutionMediaVideo ||
        !intro ||
        !card ||
        !cardTitle ||
        !cardDesc ||
        !cardImg
    ) return;

    let currentIndex = 0;
    let changing = false;
    let disposed = false;
    motionResizeCleanups.push(function () {
        disposed = true;
        section.querySelectorAll(".solution-card-img-overlay").forEach(overlay => {
            gsap.killTweensOf(overlay);
            overlay.remove();
        });
    });

    /*
        카드 변경
    */
    function changeCard(index) {
        if (disposed || index === currentIndex || changing || !cardData[index]) return;

        changing = true;

        const data = cardData[index];
        const preload = new Image();

        preload.onload = function () {
            if (disposed) return;
            const overlay = document.createElement("img");

            overlay.src = preload.src;
            overlay.alt = "";
            overlay.className = "solution-card-img solution-card-img-overlay";

            Object.assign(overlay.style, {
                position: "absolute",
                inset: "0",
                width: "100%",
                height: "100%",
                objectFit: "cover",
                opacity: "0",
                zIndex: "2",
                pointerEvents: "none"
            });

            const parent = cardImg.parentElement;

            if (getComputedStyle(parent).position === "static") {
                parent.style.position = "relative";
            }

            parent.appendChild(overlay);

            cardTitle.textContent = data.title || "";
            cardDesc.textContent = data.desc || "";
            card.href = localHref(data.href);

            pagination.forEach(function (item, i) {
                item.classList.toggle("active", i === index);
            });

            gsap.to(overlay, {
                opacity: 1,
                duration: 0.3,
                ease: "power2.inOut",
                onComplete: function () {
                    cardImg.src = data.image;
                    overlay.remove();

                    currentIndex = index;
                    changing = false;
                }
            });
        };

        preload.onerror = function () {
            changing = false;
        };

        preload.src = data.image;
    }

    /*
        PC 스크롤 타임라인
    */
    const solutionTl = gsap.timeline({
        scrollTrigger: {
            id: "global-reveal",
            trigger: section,
            start: "top top",
            end: "+=600%",
            pin: true,
            scrub: 1,
            anticipatePin: 1,
            invalidateOnRefresh: true,

            onUpdate: function (self) {
                const progress = self.progress;

                if (progress < 0.68) {
                    changeCard(0);
                } else if (progress < 0.78) {
                    changeCard(1);
                } else if (progress < 0.87) {
                    changeCard(2);
                } else {
                    changeCard(3);
                }
            }
        }
    });

    solutionTl.fromTo(solutionClipShape, {
        attr: { transform: () => getMaskTransform() }
    }, {
        attr: { transform: () => getMaskTransform(true) },
        duration: .8,
        ease: "power2.inOut"
    }, 0);

    gsap.set(solutionMediaVideo, { filter: "brightness(1)" });
    solutionTl.to(solutionMediaVideo, {
        filter: "brightness(.7)",
        duration: .2,
        ease: "power1.out"
    }, .75);

    // Reveal the heading and cards after the mountain has filled the screen.
    solutionTl.set(content, { autoAlpha: 1 }, .85);
    solutionTl.fromTo(contentHead, { autoAlpha: 0, y: 30 }, {
        autoAlpha: 1,
        y: 0,
        duration: .2,
        ease: "power2.out"
    }, .88);
    solutionTl.fromTo(card, { autoAlpha: 0, y: 30 }, {
        autoAlpha: 1,
        y: 0,
        duration: .2,
        ease: "power2.out"
    }, .95);
    solutionTl.to({}, { duration: .6 });

    motionResize(function () {
        setSolutionMedia();
        solutionTl.invalidate();
        ScrollTrigger.refresh();
    });
}

function initStandardSection(){

	const section=document.querySelector(".standard-section");
	const slider=section?.querySelector(".standard-slider");
	const track=section?.querySelector(".standard-track");

	if(!section||section.hidden||!slider||!track) return;

	function getDistance(){

		const lastCard=track.querySelector(".standard-card:last-child");

		if(!lastCard) return 0;

		const rightSpace=window.innerWidth*0.033;
		const lastRight=lastCard.offsetLeft+lastCard.offsetWidth;

		return Math.max(
			0,
			lastRight-(slider.clientWidth-rightSpace)
		);
	}

	const standardTl=gsap.timeline({
		scrollTrigger:{
			trigger:slider,
			start:"center center",
			end:function(){
				return "+="+getDistance();
			},
			pin:true,
			scrub:1,
			anticipatePin:1,
			invalidateOnRefresh:true
		}
	});

	standardTl.to(track,{
		x:function(){
			return -getDistance();
		},
		ease: "none",
		duration: getDistance()
	});

	standardTl.to({}, {
		duration: 1
	});
	motionResize(function(){
		ScrollTrigger.refresh();
	});

}

document.querySelectorAll(".export-map-pin").forEach((pin) => {
    pin.addEventListener("click", (e) => {
        e.stopPropagation();

        const wasActive = pin.classList.contains("is-active");

        document.querySelectorAll(".export-map-pin.is-active").forEach((activePin) => {
            activePin.classList.remove("is-active");
        });

        if (!wasActive) {
            pin.classList.add("is-active");
        }
    });

    pin.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            pin.click();
        }

        if (e.key === "Escape") {
            pin.classList.remove("is-active");
            pin.blur();
        }
    });
});

document.addEventListener("click", () => {
    document.querySelectorAll(".export-map-pin.is-active").forEach((pin) => {
        pin.classList.remove("is-active");
    });
});
 
function initLocalRouting() {
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.setAttribute("href", localHref(link.getAttribute("href"), "#partner"));
        link.removeAttribute("target");
    });
    document.addEventListener("click", event => {
        const link = event.target.closest("a[href]");
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (!link.getAttribute("href").startsWith("#")) return;
        event.preventDefault();
        navigateLocal(link.getAttribute("href"));
    });
}

function finishMainIntro() {
    const visual = document.querySelector(".main-visual");
    if (!visual) return;
    visual.classList.add("is-ready");
    gsap.set(visual.querySelector(".main-slider"), { width:"100vw", left:0, top:0, height:"100dvh", borderRadius:0 });
    gsap.set([document.querySelector(".header"), visual.querySelector(".main-slide-info"), visual.querySelector(".hero-content")].filter(Boolean), { autoAlpha:1, y:0 });
}

function initAboutReveal() {
    const section = document.querySelector(".about-reveal-section");
    const heading = section?.querySelector(".about-reveal-title");
    if (!section || !heading) return;
    if (!heading.querySelector(".about-reveal-word")) {
        heading.querySelectorAll(".about-reveal-line").forEach(line => {
            const fragment = document.createDocumentFragment();
            (line.textContent.match(/\S+|\s+/g) || []).forEach(part => {
                if (/^\s+$/.test(part)) fragment.appendChild(document.createTextNode(part));
                else {
                    const word = document.createElement("span");
                    word.className = "about-reveal-word";
                    word.textContent = part;
                    fragment.appendChild(word);
                }
            });
            line.replaceChildren(fragment);
        });
    }
    const words = [...heading.querySelectorAll(".about-reveal-word")];
    const total = words.reduce((count, word) => count + [...word.textContent].length, 0);
    if (!total) return;
    gsap.set(words, { "--ink": "0%" });
    const timeline = gsap.timeline({ scrollTrigger: {
        id: "about-reveal",
        trigger: section,
        start: "top top",
        end: () => "+=" + Math.round(window.innerHeight * 1.8),
        pin: true,
        pinSpacing: true,
        scrub: .65,
        anticipatePin: 1,
        invalidateOnRefresh: true
    } });
    timeline.to({}, { duration: .04 });
    words.forEach(word => timeline.to(word, {
        "--ink": "100%",
        duration: [...word.textContent].length / total,
        ease: "none"
    }));
    timeline.to({}, { duration: .12 });
}

function initBongbongStory() {
    const section = document.querySelector(".bongbong-story-section");
    const stage = section?.querySelector(".bongbong-story-stage");
    const deck = section?.querySelector(".bongbong-story-cards");
    const cards = deck ? [...deck.querySelectorAll(".bongbong-story-card")] : [];
    if (!section || !stage || !deck || cards.length !== 4) return;
    const mobile = () => window.innerWidth <= 970;
    const availableHeight = () => {
        const style = getComputedStyle(stage);
        return stage.clientHeight - Number.parseFloat(style.paddingTop) - Number.parseFloat(style.paddingBottom);
    };
    const overflow = () => Math.max(0, deck.offsetTop + deck.offsetHeight + 24 - (stage.clientHeight - Number.parseFloat(getComputedStyle(stage).paddingBottom)));
    const timeline = gsap.timeline({ scrollTrigger: {
        id: "bongbong-story",
        trigger: section,
        start: "top top",
        end: () => "+=" + Math.round(window.innerHeight * 2.1 + overflow()),
        pin: true,
        pinSpacing: true,
        scrub: .8,
        anticipatePin: 1,
        invalidateOnRefresh: true
    } });
    timeline.to({}, { duration: .1 });
    timeline.fromTo(cards, {
        x: (index, card) => deck.clientWidth / 2 - card.offsetLeft - card.offsetWidth / 2 + [-60, -20, 40, 0][index],
        y: (index, card) => Math.min(deck.offsetHeight, availableHeight()) / 2 - card.offsetTop - card.offsetHeight / 2 + [18, -18, 10, 0][index],
        rotation: index => [-9, 7, -4, 0][index],
        scale: .96
    }, {
        x: 0,
        y: index => mobile() ? 0 : [16, -16, 12, 0][index],
        rotation: index => (mobile() ? [-4, 3, -2, 0] : [-7, 2, -3, 3])[index],
        scale: 1,
        duration: 1.1,
        ease: "power2.inOut"
    }, .1);
    timeline.fromTo(deck, { y: 0 }, { y: () => -overflow(), duration: .5, ease: "none" }, 1.2);
    timeline.to({}, { duration: .3 });
}

function rebuildMainMotion() {
    if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") return;
    closeLocalMenu();
    clearTimeout(fadeTimer);
    motionResizeCleanups.splice(0).forEach(cleanup => cleanup());
    if (fadeItems.length) gsap.killTweensOf(fadeItems);
    fadeItems.forEach(item => item.classList.remove("is-fade-up"));
    motionContext?.revert();
    motionContext = null;
    document.querySelectorAll(".solution-content,.solution-content .section-head").forEach(item => {
        gsap.set(item, { clearProps:"opacity,visibility,transform" });
    });
    if (lenisTicker) gsap.ticker.remove(lenisTicker);
    lenisTicker = null;
    window.lenis?.destroy();
    window.lenis = null;
    syncBrandGalleryMotion();
    document.querySelectorAll(".solution-mobile-list").forEach(list => list.remove());
    if (reducedMotionQuery.matches) {
        finishMainIntro();
        initSolutionSection();
        fadeItems = document.querySelectorAll("[data-fade]");
        if (fadeItems.length) gsap.set(fadeItems, { opacity:1, y:0 });
        window.mainSwiper?.autoplay?.stop();
        if (window.mainSwiper) window.mainSwiper.params.speed = 0;
        document.querySelectorAll("video").forEach(video => video.pause());
        introPlayed = true;
    } else {
        initCommon();
        if (window.mainSwiper) {
            window.mainSwiper.params.speed = 1200;
            if (window.mainSwiper.slides.length > 1) window.mainSwiper.autoplay?.start();
        }
        motionContext = gsap.context(function () {
            initFadeUp();
            if (!introPlayed && window.scrollY <= 10) {
                initMainIntro(window.lenis);
                introPlayed = true;
            } else finishMainIntro();
            initAboutReveal();
            initBongbongStory();
            initStandardSection();
            initProductsSection();
            initSolutionSection();
        });
    }
    ScrollTrigger.refresh();
}

document.addEventListener("DOMContentLoaded", function () {
    initHeaderMenu();
    initHeaderHover();
    initHeaderScroll();
    initLanguage();
    
    if (document.querySelector(".main-page")) {
        initMainSwiper();
        initBrandGallery();
        rebuildMainMotion();
        let desktop = window.innerWidth > 970;
        let resizeFrame = 0;
        window.addEventListener("resize", function () {
            cancelAnimationFrame(resizeFrame);
            resizeFrame = requestAnimationFrame(function () {
                const nextDesktop = window.innerWidth > 970;
                if (nextDesktop !== desktop) { desktop = nextDesktop; rebuildMainMotion(); }
            });
        }, { passive:true });
        reducedMotionQuery.addEventListener("change", rebuildMainMotion);
        document.fonts?.ready.then(() => { if (typeof ScrollTrigger !== "undefined") ScrollTrigger.refresh(); });
        window.addEventListener("load", () => { if (typeof ScrollTrigger !== "undefined") ScrollTrigger.refresh(); }, { once:true });
    }
    initLocalRouting();
    initSiteIcons();
});
