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

// Form Submission
document.getElementById('loginForm').addEventListener('submit', (e) => {
  e.preventDefault();

  const loginData = {
    email: document.getElementById('email').value,
    password: document.getElementById('password').value,
    remember: document.getElementById('remember').checked
  };

  console.log('Login attempt:', loginData);
  alert('Login functionality would be implemented here!');
  
  // Redirect after successful login
  // window.location.href = 'dashboard.html';
});