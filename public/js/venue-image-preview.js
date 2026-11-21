(() => {
    const editor = document.querySelector('[data-venue-photos]');
    if (!editor || editor.dataset.ready) return;
    editor.dataset.ready = 'true';

    const picker = editor.querySelector('#venue-photos');
    const gallery = editor.querySelector('[data-photo-cards]');
    const status = editor.querySelector('[data-photo-status]');
    const photos = Array.from(gallery.querySelectorAll('[data-saved-photo]'), (card) => ({
        id: card.dataset.savedPhoto,
        url: card.querySelector('img').src,
        primary: card.querySelector('input').checked,
    }));

    let primary = photos.find((photo) => photo.primary) || photos[0];
    const formDataFiles = () => {
        const files = new DataTransfer();
        photos.filter((photo) => photo.file).forEach((photo) => files.items.add(photo.file));
        picker.files = files.files;
    };

    function notify(message) {
        status.textContent = message;
        status.hidden = !message;
    }

    function valid(file) {
        return ['image/jpeg', 'image/png', 'image/webp'].includes(file.type) &&
            file.size <= 2 * 1024 * 1024;
    }

    function remove(photo) {
        if (photo.id) {
            const marker = document.createElement('input');
            marker.type = 'hidden';
            marker.name = 'removed_images[]';
            marker.value = photo.id;
            editor.append(marker);
        } else {
            URL.revokeObjectURL(photo.url);
        }
        photos.splice(photos.indexOf(photo), 1);
        if (primary === photo) primary = photos[0];
    }

    function button(label, icon, action) {
        const control = document.createElement('button');
        control.type = 'button';
        control.title = label;
        control.setAttribute('aria-label', label);
        control.textContent = icon;
        control.addEventListener('click', action);
        return control;
    }

    function render() {
        formDataFiles();
        let newIndex = 0;
        gallery.replaceChildren(...photos.map((photo, index) => {
            const card = document.createElement('figure');
            const image = document.createElement('img');
            image.src = photo.url;
            image.alt = 'Venue photo ' + (index + 1);
            const actions = document.createElement('div');
            actions.className = 'venue-photo-actions';
            actions.append(button('Replace ' + image.alt, '✎', () => {
                const replacement = document.createElement('input');
                replacement.type = 'file';
                replacement.accept = picker.accept;
                replacement.addEventListener('change', () => {
                    const file = replacement.files[0];
                    if (!file) return;
                    if (!valid(file)) {
                        notify('Choose a JPEG, PNG or WebP photo up to 2 MB.');
                        return;
                    }
                    const wasPrimary = primary === photo;
                    remove(photo);
                    const next = { file, url: URL.createObjectURL(file) };
                    photos.splice(index, 0, next);
                    if (wasPrimary || !primary) primary = next;
                    notify('');
                    render();
                });
                replacement.click();
            }), button('Remove ' + image.alt, '×', () => {
                remove(photo);
                notify('');
                render();
            }));
            const label = document.createElement('label');
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'primary_image';
            radio.value = photo.id ? 'saved:' + photo.id : 'new:' + newIndex++;
            radio.checked = primary === photo;
            radio.addEventListener('change', () => { primary = photo; });
            label.append(radio, ' Primary');
            card.append(image, actions, label);
            return card;
        }));
    }

    picker.addEventListener('click', () => { picker.value = ''; });
    picker.addEventListener('cancel', formDataFiles);
    editor.closest('form').addEventListener('submit', formDataFiles);
    picker.addEventListener('change', () => {
        const files = Array.from(picker.files);
        if (photos.length + files.length > 3) {
            notify('A venue can have up to three photos. Remove a photo to make room.');
        } else if (!files.every(valid)) {
            notify('Choose JPEG, PNG or WebP photos up to 2 MB each.');
        } else {
            photos.push(...files.map((file) => ({ file, url: URL.createObjectURL(file) })));
            primary ||= photos[0];
            notify('');
        }
        render();
    });

    const dialog = editor.closest('dialog');
    dialog?.addEventListener('close', () => {
        photos.filter((photo) => photo.file).forEach((photo) => URL.revokeObjectURL(photo.url));
    }, { once: true });

    render();
})();
