const passwordInput = document.getElementById('password');
const rulesList = document.getElementById('password-rules');

if (passwordInput && rulesList) {
    const checks = {
        length: (value) => Array.from(value).length >= 8,
        uppercase: (value) => /\p{Lu}/u.test(value),
        lowercase: (value) => /\p{Ll}/u.test(value),
        symbol: (value) => /[\p{P}\p{S}]/u.test(value),
    };

    function updatePasswordRules() {
        const value = passwordInput.value;

        for (const rule of rulesList.querySelectorAll('[data-rule]')) {
            const met = checks[rule.dataset.rule](value);
            rule.classList.toggle('is-met', met);
        }
    }

    passwordInput.addEventListener('input', updatePasswordRules);
    passwordInput.addEventListener('change', updatePasswordRules);
    updatePasswordRules();
}