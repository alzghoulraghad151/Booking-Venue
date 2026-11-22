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

const review = document.querySelector('#invite-review');
const edit = document.querySelector('[data-invite-review-open]');

if (review && typeof review.showModal === 'function') {
    review.close();

    if (review.hasAttribute('data-invite-auto-open')) {
        review.showModal();
    }

    if (edit) {
        edit.hidden = false;
        edit.addEventListener('click', () => review.showModal());
    }
}
