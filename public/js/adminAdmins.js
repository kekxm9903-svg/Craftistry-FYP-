// adminAdmins.js

// ── Sidebar toggle (mobile) ───────────────────────────────────────────────────

const sidebar = document.getElementById('admin-sidebar');
const toggle  = document.getElementById('sidebar-toggle');

if (toggle && sidebar) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));

    document.addEventListener('click', (e) => {
        if (
            window.innerWidth < 768 &&
            sidebar.classList.contains('open') &&
            !sidebar.contains(e.target) &&
            !toggle.contains(e.target)
        ) {
            sidebar.classList.remove('open');
        }
    });
}

// ── Auto-dismiss alert after 4s ───────────────────────────────────────────────

const alertEl = document.getElementById('admin-alert');
if (alertEl) {
    setTimeout(() => {
        alertEl.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        alertEl.style.opacity    = '0';
        alertEl.style.transform  = 'translateY(-6px)';
        setTimeout(() => alertEl.remove(), 400);
    }, 4000);
}

// ── Password show/hide toggle ─────────────────────────────────────────────────

const pwToggle = document.getElementById('pw-toggle');
const pwIcon   = document.getElementById('pw-icon');
const pwInput  = document.getElementById('password');

if (pwToggle && pwInput) {
    pwToggle.addEventListener('click', () => {
        const isPassword = pwInput.type === 'password';
        pwInput.type     = isPassword ? 'text' : 'password';
        pwIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
    });
}