/* ═══════════════════════════════════════════
   CRAFTISTRY — classEventDeleteIndex.js
   Delete logic for the class/event index page
   (participantsBaseRoute and csrfToken are
    injected inline by the blade template)
═══════════════════════════════════════════ */

let deleteTargetId = null;

/* ── Open delete modal ── */
function confirmDelete(id, name) {
    deleteTargetId = id;
    document.getElementById('deleteClassName').textContent = name;
    document.getElementById('deleteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

/* ── Close delete modal ── */
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.body.style.overflow = '';
    deleteTargetId = null;
}

/* ── Execute delete ── */
function deleteClass() {
    if (!deleteTargetId) return;

    fetch(`${participantsBaseRoute}/${deleteTargetId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        closeDeleteModal();
        if (data.success) {
            const card = document.querySelector(`.class-card[data-id="${deleteTargetId}"]`);
            if (card) card.remove();
            showPopup('success', data.message || 'Deleted successfully.');

            // If grid is now empty reload so empty-state shows
            const remaining = document.querySelectorAll('.class-card');
            if (remaining.length === 0) {
                setTimeout(() => window.location.href = indexRoute, 1200);
            }
        } else {
            showPopup('error', data.message || 'Could not delete.');
        }
    })
    .catch(() => {
        closeDeleteModal();
        showPopup('error', 'Something went wrong. Please try again.');
    });
}

/* ── Toast popup ── */
function showPopup(type, msg) {
    const isSuccess = type === 'success';
    const el  = document.getElementById(isSuccess ? 'successPopup'       : 'errorNotification');
    const txt = document.getElementById(isSuccess ? 'successPopupMsg'    : 'errorPopupMsg');
    if (!el || !txt) return;
    txt.textContent = msg;
    el.classList.remove('hide');
    el.classList.add('show');
    setTimeout(() => {
        el.classList.remove('show');
        el.classList.add('hide');
        setTimeout(() => el.classList.remove('hide'), 350);
    }, 3000);
}

/* ── Close on Escape ── */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDeleteModal();
});