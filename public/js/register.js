// register.js

// ── Styled alert modal (matches logout modal design) ─────────────
function showAlert(message, type = 'error') {
    // Remove existing if any
    const existing = document.getElementById('registerAlertModal');
    if (existing) existing.remove();

    const isError = type === 'error';
    const iconClass  = isError ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
    const iconBg     = isError ? 'linear-gradient(135deg,#fff5f5,#fed7d7)' : 'linear-gradient(135deg,#f0fff4,#c6f6d5)';
    const iconBorder = isError ? '#fca5a5' : '#9ae6b4';
    const iconColor  = isError ? '#ef4444' : '#38a169';
    const btnBg      = isError
        ? 'linear-gradient(135deg,#ef4444,#dc2626)'
        : 'linear-gradient(135deg,#667eea,#764ba2)';
    const btnShadow  = isError
        ? '0 4px 14px rgba(239,68,68,.35)'
        : '0 4px 14px rgba(102,126,234,.35)';

    const modal = document.createElement('div');
    modal.id = 'registerAlertModal';
    modal.style.cssText = [
        'position:fixed','inset:0','z-index:99999',
        'display:flex','align-items:center','justify-content:center',
        'animation:ralFadeIn .15s ease'
    ].join(';');

    modal.innerHTML = `
        <div style="position:absolute;inset:0;background:rgba(0,0,0,.48);backdrop-filter:blur(3px);"
             onclick="document.getElementById('registerAlertModal')?.remove()"></div>
        <div style="
            position:relative;background:#fff;border-radius:16px;
            padding:36px 32px 28px;max-width:380px;width:90%;
            box-shadow:0 24px 64px rgba(102,126,234,.22),0 4px 16px rgba(0,0,0,.08);
            text-align:center;z-index:1;
            animation:ralSlideIn .22s cubic-bezier(.34,1.56,.64,1);
        ">
            <div style="
                width:60px;height:60px;
                background:${iconBg};
                border-radius:50%;display:flex;align-items:center;justify-content:center;
                margin:0 auto 18px;
                border:2px solid ${iconBorder};
                box-shadow:0 4px 12px rgba(0,0,0,.08);
            ">
                <i class="${iconClass}" style="color:${iconColor};font-size:1.45rem;"></i>
            </div>
            <div style="font-size:1.15rem;font-weight:800;color:#1a202c;margin-bottom:10px;">
                ${isError ? 'Oops!' : 'Success'}
            </div>
            <div style="font-size:0.84rem;color:#718096;line-height:1.65;margin-bottom:28px;">
                ${message}
            </div>
            <button onclick="document.getElementById('registerAlertModal')?.remove()" style="
                width:100%;padding:12px;border-radius:8px;border:none;
                background:${btnBg};
                color:#fff;font-size:0.88rem;font-weight:700;
                cursor:pointer;font-family:'Inter',sans-serif;
                box-shadow:${btnShadow};
                transition:opacity .15s,transform .15s;
            " onmouseover="this.style.opacity='.88';this.style.transform='translateY(-1px)'"
               onmouseout="this.style.opacity='1';this.style.transform='translateY(0)'">
                OK
            </button>
        </div>
    `;

    // Inject keyframes once
    if (!document.getElementById('ralStyles')) {
        const style = document.createElement('style');
        style.id = 'ralStyles';
        style.textContent = `
            @keyframes ralFadeIn  { from { opacity:0 } to { opacity:1 } }
            @keyframes ralSlideIn { from { opacity:0;transform:scale(.88) translateY(16px) } to { opacity:1;transform:scale(1) translateY(0) } }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(modal);

    // Close on Escape
    const onKey = (e) => { if (e.key === 'Escape') { modal.remove(); document.removeEventListener('keydown', onKey); } };
    document.addEventListener('keydown', onKey);
}

// ── Password visibility toggle ──────────────────────────────────
function togglePasswordVisibility(buttonId, inputId) {
    const button = document.getElementById(buttonId);
    const input  = document.getElementById(inputId);
    if (!button || !input) return;

    button.addEventListener('click', () => {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        button.querySelector('i').classList.toggle('fa-eye');
        button.querySelector('i').classList.toggle('fa-eye-slash');
    });
}

// ── Malaysian phone validation ──────────────────────────────────
function validateMalaysianPhone(local) {
    const digits = local.replace(/[\s\-()]/g, '');
    if (!/^\d+$/.test(digits)) return false;
    if (digits.length < 7 || digits.length > 10) return false;
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    togglePasswordVisibility('togglePassword', 'password');
    togglePasswordVisibility('toggleConfirmPassword', 'confirmPassword');

    // ── Phone prefix logic ──
    const phoneLocal = document.getElementById('phoneLocal');
    const phoneFull  = document.getElementById('phoneFull');
    const phoneError = document.getElementById('phoneError');

    phoneLocal?.addEventListener('input', function () {
        this.value = this.value.replace(/[^\d\s\-]/g, '');

        const isValid = validateMalaysianPhone(this.value);
        if (this.value.length > 0) {
            phoneError.classList.toggle('show', !isValid);
            this.closest('.phone-input-wrap').style.borderColor = isValid ? '' : '#e53e3e';
        } else {
            phoneError.classList.remove('show');
            this.closest('.phone-input-wrap').style.borderColor = '';
        }

        const digits = this.value.replace(/[\s\-]/g, '');
        phoneFull.value = digits ? '+60' + digits : '';
    });

    // Pre-fill from old() value if server returned validation error
    const oldPhone = phoneFull.value;
    if (oldPhone && oldPhone.startsWith('+60')) {
        phoneLocal.value = oldPhone.replace('+60', '');
    }

    // ── Form validation ──
    const registerForm = document.getElementById('registerForm');
    registerForm?.addEventListener('submit', function (e) {
        const password        = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const terms           = document.getElementById('terms');
        const localVal        = phoneLocal?.value ?? '';

        if (!localVal || !validateMalaysianPhone(localVal)) {
            e.preventDefault();
            phoneError.classList.add('show');
            phoneLocal.closest('.phone-input-wrap').style.borderColor = '#e53e3e';
            phoneLocal.focus();
            showAlert('Please enter a valid Malaysian phone number (e.g. 12-3456789).');
            return false;
        }

        if (password !== confirmPassword) {
            e.preventDefault();
            showAlert('Passwords do not match. Please re-enter your password.');
            return false;
        }

        if (password.length < 8) {
            e.preventDefault();
            showAlert('Password must be at least 8 characters long.');
            return false;
        }

        if (!terms.checked) {
            e.preventDefault();
            showAlert('Please accept the Terms &amp; Conditions to continue.');
            return false;
        }
    });
});