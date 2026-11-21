(() => {
    const input = document.getElementById('landing-address');
    const source = input?.list;
    const menu = document.getElementById('landing-address-menu');
    if (!source || !menu) return;

    const list = menu.querySelector('[role="listbox"]');
    const empty = menu.querySelector('[role="status"]');
    const addresses = [...new Set(Array.from(source.options, option => option.value).filter(Boolean))];
    const options = addresses.map((address, index) => {
        const item = document.createElement('li');
        item.id = 'landing-address-option-' + index;
        item.setAttribute('role', 'option');
        item.setAttribute('aria-selected', 'false');
        item.textContent = address;
        return item;
    });
    list.replaceChildren(...options);

    let visible = [];
    let active = -1;

    function selectActive(index) {
        visible[active]?.setAttribute('aria-selected', 'false');
        active = index;
        const item = visible[index];
        if (!item) {
            input.removeAttribute('aria-activedescendant');
            return;
        }
        item.setAttribute('aria-selected', 'true');
        input.setAttribute('aria-activedescendant', item.id);
        item.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    }

    function open() {
        selectActive(-1);
        const term = input.value.trim().toLocaleLowerCase();
        visible = options.filter(item => {
            const matches = item.textContent.toLocaleLowerCase().includes(term);
            item.hidden = !matches;
            return matches;
        });
        empty.hidden = visible.length > 0;
        empty.textContent = visible.length ? '' : 'No matching addresses. You can search using what you typed.';
        menu.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function close() {
        selectActive(-1);
        menu.hidden = true;
        input.setAttribute('aria-expanded', 'false');
    }

    function choose(item) {
        input.value = item.textContent;
        close();
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    input.removeAttribute('list');
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-controls', list.id);
    input.setAttribute('aria-expanded', 'false');
    input.classList.add('home-address__input--enhanced');
    input.addEventListener('focus', open);
    input.addEventListener('click', () => { if (menu.hidden) open(); });
    input.addEventListener('input', open);
    input.addEventListener('blur', close);
    input.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !menu.hidden) {
            event.preventDefault();
            close();
        } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (menu.hidden) open();
            if (!visible.length) return;
            const step = event.key === 'ArrowDown' ? 1 : -1;
            const next = active < 0 && step < 0 ? visible.length - 1 : active + step;
            selectActive(Math.max(0, Math.min(visible.length - 1, next)));
        } else if (event.key === 'Enter' && !menu.hidden && active >= 0) {
            event.preventDefault();
            choose(visible[active]);
        }
    });

    list.addEventListener('mousedown', event => {
        if (event.target.closest('[role="option"]')) event.preventDefault();
    });
    list.addEventListener('click', event => {
        const item = event.target.closest('[role="option"]');
        if (item) choose(item);
    });
})();
