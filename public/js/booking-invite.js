const invitation = document.querySelector('#invitation');
const example = document.querySelector('[data-invite-example]');

example?.addEventListener('click', () => {
    if (!invitation || invitation.value.trim()) {
        invitation?.focus();
        return;
    }

    invitation.value = `Subject: Sales team planning meeting

Hi team,

Can we meet in Amman tomorrow at 10:00 AM for two hours?
Eight people will join, including two on Zoom. Please arrange a screen,
a camera, coffee, and water.

Thanks!`;
    invitation.focus();
});

// The review panel is plain content on the page (visible without JavaScript).
// With JavaScript it can be closed and reopened, and it starts collapsed once suggestions are shown.
const review = document.querySelector('#invite-review');
const edit = document.querySelector('[data-invite-review-open]');
const close = document.querySelector('[data-invite-review-close]');
const results = document.querySelector('#invite-results');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function reveal(section) {
    section.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    section.querySelector('h2')?.focus({ preventScroll: true });
}

if (review) {
    const setOpen = (open) => {
        review.hidden = !open;
        if (edit) edit.hidden = open;
    };

    if (close) close.hidden = false;

    close?.addEventListener('click', () => {
        setOpen(false);
        edit?.focus();
    });

    edit?.addEventListener('click', () => {
        setOpen(true);
        reveal(review);
    });

    if (review.hasAttribute('data-invite-collapsed')) {
        setOpen(false);
    } else {
        setOpen(true);
        reveal(review);
    }
}

if (results) {
    reveal(results);
}