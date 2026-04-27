// adminReports.js

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

// ── Report detail modal ───────────────────────────────────────────────────────

const detailOverlay = document.getElementById('modal-overlay');

function openModal(id) {
    const data = document.getElementById('modal-data-' + id);
    if (!data) return;

    document.getElementById('modal-reporter').textContent = data.dataset.reporter;
    document.getElementById('modal-reported').textContent = data.dataset.reported;
    document.getElementById('modal-reason').textContent   = data.dataset.reason;
    document.getElementById('modal-date').textContent     = data.dataset.date;
    document.getElementById('modal-details').textContent  = data.dataset.details || '—';

    // Status badge
    const status = data.dataset.status;
    const statusEl = document.getElementById('modal-status');
    const labels = { pending: '⏳ Pending', reviewed: '👁 Reviewed', dismissed: '✕ Dismissed' };
    statusEl.textContent = labels[status] || status;

    detailOverlay.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    detailOverlay.classList.remove('open');
    document.body.style.overflow = '';
}

// ── Ban confirm modal ─────────────────────────────────────────────────────────

const banOverlay = document.getElementById('ban-overlay');

function openBanModal(reportId, username, userId) {
    document.getElementById('ban-username').textContent = username;
    document.getElementById('ban-form').action = '/admin/users/' + userId + '/ban';
    banOverlay.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeBanModal() {
    banOverlay.classList.remove('open');
    document.body.style.overflow = '';
}

// ── Close modals on Escape ────────────────────────────────────────────────────

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
        closeBanModal();
        closeAllDropdowns();
    }
});

// ── Status dropdown — click-based, fixed position ────────────────────────────

let activeDropdown = null;

function closeAllDropdowns() {
    document.querySelectorAll('.status-dropdown.open').forEach(d => {
        d.classList.remove('open');
        d.closest('.status-dropdown-wrap')
            ?.querySelector('.action-btn.status-toggle')
            ?.classList.remove('active');
    });
    activeDropdown = null;
}

document.querySelectorAll('.action-btn.status-toggle').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();

        const wrap     = btn.closest('.status-dropdown-wrap');
        const dropdown = wrap.querySelector('.status-dropdown');
        const isOpen   = dropdown.classList.contains('open');

        // Close any other open dropdown first
        closeAllDropdowns();

        if (!isOpen) {
            // Position using fixed coords relative to the viewport
            const rect = btn.getBoundingClientRect();

            dropdown.style.top  = (rect.bottom + 6) + 'px';

            // Align to right edge of button, but keep on screen
            const dropWidth = 150; // min-width from CSS
            let left = rect.right - dropWidth;
            if (left < 8) left = 8;
            dropdown.style.left = left + 'px';

            dropdown.classList.add('open');
            btn.classList.add('active');
            activeDropdown = dropdown;
        }
    });
});

// Close dropdown when clicking anywhere outside it
document.addEventListener('click', (e) => {
    if (activeDropdown && !e.target.closest('.status-dropdown-wrap')) {
        closeAllDropdowns();
    }
});

// Reposition on scroll/resize so it doesn't drift
['scroll', 'resize'].forEach(evt => {
    window.addEventListener(evt, () => {
        if (activeDropdown) closeAllDropdowns();
    }, { passive: true });
});