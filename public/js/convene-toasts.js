document.querySelectorAll('[data-convene-toast-close]').forEach(button => {
    button.addEventListener('click', () => button.closest('[data-convene-toast]').remove());
});
