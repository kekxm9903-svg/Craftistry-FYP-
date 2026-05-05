// favorites.js

// ── Helpers ──────────────────────────────────────────────────────────────────

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.content ?? '';

let toastTimer;
function showToast(msg, icon = 'check-circle') {
    const toast = document.getElementById('fav-toast');
    if (!toast) return;
    clearTimeout(toastTimer);
    toast.innerHTML = `<i class="fas fa-${icon}"></i>${msg}`;
    toast.classList.add('show');
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
}

// ── Generic remove ────────────────────────────────────────────────────────────

async function removeFavorite(url, row, name) {
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept':       'application/json',
            },
        });

        if (!res.ok) throw new Error('Request failed');

        const data = await res.json();

        if (!data.favorited) {
            row.classList.add('removing');
            setTimeout(() => {
                row.remove();
                updateHeaderCount();
            }, 300);
            showToast(`${name} removed from favourites`, 'heart-broken');
        }

    } catch (err) {
        showToast('Something went wrong. Please try again.', 'exclamation-circle');
    }
}

// ── Bind artist unfav buttons ─────────────────────────────────────────────────

function bindArtistRemoveButtons() {
    document.querySelectorAll('.artist-row .btn-unfav').forEach(btn => {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            btn.disabled = true;
            const row  = btn.closest('.request-row');
            const url  = btn.dataset.url;
            const name = row.querySelector('.request-title')?.textContent?.trim() ?? 'Artist';
            await removeFavorite(url, row, name);
            btn.disabled = false;
        });
    });
}

// ── Bind product unfav buttons ────────────────────────────────────────────────

function bindProductRemoveButtons() {
    document.querySelectorAll('.product-row .btn-unfav').forEach(btn => {
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            btn.disabled = true;
            const row  = btn.closest('.request-row');
            const url  = btn.dataset.url;
            const name = row.querySelector('.request-title')?.textContent?.trim() ?? 'Artwork';
            await removeFavorite(url, row, name);
            btn.disabled = false;
        });
    });
}

// ── Update header saved count ─────────────────────────────────────────────────

function updateHeaderCount() {
    const artistRows  = document.querySelectorAll('.artist-row').length;
    const productRows = document.querySelectorAll('.product-row').length;
    const total       = artistRows + productRows;

    const badge = document.querySelector('.page-header-card .status-badge');
    if (badge) {
        badge.innerHTML = `<i class="fas fa-heart"></i> ${total} saved total`;
    }
}

// ── Init ──────────────────────────────────────────────────────────────────────

bindArtistRemoveButtons();
bindProductRemoveButtons();