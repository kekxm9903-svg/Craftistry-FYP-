// adminAdmins.js

(function () {

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

// ── Permission checkboxes (add form) — sync .is-checked class ─────────────────

document.querySelectorAll('#addAdminForm .perm-cb').forEach(function (cb) {
    cb.addEventListener('change', function () {
        this.closest('.perm-toggle').classList.toggle('is-checked', this.checked);
    });
});

// ── EDIT PERMISSIONS MODAL ────────────────────────────────────────────────────

const editModal     = document.getElementById('editPermModal');
const editPermForm  = document.getElementById('editPermForm');
const editPermName  = document.getElementById('editPermName');

function openEditModal(btn) {
    const perms  = JSON.parse(btn.dataset.perms || '[]');
    const action = btn.dataset.action;

    editPermName.textContent = btn.dataset.name;
    editPermForm.action = action;

    // Reset then apply stored perms
    editModal.querySelectorAll('.modal-perm-cb').forEach(function (cb) {
        const checked = perms.includes(cb.dataset.module);
        cb.checked = checked;
        cb.closest('.perm-toggle').classList.toggle('is-checked', checked);
    });

    editModal.classList.add('open');
}

function closeEditModal() {
    editModal.classList.remove('open');
}

document.querySelectorAll('.btn-edit-perm').forEach(function (btn) {
    btn.addEventListener('click', function () { openEditModal(this); });
});

document.getElementById('editPermClose')  && document.getElementById('editPermClose').addEventListener('click', closeEditModal);
document.getElementById('editPermCancel') && document.getElementById('editPermCancel').addEventListener('click', closeEditModal);

// Sync .is-checked in modal
if (editModal) {
    editModal.querySelectorAll('.modal-perm-cb').forEach(function (cb) {
        cb.addEventListener('change', function () {
            this.closest('.perm-toggle').classList.toggle('is-checked', this.checked);
        });
    });
}

// ── REMOVE CONFIRM MODAL ──────────────────────────────────────────────────────

const removeModal = document.getElementById('removeModal');
const removeForm  = document.getElementById('removeForm');
const removeName  = document.getElementById('removeName');

function openRemoveModal(btn) {
    removeName.textContent = btn.dataset.name;
    removeForm.action      = btn.dataset.action;
    removeModal.classList.add('open');
}

function closeRemoveModal() {
    removeModal.classList.remove('open');
}

document.querySelectorAll('.btn-remove').forEach(function (btn) {
    btn.addEventListener('click', function () { openRemoveModal(this); });
});

document.getElementById('removeClose')  && document.getElementById('removeClose').addEventListener('click', closeRemoveModal);
document.getElementById('removeCancel') && document.getElementById('removeCancel').addEventListener('click', closeRemoveModal);

// ── Close modals on backdrop click or Escape ──────────────────────────────────

[editModal, removeModal].forEach(function (modal) {
    if (!modal) return;
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('open');
    });
});

document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    editModal  && editModal.classList.remove('open');
    removeModal && removeModal.classList.remove('open');
});

})();