// public/js/register.js

// Toggle Password Visibility
function togglePasswordVisibility(buttonId, inputId) {
  const button = document.getElementById(buttonId);
  const input = document.getElementById(inputId);
  
  button.addEventListener('click', () => {
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    button.querySelector('i').classList.toggle('fa-eye');
    button.querySelector('i').classList.toggle('fa-eye-slash');
  });
}

togglePasswordVisibility('togglePassword', 'password');
togglePasswordVisibility('toggleConfirmPassword', 'confirmPassword');

// Form will submit normally - Laravel handles it
// But you can add client-side validation
document.getElementById('registerForm').addEventListener('submit', (e) => {
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirmPassword').value;

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
});