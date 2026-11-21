(() => {
    const edit = document.querySelector('#management-edit-dialog');
    const deletion = document.querySelector('#management-delete-dialog');
    if (typeof edit?.showModal !== 'function' || !deletion) return;

    const editBody = edit.querySelector('[data-edit-body]');
    const editTitle = edit.querySelector('h2');
    const content = document.querySelector('#admin-content');
    let editTrigger;
    let editRequest;
    let originalUrl;
    let directReturnUrl;
    let pendingDelete;
    let confirmedForm;

    for (const dialog of [edit, deletion]) {
        dialog.querySelectorAll('[data-dialog-close]').forEach((button) => {
            button.addEventListener('click', () => dialog.close());
        });
    }

    function renderEdit(page) {
        editTitle.textContent = page.dataset.title;
        editBody.replaceChildren(page);
        editBody.setAttribute('aria-busy', 'false');
        page.querySelector('[aria-invalid="true"]')?.focus();
    }

    const directPage = content.querySelector('[data-edit-page]');
    if (directPage) {
        directReturnUrl = directPage.dataset.returnUrl;
        renderEdit(directPage);
        edit.showModal();
        directPage.querySelector('[aria-invalid="true"]')?.focus();
    }

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('a[data-edit-dialog]');
        if (!link || event.defaultPrevented || event.button !== 0 ||
            event.ctrlKey || event.metaKey || event.shiftKey || event.altKey ||
            link.origin !== location.origin) return;

        event.preventDefault();
        if (edit.open) return;

        editTrigger = link;
        const drawer = link.closest('#admin-nav-drawer');
        if (drawer?.open) drawer.close();

        const request = new AbortController();
        editRequest = request;
        editTitle.textContent = 'Edit';
        editBody.setAttribute('aria-busy', 'true');
        const status = document.createElement('p');
        status.setAttribute('role', 'status');
        status.textContent = 'Loading…';
        editBody.replaceChildren(status);
        edit.showModal();

        try {
            const response = await fetch(link.href, { signal: request.signal });
            if (!response.ok || response.redirected) throw new Error('Edit page unavailable');
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            const page = html.querySelector('[data-edit-page]');
            if (!page) throw new Error('Missing edit form');
            if (request.signal.aborted || !edit.open) return;

            // Keep normal Laravel form submission and validation redirects on the edit URL.
            originalUrl = location.href;
            history.replaceState(history.state, '', link.href);
            renderEdit(document.adoptNode(page));

            const preview = page.querySelector('script[data-venue-preview]');
            if (preview) {
                const script = document.createElement('script');
                script.src = preview.src;
                preview.replaceWith(script);
            }
        } catch {
            if (!request.signal.aborted) location.assign(link.href);
        }
    });

    edit.addEventListener('close', () => {
        editRequest?.abort();
        if (directReturnUrl) {
            location.assign(directReturnUrl);
            return;
        }
        if (originalUrl) history.replaceState(history.state, '', originalUrl);
        originalUrl = undefined;
        editBody.replaceChildren();
        const fallback = document.querySelector('.admin-nav-toggle:not([hidden])');
        if (editTrigger?.getClientRects().length) editTrigger.focus();
        else fallback?.focus();
    });

    content.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.matches('form[data-delete-message]') || form === confirmedForm) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        pendingDelete = { form, button: event.submitter };
        const action = form.dataset.deleteAction || 'Delete';
        deletion.querySelector('h2').textContent = action === 'Delete' ? 'Confirm deletion' : action + '?';
        deletion.querySelector('[data-dialog-confirm]').textContent = action;
        deletion.querySelector('#management-delete-message').textContent = form.dataset.deleteMessage;
        deletion.showModal();
    }, true);

    deletion.addEventListener('close', () => {
        const button = pendingDelete?.button;
        pendingDelete = undefined;
        button?.focus();
    });

    deletion.querySelector('[data-dialog-confirm]').addEventListener('click', () => {
        if (!pendingDelete) return;
        const { form, button } = pendingDelete;
        confirmedForm = form;
        // The inline confirmation remains available when JavaScript enhancement is unavailable.
        const nativeConfirmation = form.onsubmit;
        form.onsubmit = null;
        deletion.close();
        try {
            form.requestSubmit(button || undefined);
        } finally {
            form.onsubmit = nativeConfirmation;
            confirmedForm = undefined;
        }
    });
})();
