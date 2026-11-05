
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
				// Cards in one row appear in order; stacked on mobile, scrolling already sets the order.
				delay:window.innerWidth>970?Number(item.dataset.fadeDelay)||0:0,
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
        menuButton.setAttribute("aria-label", menuButton.dataset.labelClose);
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
        menuButton.setAttribute("aria-label", menuButton.dataset.labelOpen);
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

	const isMain=document.body.classList.contains("main")||document.body.classList.contains("has-hero-header");

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
    // Switching language keeps the reader on the same section.
    links.forEach(link => link.addEventListener("click", () => { link.hash = location.hash; }));
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

    if (!visualSlider) return;

    if (lenis) lenis.stop();

    // 등장 요소 초기 상태
    gsap.set([header, heroContent].filter(Boolean), {
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

    if (heroContent) {
        introTl.to(heroContent, {
            autoAlpha: 1,
            y: 0,
            duration: 0.6,
            ease: "power2.out"
        }, 1.8);
    }
}



function syncBackgroundVideo(video) {
    const rect = video.getBoundingClientRect();
    const visible = rect.bottom >= 0 && rect.top <= window.innerHeight;
    if (visible && !reducedMotionQuery.matches) video.play().catch(() => {});
    else video.pause();
}

// The hero plays its films in turn and loops back to the first. Only the visible one decodes.
function initHeroVideos() {
    const videos = [...document.querySelectorAll("[data-hero-video]")];
    if (!videos.length) return;
    const media = videos[0].parentElement;
    let current = 0;
    const active = () => videos[current];
    const sync = () => {
        const rect = media.getBoundingClientRect();
        const visible = rect.bottom >= 0 && rect.top <= window.innerHeight;
        videos.forEach(video => { if (video !== active()) video.pause(); });
        if (visible && !reducedMotionQuery.matches) active().play().catch(() => {});
        else active().pause();
    };
    const show = index => {
        current = index;
        const video = active();
        // A server without range requests cannot rewind; reloading starts the film over instead.
        const seekable = video.seekable.length && video.seekable.end(0) > 1;
        if (seekable) video.currentTime = 0;
        else video.load();
        videos.forEach(other => other.classList.toggle("is-active", other === video));
        sync();
    };
    videos.forEach((video, index) => {
        video.addEventListener("ended", () => show((index + 1) % videos.length));
        // Start fetching the next film once the current one is under way.
        video.addEventListener("playing", () => {
            const next = videos[(index + 1) % videos.length];
            if (next.preload === "none") { next.preload = "auto"; next.load(); }
        }, { once: true });
    });
    if (videos.length === 1) videos[0].loop = true;
    new IntersectionObserver(sync).observe(media);
    reducedMotionQuery.addEventListener("change", sync);
    sync();
}

function initPillarVideo() {
    const section = document.querySelector(".pillars-section");
    if (!section) return;
    const buttons = [...section.querySelectorAll(".pillars-label")];
    const panelOf = button => document.getElementById(button.getAttribute("aria-controls"));
    const videos = buttons.map(panelOf).filter(panel => panel?.tagName === "VIDEO");
    const syncPlaying = () => buttons.forEach(button => button.classList.toggle("is-playing", panelOf(button)?.tagName === "VIDEO" && !panelOf(button).paused));

    buttons.forEach(button => button.addEventListener("click", () => {
        const panel = panelOf(button);
        const video = panel.tagName === "VIDEO" ? panel : null;
        if (button.classList.contains("is-active")) {
            // Pressing the shown label again plays or pauses its video.
            if (video) video.paused ? video.play().catch(() => {}) : video.pause();
            return;
        }
        videos.forEach(other => { if (other !== video) other.pause(); });
        buttons.forEach(other => {
            const active = other === button;
            other.classList.toggle("is-active", active);
            other.setAttribute("aria-pressed", String(active));
            panelOf(other).classList.toggle("is-active", active);
        });
        video?.play().catch(() => {});
    }));

    videos.forEach(video => ["play", "pause", "ended"].forEach(type => video.addEventListener(type, syncPlaying)));
    new IntersectionObserver(entries => {
        if (!entries[0].isIntersecting) videos.forEach(video => video.pause());
    }).observe(section);
}

function initBackgroundVideos() {
    const videos = ["about-video", "product-hero-video", "ingredients-video"].map(id => document.getElementById(id)).filter(Boolean);
    initHeroVideos();
    // 화면을 벗어나면 디코딩을 멈춰 다른 섹션의 스크롤 모션을 방해하지 않는다.
    const observer = new IntersectionObserver(entries => entries.forEach(entry => syncBackgroundVideo(entry.target)));
    videos.forEach(video => {
        observer.observe(video);
        reducedMotionQuery.addEventListener("change", () => syncBackgroundVideo(video));
        syncBackgroundVideo(video);
    });
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
            image: item.dataset.image || localGray,
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
                img.src = data.image;
                img.alt = "";
                img.loading = "lazy";

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
    const onMainPage = !!document.querySelector(".main-page");
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        const href = link.getAttribute("href");
        // Shared header and footer anchors point at main page sections unless this page has the target.
        if (onMainPage) link.setAttribute("href", localHref(href, "#partner"));
        else if (!document.getElementById(href.slice(1))) link.setAttribute("href", document.body.dataset.home + href);
        link.removeAttribute("target");
    });
    document.addEventListener("click", event => {
        const link = event.target.closest("a[href]");
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (!link.getAttribute("href").startsWith("#")) {
            // A link to a tab on this page doesn't reload, so the full menu has to close itself.
            if (link.pathname === location.pathname && link.hash) closeLocalMenu(false);
            return;
        }
        event.preventDefault();
        navigateLocal(link.getAttribute("href"));
    });
}

