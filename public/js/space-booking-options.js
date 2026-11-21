const bookingForm = document.querySelector('.space-booking-options');
const eventType = bookingForm?.querySelector('#event_type_id');

if (bookingForm && eventType) {
    const layouts = bookingForm.querySelectorAll('[data-layout-event-types]');
    const layoutOptions = bookingForm.querySelector('[data-layout-options]');
    const layoutStatus = bookingForm.querySelector('[data-layout-status]');
    const submit = document.querySelector('[form="space-booking-form"][type="submit"]');

    function updateLayouts() {
        const selectedType = eventType.value;
        let hasMatch = false;

        for (const layout of layouts) {
            const eventTypeIds = layout.dataset.layoutEventTypes.split(' ');
            const matches = selectedType !== '' && eventTypeIds.includes(selectedType);
            const radio = layout.querySelector('input');

            layout.hidden = !matches;
            radio.disabled = !matches;
            if (!matches) radio.checked = false;
            hasMatch ||= matches;
        }

        if (submit) submit.disabled = !hasMatch;
        if (layoutOptions) layoutOptions.hidden = !hasMatch;
        if (layoutStatus) {
            layoutStatus.hidden = hasMatch;
            layoutStatus.textContent = selectedType
                ? 'No layouts match this event type for this space.'
                : 'Choose an event type to see matching layouts.';
        }
    }

    const cards = bookingForm.querySelectorAll('[data-event-type-ids]');
    const status = bookingForm.querySelector('[data-package-status]');
    const noPackage = bookingForm.querySelector(
        'input[name="package_choice"][value=""]'
    );

    const pagination = bookingForm.querySelector('[data-package-pagination]');
    const packagesPerPage = 4;
    let currentPage = 1;

    function updatePackages() {
        const selectedType = eventType.value;
        let matchingCount = 0;
        const firstIndex = (currentPage - 1) * packagesPerPage;
        const lastIndex = firstIndex + packagesPerPage;

        for (const card of cards) {
            const radio = card.querySelector('input[name="package_choice"]');
            const matches = selectedType !== '' && card.dataset.eventTypeIds.split(' ').includes(selectedType);
            card.hidden = !matches || matchingCount < firstIndex || matchingCount >= lastIndex;
            radio.disabled = !matches;
            if (matches) matchingCount++;

            if (!matches && radio.checked) {
                radio.checked = false;
                if (noPackage) noPackage.checked = true;
            }
        }

        if (pagination) {
            const pageCount = Math.ceil(matchingCount / packagesPerPage);
            pagination.replaceChildren();
            pagination.hidden = pageCount <= 1;

            for (let page = 1; page <= pageCount && pageCount > 1; page++) {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.page = String(page);
                button.textContent = String(page);
                button.setAttribute('aria-label', 'Package page ' + page);
                if (page === currentPage) button.setAttribute('aria-current', 'page');
                pagination.append(button);
            }
        }

        status.hidden = matchingCount > 0;
        status.textContent = selectedType
            ? 'No packages match this event type for this space.'
            : 'Choose an event type to see matching packages.';
    }

    eventType.addEventListener('change', () => {
        currentPage = 1;
        updateLayouts();
        updatePackages();
    });

    pagination?.addEventListener('click', ({ target }) => {
        const button = target.closest('button[data-page]');
        if (!button) return;
        currentPage = Number(button.dataset.page);
        updatePackages();
        pagination.querySelector('[aria-current="page"]')?.focus();
    });

    bookingForm.addEventListener('formdata', ({ formData }) => {
        const packageId = formData.get('package_choice');
        formData.delete('package_choice');

        if (packageId) {
            formData.set('package_id', packageId);
        }
    });

    updateLayouts();
    updatePackages();
}
const reviewsDialog = document.querySelector('.space-reviews-dialog');
document.querySelector('[data-reviews-open]')?.addEventListener('click', () => {
    reviewsDialog?.showModal();
});

bookingForm?.addEventListener('click', ({ target }) => {
    const open = target.closest('[data-package-open]');
    const close = target.closest('[data-package-close]');
    if (open) open.closest('.package-option').querySelector('dialog').showModal();
    if (close) close.closest('dialog').close();
});
