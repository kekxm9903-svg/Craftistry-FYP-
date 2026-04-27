// adminFeedbacks.js

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

// ── Full message modal ────────────────────────────────────────────────────────

const overlay = document.getElementById('modal-overlay');

function openModal(id) {
    const data = document.getElementById('modal-data-' + id);
    if (!data) return;

    document.getElementById('modal-avatar').textContent  = data.dataset.name.charAt(0).toUpperCase();
    document.getElementById('modal-name').textContent    = data.dataset.name;
    document.getElementById('modal-email').textContent   = data.dataset.email;
    document.getElementById('modal-subject').textContent = data.dataset.subject;
    document.getElementById('modal-date').textContent    = data.dataset.date;
    document.getElementById('modal-message').textContent = data.dataset.message;

    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
}

// Close on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});