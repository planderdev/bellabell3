// /admin/slides: shrink images in the browser so uploads stay under the server's limits, and confirm deletes.
const MAX_IMAGE_SIDE = 1800;
const KEEP_UNDER_BYTES = 1500000;

async function shrinkImage(file) {
    const bitmap = await createImageBitmap(file);
    const scale = Math.min(1, MAX_IMAGE_SIDE / Math.max(bitmap.width, bitmap.height));
    if (scale === 1 && file.size <= KEEP_UNDER_BYTES && ["image/jpeg", "image/png", "image/webp"].includes(file.type)) return file;
    const canvas = document.createElement("canvas");
    canvas.width = Math.round(bitmap.width * scale);
    canvas.height = Math.round(bitmap.height * scale);
    const context = canvas.getContext("2d");
    context.fillStyle = "#fff";
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    const blob = await new Promise(resolve => canvas.toBlob(resolve, "image/jpeg", 0.86));
    return new File([blob], file.name.replace(/\.[^.]+$/, "") + ".jpg", { type:"image/jpeg" });
}

function initSlideUpload() {
    const form = document.querySelector("[data-slide-upload]");
    if (!form) return;
    const input = form.querySelector("input[type=file]");
    const button = form.querySelector("button[type=submit]");
    let shrunk = false;
    form.addEventListener("submit", async event => {
        if (shrunk || !input.files.length) return;
        event.preventDefault();
        button.disabled = true;
        button.textContent = button.dataset.busyLabel;
        try {
            const transfer = new DataTransfer();
            transfer.items.add(await shrinkImage(input.files[0]));
            input.files = transfer.files;
        } catch {
            // The browser cannot decode it; let the server answer with its own message.
        }
        shrunk = true;
        form.submit();
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initSlideUpload();
    document.querySelectorAll("[data-confirm]").forEach(form => {
        form.addEventListener("submit", event => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
});
