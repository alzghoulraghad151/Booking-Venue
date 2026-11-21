const photoInput = document.getElementById('photo');
const choosePhotoButton = document.getElementById('choose-profile-photo');

choosePhotoButton.addEventListener('click', () => {
    photoInput.click();
});

photoInput.addEventListener('change', () => {
    if (photoInput.files.length > 0) {
        photoInput.form.requestSubmit();
    }
});

const emailDialog = document.getElementById('email-dialog');
const passwordInput = document.getElementById('current_password');

document.getElementById('edit-email').addEventListener('click', () => {
    emailDialog.showModal();
});

document.getElementById('cancel-email').addEventListener('click', () => {
    emailDialog.close();
});

emailDialog.addEventListener('close', () => {
    passwordInput.value = '';
});

if (emailDialog.hasAttribute('data-show-errors')) {
    emailDialog.showModal();
}