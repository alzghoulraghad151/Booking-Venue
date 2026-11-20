const imageInput = document.getElementById('images');
const previewGallery = document.getElementById('venue-image-previews');

if (imageInput && previewGallery) {
    let previewUrls = [];
    const allowedTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);

    imageInput.addEventListener('change', () => {
        previewUrls.forEach((url) => URL.revokeObjectURL(url));
        previewUrls = [];
        previewGallery.replaceChildren();

        const files = Array.from(imageInput.files);

const maxFiles = Number(imageInput.dataset.maxFiles);
const tooManyFiles = files.length > maxFiles;

imageInput.setCustomValidity(
    tooManyFiles ? `You can add up to ${maxFiles} more image(s).` : ''
);

if (tooManyFiles) {
    imageInput.reportValidity();
    return;
}



for (const file of files)  {
            if (!allowedTypes.has(file.type) || file.size > 2 * 1024 * 1024) {
                continue;
            }

            const previewUrl = URL.createObjectURL(file);
            previewUrls.push(previewUrl);

            const image = document.createElement('img');
            image.src = previewUrl;
            image.alt = `Preview of ${file.name}`;

            previewGallery.append(image);
        }
    });
}