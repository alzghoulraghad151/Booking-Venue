const siteHeader = document.querySelector('[data-site-header]');
const siteMenuToggle = siteHeader?.querySelector('[data-site-menu-toggle]');

if (siteMenuToggle) {
    const setExpanded = (expanded) => siteMenuToggle.setAttribute('aria-expanded', String(expanded));

    siteMenuToggle.hidden = false;
    siteMenuToggle.addEventListener('click', () => {
        setExpanded(siteMenuToggle.getAttribute('aria-expanded') === 'false');
    });

    siteHeader.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && siteMenuToggle.getAttribute('aria-expanded') === 'true') {
            setExpanded(false);
            siteMenuToggle.focus();
        }
    });
}
