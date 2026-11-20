const navDrawer = document.querySelector('#admin-nav-drawer');
const navToggle = document.querySelector('.admin-nav-toggle');
const sidebar = document.querySelector('.convene-admin-ui > .admin-sidebar');

if (navDrawer && navToggle && sidebar && typeof navDrawer.showModal === 'function') {
    const mobile = window.matchMedia('(max-width: 991px)');

    navToggle.hidden = false;
    document.body.classList.add('admin-nav-enhanced');

    navToggle.addEventListener('click', () => {
        navDrawer.append(sidebar);
        navDrawer.showModal();
    });

    navDrawer.querySelector('.admin-nav-close').addEventListener('click', () => navDrawer.close());
    navDrawer.addEventListener('click', (event) => {
        if (event.target === navDrawer) {
            const bounds = navDrawer.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right ||
                event.clientY < bounds.top || event.clientY > bounds.bottom) {
                navDrawer.close();
            }
        }
    });

    navDrawer.addEventListener('close', () => {
        document.body.insertBefore(sidebar, navDrawer);
        if (mobile.matches) navToggle.focus();
    });

    mobile.addEventListener('change', () => {
        if (!mobile.matches && navDrawer.open) {
            const focus = sidebar.querySelector('[aria-current="page"]') || sidebar.querySelector('a');
            navDrawer.close();
            document.body.insertBefore(sidebar, navDrawer);
            focus?.focus();
        }
    });
}
