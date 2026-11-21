const bookingForm = document.querySelector('.space-booking-options');
const eventType = bookingForm?.querySelector('#event_type_id');

if (bookingForm && eventType) {
    const cards = bookingForm.querySelectorAll('[data-event-type-ids]');
    const status = bookingForm.querySelector('[data-package-status]');
    const noPackage = bookingForm.querySelector(
        'input[name="package_choice"][value=""]'
    );

    const loadMore = bookingForm.querySelector('[data-packages-more]');
    const previewLimit = 6;
    let expanded = false;

    function updatePackages() {
        const selectedType = eventType.value;
        let matchingCount = 0;

        for (const card of cards) {
            const radio = card.querySelector('input[name="package_choice"]');
            const eventTypeIds = card.dataset.eventTypeIds.split(' ');
            const matches = selectedType !== '' && eventTypeIds.includes(selectedType);
            if (matches) {
                matchingCount++;
            }

            const show = matches && (expanded || matchingCount <= previewLimit);
            card.hidden = !show;
            radio.disabled = !show;

            if (!show && radio.checked) {
                radio.checked = false;
                if (noPackage) noPackage.checked = true;
            }
        }

        if (loadMore) {
            loadMore.hidden = expanded || matchingCount <= previewLimit;
        }

        status.hidden = matchingCount > 0;
        status.textContent = selectedType
            ? 'No packages match this event type for this space.'
            : 'Choose an event type to see matching packages.';
    }

    eventType.addEventListener('change', () => {
        expanded = false;
        updatePackages();
    });

    loadMore?.addEventListener('click', () => {
        expanded = true;
        updatePackages();
        const firstNewCard = Array.from(cards).filter(card => !card.hidden)[previewLimit];
        firstNewCard?.querySelector('input')?.focus();
    });

    bookingForm.addEventListener('formdata', ({ formData }) => {
        const packageId = formData.get('package_choice');
        formData.delete('package_choice');

        if (packageId) {
            formData.set('package_id', packageId);
        }
    });

    updatePackages();
}
const reviewsDialog = document.querySelector('.space-reviews-dialog');
document.querySelector('[data-reviews-open]')?.addEventListener('click', () => {
    reviewsDialog?.showModal();
});
