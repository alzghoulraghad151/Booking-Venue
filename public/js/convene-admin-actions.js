const adminContent = document.querySelector('#admin-content');

adminContent?.addEventListener('submit', (event) => {
    const form = event.target;
    const button = event.submitter;
    if (event.defaultPrevented || form.method !== 'post' || !button?.matches('button.admin-button')) {
        return;
    }

    if (form.querySelector('button[aria-busy="true"]')) {
        event.preventDefault();
        return;
    }

    // Keep the submitter enabled so its name and value are still submitted.
    button.setAttribute('aria-busy', 'true');
    button.setAttribute('aria-disabled', 'true');
});

window.addEventListener('pageshow', (event) => {
    if (!event.persisted) return;

    adminContent?.querySelectorAll('button.admin-button[aria-busy="true"]').forEach((button) => {
        button.removeAttribute('aria-busy');
        button.removeAttribute('aria-disabled');
    });
});
