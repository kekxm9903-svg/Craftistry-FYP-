// favorites.js

console.log('Favorites JS loaded');

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

// ── Remove favourite ──────────────────────────────────────────────────────────

function bindRemoveButtons() {
    document.querySelectorAll('.btn-unfav').forEach(btn => {
        btn.addEventListener('click', handleRemove);
    });
}

async function handleRemove(e) {
    e.preventDefault();
    e.stopPropagation();

    const btn  = e.currentTarget;
    const card = btn.closest('.artist-card');
    const url  = btn.dataset.url;
    const name = card.querySelector('.artist-name')?.textContent?.trim() ?? 'Artist';

    btn.disabled = true;

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
            card.classList.add('removing');
            setTimeout(() => {
                card.remove();
                updateCount();
                checkIfEmpty();
            }, 300);
            showToast(`${name} removed from favourites`, 'heart-broken');
        }

    } catch (err) {
        btn.disabled = false;
        showToast('Something went wrong. Please try again.', 'exclamation-circle');
    }
}

// ── Update header count ───────────────────────────────────────────────────────

function updateCount() {
    const remaining = document.querySelectorAll('.artist-card').length;
    const sub = document.querySelector('.page-header p');
    if (!sub) return;
    sub.textContent = remaining === 0
        ? 'Start building your personal artist collection'
        : `${remaining} ${remaining === 1 ? 'artist' : 'artists'} in your collection`;
}

// ── Show empty state when last card removed ───────────────────────────────────

function checkIfEmpty() {
    if (document.querySelectorAll('.artist-card').length > 0) return;

    document.querySelector('.filter-bar')?.remove();
    document.querySelector('.artist-grid')?.remove();
    document.querySelector('.empty-search')?.remove();

    const page = document.querySelector('.fav-page');
    if (!page) return;

    const empty = document.createElement('div');
    empty.className = 'empty-state';
    empty.innerHTML = `
        <div class="empty-icon"><i class="fas fa-heart"></i></div>
        <h3>No favourite artists yet</h3>
        <p>Discover talented artists and tap <strong>Favourite</strong> on their profile to save them here.</p>
        <a href="/artists" class="btn-browse-empty">
            <i class="fas fa-compass"></i>
            Browse Artists
        </a>
    `;
    page.appendChild(empty);
}

// ── Live search ───────────────────────────────────────────────────────────────

const searchInput    = document.getElementById('fav-search');
const searchClear    = document.getElementById('search-clear');
const emptySearch    = document.getElementById('empty-search');
const clearSearchBtn = document.getElementById('clear-search-btn');

function runSearch(query) {
    const q      = query.toLowerCase().trim();
    const cards  = document.querySelectorAll('.artist-card');
    let   visible = 0;

    cards.forEach(card => {
        const match = q === ''
            || (card.dataset.name ?? '').includes(q)
            || (card.dataset.specialization ?? '').includes(q);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    if (emptySearch) {
        emptySearch.style.display = visible === 0 && q !== '' ? 'block' : 'none';
    }
    if (searchClear) {
        searchClear.style.display = q !== '' ? 'flex' : 'none';
    }
}

function clearSearch() {
    if (searchInput) { searchInput.value = ''; runSearch(''); searchInput.focus(); }
}

if (searchInput)    searchInput.addEventListener('input', e => runSearch(e.target.value));
if (searchClear)    searchClear.addEventListener('click', clearSearch);
if (clearSearchBtn) clearSearchBtn.addEventListener('click', clearSearch);

// ── Sort ──────────────────────────────────────────────────────────────────────

const sortSelect = document.getElementById('fav-sort');

function sortCards(method) {
    const grid  = document.getElementById('fav-grid');
    if (!grid) return;
    const cards = [...grid.querySelectorAll('.artist-card')];

    cards.sort((a, b) => {
        switch (method) {
            case 'name-az':  return (a.dataset.name ?? '').localeCompare(b.dataset.name ?? '');
            case 'name-za':  return (b.dataset.name ?? '').localeCompare(a.dataset.name ?? '');
            case 'rating':   return parseFloat(b.dataset.rating ?? 0) - parseFloat(a.dataset.rating ?? 0);
            case 'oldest':   return new Date(a.dataset.favoritedAt ?? 0) - new Date(b.dataset.favoritedAt ?? 0);
            default:         return new Date(b.dataset.favoritedAt ?? 0) - new Date(a.dataset.favoritedAt ?? 0);
        }
    });

    cards.forEach((card, i) => {
        card.style.setProperty('--i', i);
        card.style.animation = 'none';
        void card.offsetHeight;
        card.style.animation = '';
        grid.appendChild(card);
    });
}

if (sortSelect) sortSelect.addEventListener('change', e => sortCards(e.target.value));

// ── Card link click — stop propagation ───────────────────────────────────────

document.querySelectorAll('.card-link').forEach(link => {
    link.addEventListener('click', e => {
        e.stopPropagation();
        if (link.getAttribute('href') === '#') e.preventDefault();
    });
});

// ── Init ──────────────────────────────────────────────────────────────────────

bindRemoveButtons();