const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('toggle-password');
const eyeSlash = document.getElementById('password-eye-slash');

if (passwordInput && togglePassword && eyeSlash) {
    togglePassword.hidden = false;

    togglePassword.addEventListener('click', () => {
        const showPassword = passwordInput.type === 'password';

        passwordInput.type = showPassword ? 'text' : 'password';
        eyeSlash.setAttribute('visibility', showPassword ? 'hidden' : 'visible');
        togglePassword.setAttribute(
            'aria-label',
            showPassword ? 'Hide password' : 'Show password'
        );
    });
}