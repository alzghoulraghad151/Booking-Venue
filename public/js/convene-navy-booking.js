/* Convene · Navy booking flow on the space page.
   Adds the live summary bar, step states and the mobile full-screen sheet.
   The form itself (fields, names, layout/package filtering) is still driven by
   space-booking-options.js, so the request sent to bookings.create is unchanged. */
(() => {
    const section = document.querySelector('.nvb');
    const form = document.getElementById('space-booking-form');
    if (!section || !form) return;

    const rate = parseFloat(section.dataset.rate || '');
    const eventType = form.querySelector('#event_type_id');
    const date = form.querySelector('#nvb-date');
    const start = form.querySelector('#nvb-start');
    const end = form.querySelector('#nvb-end');
    const picks = form.querySelector('[data-nvb-picks]');
    const total = form.querySelector('[data-nvb-total]');
    const totalLabel = form.querySelector('[data-nvb-total-label]');
    const steps = Object.fromEntries([...form.querySelectorAll('[data-nvb-step]')].map((s) => [s.dataset.nvbStep, s]));
    const money = (n) => `${n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} JOD`;

    function hours() {
        if (!date?.value || !start?.value || !end?.value) return null;
        const [sh, sm] = start.value.split(':').map(Number);
        const [eh, em] = end.value.split(':').map(Number);
        const h = (eh * 60 + em - (sh * 60 + sm)) / 60;
        return h > 0 ? h : null;
    }

    function update() {
        const typeText = eventType?.value ? eventType.selectedOptions[0].textContent.trim() : '';
        const layoutInput = form.querySelector('input[name="space_layout_id"]:checked');
        const layoutText = layoutInput ? layoutInput.nextElementSibling?.dataset.nvbLabel : '';
        const pkgInput = form.querySelector('input[name="package_choice"]:checked');
        const pkgCard = pkgInput?.value ? pkgInput.closest('[data-price]') : null;
        const pkgPrice = pkgCard ? parseFloat(pkgCard.dataset.price) : 0;
        const h = hours();

        const parts = [typeText, layoutText, pkgCard ? pkgCard.dataset.name : ''].filter(Boolean);
        if (h && date.value) parts.push(`${date.value} · ${start.value}–${end.value}`);
        picks.textContent = parts.length ? parts.join(' · ') : 'Choose an event type and a layout';

        if (h && !Number.isNaN(rate)) {
            // Same rule as BookingPriceCalculator: a package includes the first hour of the space.
            const billable = pkgCard ? Math.max(0, h - 1) : h;
            totalLabel.textContent = 'Estimated total';
            total.textContent = money(rate * billable + pkgPrice);
        } else if (pkgCard) {
            totalLabel.textContent = 'Package';
            total.textContent = Number.isNaN(rate) ? money(pkgPrice) : `${money(pkgPrice)} + ${money(rate)} / hour after the 1st hour`;
        } else {
            totalLabel.textContent = 'Price';
            total.textContent = Number.isNaN(rate) ? '—' : `${money(rate)} / hour`;
        }

        steps.type?.classList.toggle('is-done', !!eventType?.value);
        steps.layout?.classList.toggle('is-done', !!layoutInput);
        steps.package?.classList.toggle('is-done', !!eventType?.value && !!pkgInput);
        steps.when?.classList.toggle('is-done', !!h);
    }

    form.addEventListener('change', update);
    form.addEventListener('input', update);
    // space-booking-options.js reacts to the same change events; read the result afterwards.
    eventType?.addEventListener('change', () => setTimeout(update, 0));
    update();

    /* Mobile: the flow opens as a full-screen sheet */
    const mobile = matchMedia('(max-width: 760px)');
    const open = (e) => {
        if (!mobile.matches) return;
        e?.preventDefault();
        section.classList.add('is-open');
        document.documentElement.classList.add('nvb-lock');
        form.querySelector('.nvb-close')?.focus();
    };
    const close = () => {
        section.classList.remove('is-open');
        document.documentElement.classList.remove('nvb-lock');
    };
    document.querySelectorAll('[data-nvb-open]').forEach((b) => b.addEventListener('click', open));
    section.querySelectorAll('[data-nvb-close]').forEach((b) => b.addEventListener('click', close));
    addEventListener('keydown', (e) => { if (e.key === 'Escape' && section.classList.contains('is-open') && !document.querySelector('dialog[open]')) close(); });
    mobile.addEventListener?.('change', () => { if (!mobile.matches) close(); });
    if (form.querySelector('[role="alert"]') && mobile.matches) open();
})();
