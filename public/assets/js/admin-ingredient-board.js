// /admin/ingredients: drag to reorder without a page reload.
document.addEventListener("DOMContentLoaded", () => {
    const page = document.querySelector("[data-ingredient-board]");
    const list = document.querySelector("#ingredient-list");
    if (!page || !list) return;
    const status = document.querySelector("#ingredient-status");

    function say(message, isError = false) {
        status.textContent = message;
        status.hidden = false;
        status.classList.toggle("is-error", isError);
    }

    Sortable.create(list, {
        animation:150,
        forceFallback:true,
        handle:".admin-slide-handle",
        ghostClass:"is-ghost",
        dragClass:"is-dragging",
        async onEnd(event) {
            if (event.oldIndex === event.newIndex) return;
            [...list.children].forEach((item, index) => {
                item.querySelector(".admin-slide-number").textContent = String(index + 1).padStart(2, "0");
            });
            const body = new FormData();
            body.set("token", page.dataset.token);
            body.set("op", "order");
            body.set("ids", [...list.children].map(item => item.dataset.ingredientId).join(","));
            try {
                const response = await fetch(page.dataset.action || "/admin/ingredients", { method:"POST", body, headers:{ "X-Requested-With":"fetch" }, credentials:"same-origin" });
                const data = await response.json();
                if (data.error) throw new Error(data.error);
                say("순서를 저장했습니다.");
            } catch (error) {
                say(error.message || "저장하지 못했습니다. 다시 시도해 주세요.", true);
            }
        }
    });
});
