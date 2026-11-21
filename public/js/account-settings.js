const passwordDialog = document.getElementById('password-dialog');
const passwordForm = passwordDialog.querySelector('form');

document.getElementById('open-password-dialog').addEventListener('click', () => {
    passwordDialog.showModal();
});

document.getElementById('close-password-dialog').addEventListener('click', () => {
    passwordDialog.close();
});

passwordDialog.addEventListener('close', () => {
    passwordForm.reset();
});

if (passwordDialog.hasAttribute('data-show-errors')) {
    passwordDialog.showModal();
}