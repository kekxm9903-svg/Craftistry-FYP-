// adminUserList.js

// ── Sidebar toggle (mobile) ───────────────────────────────────────────────────

const sidebar = document.getElementById('admin-sidebar');
const toggle  = document.getElementById('sidebar-toggle');

if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });

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

const alert = document.getElementById('admin-alert');
if (alert) {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        alert.style.opacity    = '0';
        alert.style.transform  = 'translateY(-6px)';
        setTimeout(() => alert.remove(), 400);
    }, 4000);
}

// ── Search: auto-submit on Enter, clear button ────────────────────────────────

function clearSearch() {
    const form  = document.getElementById('filterForm');
    const input = form.querySelector('.search-input');
    input.value = '';
    form.submit();
}

// Submit search form on Enter key
const searchInput = document.querySelector('.search-input');
if (searchInput) {
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('filterForm').submit();
        }
    });
}