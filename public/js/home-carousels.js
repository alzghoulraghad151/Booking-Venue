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
    const cards = [...arrivalsGroup.querySelectorAll('.home-space-card')];
    const duplicate = arrivalsGroup.cloneNode(true);
    duplicate.removeAttribute('data-arrivals-group');
    duplicate.setAttribute('aria-hidden', 'true');
    duplicate.inert = true;
    duplicate.querySelectorAll('a').forEach((link) => link.setAttribute('tabindex', '-1'));
    arrivalsTrack.append(duplicate);

    const sizeCards = () => {
        const visible = innerWidth <= 640 ? 1 : innerWidth <= 900 ? 2 : 3;
        const gap = Number.parseFloat(getComputedStyle(arrivalsGroup).gap) || 24;
        const sectionWidth = arrivals.clientWidth;
        const width = (sectionWidth - gap * (visible - 1)) / visible;
        cards.forEach((card) => { card.style.flexBasis = `${width}px`; });
        duplicate.querySelectorAll('.home-space-card').forEach((card) => { card.style.flexBasis = `${width}px`; });
    };

    if (cards.length > 1) arrivalsTrack.classList.add('is-moving');
    else arrivalsTrack.classList.remove('is-moving');
    addEventListener('resize', sizeCards);
    sizeCards();
}