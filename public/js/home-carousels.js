document.querySelectorAll('[data-home-carousel]').forEach((carousel) => {
    const viewport = carousel.querySelector('[data-carousel-viewport]');
    const track = carousel.querySelector('[data-carousel-track]');
    const previous = carousel.querySelector('[data-carousel-previous]');
    const next = carousel.querySelector('[data-carousel-next]');
    if (!viewport || !track) return;

    const items = [...track.children].filter((item) => item.matches('.home-carousel__item'));
    const gap = () => Number.parseFloat(getComputedStyle(track).gap) || 24;
    let itemWidth = 0;

    const update = () => {
        const visible = innerWidth <= 640 ? 1 : innerWidth <= 900 ? 2 : 3;
        itemWidth = items.length ? (viewport.clientWidth - gap() * (visible - 1)) / visible : 0;
        items.forEach((item) => { item.style.flexBasis = `${Math.max(0, itemWidth)}px`; });
        const max = viewport.scrollWidth - viewport.clientWidth;
        if (previous) previous.disabled = items.length <= visible || viewport.scrollLeft <= 2;
        if (next) next.disabled = items.length <= visible || viewport.scrollLeft >= max - 2;
    };

    previous?.addEventListener('click', () => viewport.scrollBy({ left: -(itemWidth + gap()), behavior: 'smooth' }));
    next?.addEventListener('click', () => viewport.scrollBy({ left: itemWidth + gap(), behavior: 'smooth' }));
    viewport.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
    addEventListener('resize', update);
    update();
});

const arrivals = document.querySelector('[data-home-arrivals]');
const arrivalsTrack = arrivals?.querySelector('[data-arrivals-track]');
const arrivalsGroup = arrivals?.querySelector('[data-arrivals-group]');
if (arrivalsTrack && arrivalsGroup) {
    const sourceCards = [...arrivalsGroup.querySelectorAll('.home-space-card')];
    let duplicate = null;

    const sizeCards = () => {
        if (!sourceCards.length) return;

        duplicate?.remove();
        duplicate = null;
        arrivalsGroup.replaceChildren(...sourceCards);

        const visible = innerWidth <= 640 ? 1 : innerWidth <= 900 ? 2 : 3;
        const gap = Number.parseFloat(getComputedStyle(arrivalsGroup).gap) || 24;
        const sectionWidth = arrivals.querySelector('.home-arrivals__viewport')?.clientWidth || arrivals.clientWidth;
        const width = (sectionWidth - gap * (visible - 1)) / visible;
        if (!Number.isFinite(width) || width <= 0) return;

        sourceCards.forEach((card) => { card.style.flexBasis = `${width}px`; });

        if (sourceCards.length > 1) {
            let index = 0;
            while (arrivalsGroup.scrollWidth < sectionWidth) {
                const repeatedCard = sourceCards[index % sourceCards.length].cloneNode(true);
                repeatedCard.setAttribute('aria-hidden', 'true');
                repeatedCard.inert = true;
                repeatedCard.querySelectorAll('a').forEach((link) => link.setAttribute('tabindex', '-1'));
                repeatedCard.style.flexBasis = `${width}px`;
                arrivalsGroup.append(repeatedCard);
                index += 1;
            }

            duplicate = arrivalsGroup.cloneNode(true);
            duplicate.removeAttribute('data-arrivals-group');
            duplicate.setAttribute('aria-hidden', 'true');
            duplicate.inert = true;
            duplicate.querySelectorAll('a').forEach((link) => link.setAttribute('tabindex', '-1'));
            arrivalsTrack.append(duplicate);

            const cycleWidth = arrivalsGroup.getBoundingClientRect().width;
            arrivalsTrack.style.setProperty('--home-arrivals-shift', `${-cycleWidth}px`);
            arrivalsTrack.style.setProperty('--home-arrivals-duration', `${cycleWidth / 18}s`);
        }

        arrivalsTrack.classList.toggle('is-moving', sourceCards.length > 1);
    };

    addEventListener('resize', sizeCards);
    sizeCards();
}
