// /admin/slides: drag to reorder and add or delete slides without a page reload.
function initSlideBoard() {
    const board = document.querySelector("#slide-board");
    const drop = document.querySelector("[data-slide-drop]");
    if (!board || !drop) return;
    const status = document.querySelector("#slide-status");
    const countLabel = document.querySelector("#slide-count");
    const token = drop.querySelector("input[name=token]").value;
    const input = drop.querySelector("input[type=file]");
    let statusTimer = 0;

    function say(message, isError = false) {
        clearTimeout(statusTimer);
        status.textContent = message;
        status.hidden = false;
        status.classList.toggle("is-error", isError);
        if (!isError) statusTimer = setTimeout(() => { status.hidden = true; }, 2500);
    }

    async function send(body) {
        let response;
        try {
            response = await fetch("/admin/slides", { method:"POST", body, headers:{ "X-Requested-With":"fetch" }, credentials:"same-origin" });
        } catch {
            throw new Error("연결에 실패했습니다. 다시 시도해 주세요.");
        }
        const data = await response.json().catch(() => ({ error:response.status === 413 ? "이미지가 너무 큽니다. 3MB 이하로 줄여서 올려 주세요." : "저장하지 못했습니다. 다시 시도해 주세요." }));
        if (data.error) throw new Error(data.error);
        return data;
    }

    function renumber() {
        const cards = [...board.children];
        cards.forEach((card, index) => {
            card.querySelector(".admin-slide-number").textContent = index + 1;
            card.querySelector(".admin-slide-handle").setAttribute("aria-label", (index + 1) + "번 슬라이드, 끌어서 순서 변경");
            card.querySelector("[data-move=up]").disabled = index === 0;
            card.querySelector("[data-move=down]").disabled = index === cards.length - 1;
        });
        countLabel.textContent = cards.length;
    }

    let saving = Promise.resolve();
    function saveOrder() {
        const body = new FormData();
        body.set("token", token);
        body.set("op", "order");
        body.set("ids", [...board.children].map(card => card.dataset.slideId).join(","));
        // Queued so a quick second drag can't be overtaken by the first.
        saving = saving.then(() => send(body)).then(() => say("순서를 저장했습니다.")).catch(error => say(error.message, true));
        return saving;
    }

    Sortable.create(board, {
        animation:150,
        forceFallback:true,
        fallbackOnBody:true,
        handle:".admin-slide-handle, .admin-slide-frame",
        ghostClass:"is-ghost",
        dragClass:"is-dragging",
        delayOnTouchOnly:true,
        delay:120,
        onEnd(event) {
            if (event.oldIndex === event.newIndex) return;
            renumber();
            saveOrder();
        }
    });

    board.addEventListener("submit", async event => {
        const form = event.target.closest("[data-slide-form]");
        if (!form) return;
        event.preventDefault();
        const op = form.querySelector("input[name=op]").value;
        if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) return;
        const card = form.closest("[data-slide-id]");
        if (op === "up" && card.previousElementSibling) {
            board.insertBefore(card, card.previousElementSibling);
            renumber();
            form.querySelector("button")?.focus();
            saveOrder();
            return;
        }
        if (op === "down" && card.nextElementSibling) {
            board.insertBefore(card.nextElementSibling, card);
            renumber();
            form.querySelector("button")?.focus();
            saveOrder();
            return;
        }
        try {
            await send(new FormData(form));
            if (op === "delete") {
                card.remove();
                renumber();
                say("삭제했습니다.");
            } else if (op === "alt") {
                form.dataset.saved = form.elements.alt.value;
                say("이미지 설명을 저장했습니다.");
            }
        } catch (error) {
            say(error.message, true);
        }
    });

    board.addEventListener("focusin", event => {
        const form = event.target.closest("[data-slide-alt]");
        if (form && form.dataset.saved === undefined) form.dataset.saved = form.elements.alt.value;
    });
    board.addEventListener("focusout", event => {
        const form = event.target.closest("[data-slide-alt]");
        if (form && form.elements.alt.value !== form.dataset.saved) form.requestSubmit();
    });

    async function addFiles(files) {
        const list = [...files];
        for (let index = 0; index < list.length; index++) {
            say(list.length > 1 ? `올리는 중... (${index + 1}/${list.length})` : "올리는 중...");
            const body = new FormData();
            body.set("token", token);
            body.set("op", "upload");
            try {
                const image = await shrinkImage(list[index]).catch(() => list[index]);
                body.set("image", image);
                const data = await send(body);
                board.insertAdjacentHTML("beforeend", data.html);
                renumber();
            } catch (error) {
                say(list[index].name + ": " + error.message, true);
                return;
            }
        }
        say(list.length > 1 ? list.length + "장을 추가했습니다." : "추가했습니다.");
    }

    input.addEventListener("change", () => {
        if (input.files.length) addFiles(input.files);
        input.value = "";
    });
    ["dragenter", "dragover"].forEach(type => drop.addEventListener(type, event => {
        event.preventDefault();
        drop.classList.add("is-over");
    }));
    ["dragleave", "drop"].forEach(type => drop.addEventListener(type, () => drop.classList.remove("is-over")));
    drop.addEventListener("drop", event => {
        event.preventDefault();
        if (event.dataTransfer.files.length) addFiles(event.dataTransfer.files);
    });
    drop.addEventListener("submit", event => event.preventDefault());
}

document.addEventListener("DOMContentLoaded", initSlideBoard);
