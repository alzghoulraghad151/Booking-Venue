document.querySelectorAll('.place-card__media[data-card-images]').forEach((media) => {
    let images;
    try {
        images = JSON.parse(media.dataset.cardImages || '[]');
    } catch {
        images = [];
    }

    if (images.length < 2) return;

    const image = media.querySelector('.place-card__image:not(.place-card__image--empty)');
    if (!image) return;

    let currentIndex = Math.max(0, images.indexOf(image.getAttribute('src')));

    media.querySelector('.place-card__image-arrow--previous')?.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        image.src = images[currentIndex];
    });

    media.querySelector('.place-card__image-arrow--next')?.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % images.length;
        image.src = images[currentIndex];
    });
});