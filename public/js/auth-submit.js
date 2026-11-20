const submitButton = document.querySelector('[data-pending-label]');

if (submitButton?.form) {
    const form = submitButton.form;
    const originalLabel = submitButton.textContent;

    form.addEventListener('submit', (event) => {
        if (submitButton.disabled) {
            event.preventDefault();
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = submitButton.dataset.pendingLabel;
    });

    window.addEventListener('pageshow', () => {
        submitButton.disabled = false;
        submitButton.textContent = originalLabel;
    });
}