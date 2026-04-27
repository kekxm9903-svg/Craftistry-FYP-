// Toggle password visibility
function togglePasswordVisibility(buttonId, inputId) {
    const button = document.getElementById(buttonId);
    const input = document.getElementById(inputId);
    if (!button || !input) return;

    button.addEventListener('click', () => {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        button.querySelector('i').classList.toggle('fa-eye');
        button.querySelector('i').classList.toggle('fa-eye-slash');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    togglePasswordVisibility('togglePassword', 'password');
    togglePasswordVisibility('toggleConfirmPassword', 'confirmPassword');

    // Form validation
    const registerForm = document.getElementById('registerForm');
    registerForm?.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const terms = document.getElementById('terms');

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match');
            return false;
        }

        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return false;
        }

        if (!terms.checked) {
            e.preventDefault();
            alert('Please accept the Terms & Conditions');
            return false;
        }
    });
});