function finishMainIntro() {
    const visual = document.querySelector(".main-visual");
    if (!visual) return;
    visual.classList.add("is-ready");
    gsap.set(visual.querySelector(".main-slider"), { width:"100vw", left:0, top:0, height:"100dvh", borderRadius:0 });
    gsap.set([document.querySelector(".header"), visual.querySelector(".hero-content")].filter(Boolean), { autoAlpha:1, y:0 });
}

function initAboutReveal() {
    const section = document.querySelector(".about-reveal-section");
    const heading = section?.querySelector(".about-reveal-title");
    if (!section || !heading) return;
    if (!heading.querySelector(".about-reveal-word")) {
        heading.querySelectorAll(".about-reveal-line").forEach(line => {
            const fragment = document.createDocumentFragment();
            line.childNodes.forEach(node => {
                (node.textContent.match(/\S+|\s+/g) || []).forEach(part => {
                    if (/^\s+$/.test(part)) fragment.appendChild(document.createTextNode(part));
                    else {
                        const word = document.createElement("span");
                        word.className = "about-reveal-word" + (node.nodeType === Node.ELEMENT_NODE ? " is-accent" : "");
                        word.textContent = part;
                        fragment.appendChild(word);
                    }
                });
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
    timeline.fromTo(section.querySelectorAll(".about-reveal-link"), { autoAlpha: 0, y: 16 }, { autoAlpha: 1, y: 0, duration: .06, stagger: .05, ease: "power2.out" });
    timeline.to({}, { duration: .12 });
}

function initBongbongStory() {
    const section = document.querySelector(".bongbong-story-section");
    const stage = section?.querySelector(".bongbong-story-stage");
    const deck = section?.querySelector(".bongbong-story-cards");
    const cards = deck ? [...deck.querySelectorAll(".bongbong-story-card")] : [];
    if (!section || !stage || !deck || cards.length !== 4) return;
    const word = section.querySelector(".bongbong-story-word");
    const hello = section.querySelector(".bongbong-story-hello");
    const cat = section.querySelector(".bongbong-story-cat");
    const waves = section.querySelector(".bongbong-story-waves");
    const pops = [...section.querySelectorAll(".bongbong-story-pop")];
    const sparks = [...section.querySelectorAll(".bongbong-story-spark")];
    const mobile = () => window.innerWidth <= 970;
    const availableHeight = () => {
        const style = getComputedStyle(stage);
        return stage.clientHeight - Number.parseFloat(style.paddingTop) - Number.parseFloat(style.paddingBottom);
    };
    const overflow = () => Math.max(0, deck.offsetTop + deck.offsetHeight + 24 - (stage.clientHeight - Number.parseFloat(getComputedStyle(stage).paddingBottom)));
    const wordHeight = () => word.getBoundingClientRect().height;
    const wordCenter = () => stage.clientHeight / 2 - word.getBoundingClientRect().height / 2 - parseFloat(getComputedStyle(word).top);
    const hop = section.querySelector(".bongbong-story-cat-hop");
    // The cat's face sits at 41% of its image. It walks in close-up with its legs below the screen, then stands whole at the lower right.
    const catFace = .41;
    const catScale = () => mobile() ? .72 : .58;
    const catCloseX = () => window.innerWidth * (mobile() ? .3 : .17) - cat.offsetWidth * catFace;
    const catCloseY = () => mobile() ? 0 : cat.offsetHeight * .38;
    const catRestX = () => {
        const width = cat.offsetWidth * catScale();
        return Math.min(window.innerWidth * .68 - width * catFace, window.innerWidth - width);
    };
    const catRestY = () => -stage.clientHeight * .02;
    const burstX = () => catRestX() + cat.offsetWidth * catScale() * catFace;
    const burstY = () => stage.clientHeight + catRestY() - cat.offsetHeight * catScale() * .72;
    // One bouncy step: stretch going up, squash on landing, settle.
    const hopAt = (time, height, duration = .26) => {
        timeline.to(hop, { y: () => -cat.offsetHeight * height, scaleX: .95, scaleY: 1.06, duration: duration * .45, ease: "power2.out" }, time);
        timeline.to(hop, { y: 0, scaleX: 1.07, scaleY: .92, duration: duration * .4, ease: "power2.in" }, time + duration * .45);
        timeline.to(hop, { scaleX: 1, scaleY: 1, duration: duration * .15, ease: "sine.out" }, time + duration * .85);
    };
    const timeline = gsap.timeline({ scrollTrigger: {
        id: "bongbong-story",
        trigger: section,
        start: "top top",
        end: () => "+=" + Math.round(window.innerHeight * 3 + overflow()),
        pin: true,
        pinSpacing: true,
        scrub: .4,
        anticipatePin: 1,
        invalidateOnRefresh: true
    } });

    // HELLO BONGBONG, then the cat walks in from the left and pushes HELLO off screen.
    timeline.to({}, { duration: .15 });
    timeline.fromTo(word, { y: () => wordCenter() + wordHeight() * .55 }, { y: wordCenter, duration: .6, ease: "power2.inOut" }, .4);
    timeline.fromTo(hello, { autoAlpha: 1, x: 0, y: () => wordCenter() - wordHeight() * .55 }, { x: () => -window.innerWidth * 1.1, duration: .55, ease: "power2.in" }, .45);
    timeline.fromTo(cat, { autoAlpha: 1, x: () => -cat.offsetWidth * 1.05, y: catCloseY, scale: 1, rotation: 0 }, { x: catCloseX, duration: .85, ease: "sine.out" }, .15);
    [.15, .43, .71].forEach(time => hopAt(time, .05));

    // The cat bounces across to the right, tipping from foot to foot, and the products pop out on a big jump.
    timeline.to(cat, { x: catRestX, y: catRestY, scale: catScale, duration: .68, ease: "sine.inOut" }, 1.15);
    [1.15, 1.32, 1.49, 1.66].forEach((time, index) => {
        hopAt(time, .07, .17);
        timeline.to(cat, { rotation: index % 2 ? 4 : -4, duration: .085, ease: "sine.inOut" }, time);
        timeline.to(cat, { rotation: 0, duration: .085, ease: "sine.inOut" }, time + .085);
    });
    hopAt(1.85, .16, .4);
    timeline.fromTo(pops, {
        autoAlpha: 0,
        scale: 0,
        x: (index, pop) => burstX() - pop.offsetLeft - pop.offsetWidth / 2,
        y: (index, pop) => burstY() - pop.offsetTop - pop.offsetHeight / 2,
        rotation: index => [-60, 50, 40, -70][index]
    }, {
        autoAlpha: 1,
        scale: 1,
        x: 0,
        y: 0,
        rotation: index => [0, 0, 0, -14][index],
        duration: .35,
        stagger: .13,
        ease: "back.out(2.6)"
    }, 1.85);
    sparks.forEach((spark, index) => {
        const angle = index / sparks.length * Math.PI * 2;
        const start = 1.85 + index % 4 * .13;
        const reach = () => Math.min(window.innerWidth, window.innerHeight) * (.22 + index % 3 * .09);
        timeline.fromTo(spark, { autoAlpha: 1, scale: 0, x: burstX, y: burstY }, { scale: 1, x: () => burstX() + Math.cos(angle) * reach(), y: () => burstY() + Math.sin(angle) * reach(), duration: .35, ease: "power2.out" }, start);
        timeline.to(spark, { autoAlpha: 0, scale: .4, duration: .15, ease: "none" }, start + .3);
    });

    // The products leave, the cat dives and the water rises with the cards coming up through it.
    timeline.to(pops, { autoAlpha: 0, scale: .6, y: index => [-180, -180, 200, 200][index], duration: .35, ease: "power2.in" }, 2.95);
    // A little jump before the dive, goggles first, into the rising water.
    hopAt(2.8, .1, .2);
    timeline.to(cat, { y: () => cat.offsetHeight * catScale() * 1.05, rotation: 8, duration: .5, ease: "power1.in" }, 3);
    timeline.fromTo(waves, { yPercent: 100 }, { yPercent: 0, duration: .6, ease: "power2.out" }, 3.05);
    timeline.to(word, { y: 0, duration: .6, ease: "power2.inOut" }, 3.1);
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
    }, 4.1);
    timeline.fromTo(deck, { y: () => stage.clientHeight }, { y: 0, duration: .85, ease: "power2.out" }, 3.15);
    // 카드가 펼쳐지면 글자가 조각으로 보이므로 함께 사라지게 한다.
    timeline.to(word, { autoAlpha: 0, duration: .5, ease: "none" }, 4.55);
    // The water drops back so it never covers the card text.
    timeline.to(waves, { yPercent: 62, duration: .6, ease: "none" }, 4.3);
    timeline.fromTo(deck, { y: 0 }, { y: () => -overflow(), duration: .5, ease: "none", immediateRender: false }, 5.2);
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
        document.querySelectorAll("video").forEach(video => video.pause());
        introPlayed = true;
    } else {
        initCommon();
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
    
    initBackgroundVideos();
    if (document.querySelector(".main-page")) {
        initPillarVideo();
        initBrandGallery();
        rebuildMainMotion();
        let desktop = window.innerWidth > 970;
        let resizeFrame = 0;
        window.addEventListener("resize", function () {
            cancelAnimationFrame(resizeFrame);
            resizeFrame = requestAnimationFrame(function () {
                const nextDesktop = window.innerWidth > 970;
                if (nextDesktop !== desktop) {
                    desktop = nextDesktop;
                    rebuildMainMotion();
                }
            });
        }, { passive:true });
        reducedMotionQuery.addEventListener("change", rebuildMainMotion);
        document.fonts?.ready.then(() => { if (typeof ScrollTrigger !== "undefined") ScrollTrigger.refresh(); });
        window.addEventListener("load", () => { if (typeof ScrollTrigger !== "undefined") ScrollTrigger.refresh(); }, { once:true });
    }
    initLocalRouting();
    initSiteIcons();
});

// Right-click blocking, switched in /admin/settings. Form fields keep their menu so people can still paste.
if (document.querySelector('meta[name="context-menu"][content="blocked"]')) {
    document.documentElement.classList.add('no-context-menu');
    document.addEventListener('contextmenu', event => {
        if (!event.target.closest('input, textarea, select')) event.preventDefault();
    });
    document.addEventListener('dragstart', event => {
        if (event.target.closest('img, video')) event.preventDefault();
    });
}
