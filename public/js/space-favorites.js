document.querySelectorAll('.place-card__favorite-form').forEach((form) => {
    const button = form.querySelector('button[type="submit"]');
    let pending = false;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (pending) return;

        pending = true;
        button.setAttribute('aria-disabled', 'true');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });

            if (!response.ok) throw new Error('Favorite request failed');
            const { is_favorite: isFavorite } = await response.json();
            if (typeof isFavorite !== 'boolean') throw new Error('Missing favorite state');

            button.classList.toggle('is-active', isFavorite);
            button.setAttribute('aria-pressed', String(isFavorite));
            button.setAttribute('aria-label', isFavorite ? 'Remove from favorites' : 'Add to favorites');
        } catch {
            window.alert('Could not update your favorites. Please try again.');
        } finally {
            pending = false;
            button.removeAttribute('aria-disabled');
        }
    });
});
