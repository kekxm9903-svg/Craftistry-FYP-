// login.js - Complete login form functionality with Remember Me

// Toggle Password Visibility
function togglePasswordVisibility(buttonId, inputId) {
  const button = document.getElementById(buttonId);
  const input = document.getElementById(inputId);
  
  if (button && input) {  // Check if elements exist
    button.addEventListener('click', () => {
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      const icon = button.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      }
    });
  }
}

// Remember Me Functionality
function initRememberMe() {
  const emailInput = document.querySelector('input[name="email"]');
  const passwordInput = document.getElementById('password');
  const rememberCheckbox = document.querySelector('input[name="remember"]');
  const loginForm = document.getElementById('loginForm');

  if (!emailInput || !passwordInput || !rememberCheckbox || !loginForm) return;

  // Load saved credentials on page load
  const savedEmail = localStorage.getItem('rememberedEmail');
  const savedPassword = localStorage.getItem('rememberedPassword');
  
  if (savedEmail && savedPassword) {
    emailInput.value = savedEmail;
    passwordInput.value = savedPassword;
    rememberCheckbox.checked = true;
  }

  // Save credentials when form is submitted
  loginForm.addEventListener('submit', function(e) {
    if (rememberCheckbox.checked) {
      // Save email and password to localStorage
      localStorage.setItem('rememberedEmail', emailInput.value);
      localStorage.setItem('rememberedPassword', passwordInput.value);
    } else {
      // Remove saved credentials if unchecked
      localStorage.removeItem('rememberedEmail');
      localStorage.removeItem('rememberedPassword');
    }
  });

  // Clear saved credentials when checkbox is unchecked
  rememberCheckbox.addEventListener('change', function() {
    if (!this.checked) {
      localStorage.removeItem('rememberedEmail');
      localStorage.removeItem('rememberedPassword');
    }
  });
}

// Form Validation
function initFormValidation() {
  const loginForm = document.getElementById('loginForm');
  const emailInput = document.querySelector('input[name="email"]');
  const passwordInput = document.getElementById('password');

  if (!loginForm || !emailInput || !passwordInput) return;

  loginForm.addEventListener('submit', function(e) {
    let isValid = true;
    
    // Clear previous error states
    document.querySelectorAll('.is-invalid').forEach(el => {
      el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.error-message').forEach(el => {
      el.remove();
    });

    // Validate email
    if (!emailInput.value.trim()) {
      showError(emailInput, 'Please enter your email address');
      isValid = false;
    } else if (!isValidEmail(emailInput.value)) {
      showError(emailInput, 'Please enter a valid email address');
      isValid = false;
    }

    // Validate password
    if (!passwordInput.value) {
      showError(passwordInput, 'Please enter your password');
      isValid = false;
    }

    if (!isValid) {
      e.preventDefault();
    }
  });
}

// Helper function to show error
function showError(input, message) {
  input.classList.add('is-invalid');
  
  const errorDiv = document.createElement('div');
  errorDiv.className = 'error-message';
  errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
  
  input.parentElement.appendChild(errorDiv);
}

// Helper function to validate email
function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// Auto-dismiss alerts
function initAlertDismiss() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.3s ease-out';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 300);
    }, 5000);
  });
}

// Initialize all functions when page loads
document.addEventListener('DOMContentLoaded', function() {
  togglePasswordVisibility('togglePassword', 'password');
  initRememberMe();
  initFormValidation();
  initAlertDismiss();
});