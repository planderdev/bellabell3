// /admin/gallery: uploads one or many photos, shrinking each in the browser first, then reloads the list.
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("[data-gallery-upload]");
    if (!form) return;
    const input = form.querySelector("input[type=file]");
    const status = document.querySelector("#ingredient-status");
    const token = form.querySelector("input[name=token]").value;

    function say(message, isError = false) {
        status.textContent = message;
        status.hidden = false;
        status.classList.toggle("is-error", isError);
    }

    async function upload(files) {
        const list = [...files];
        for (const [index, file] of list.entries()) {
            say(`올리는 중... (${index + 1}/${list.length})`);
            const body = new FormData();
            body.set("token", token);
            body.set("op", "upload");
            let image = file;
            try { image = await shrinkImage(file); } catch { /* The server answers with its own message. */ }
            body.set("image", image);
            let error = null;
            try {
                const response = await fetch("/admin/gallery", { method:"POST", body, headers:{ "X-Requested-With":"fetch" }, credentials:"same-origin" });
                error = (await response.json()).error;
            } catch {
                error = "올리지 못했습니다. 다시 시도해 주세요.";
            }
            if (error) {
                say(`${file.name}: ${error}`, true);
                return;
            }
        }
        location.reload();
    }

    input.addEventListener("change", () => { if (input.files.length) upload(input.files); });
    ["dragover", "drop"].forEach(type => form.addEventListener(type, event => event.preventDefault()));
    form.addEventListener("drop", event => { if (event.dataTransfer.files.length) upload(event.dataTransfer.files); });
});
