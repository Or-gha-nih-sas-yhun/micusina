import './bootstrap';

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    const slash = toggle.querySelector('[data-password-slash]');

    if (!input || !slash) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isVisible = input.type === 'password';

        input.type = isVisible ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(isVisible));
        toggle.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
        slash.toggleAttribute('hidden', isVisible);
    });
});
