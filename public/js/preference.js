// preference.js

let selectedPref = null;

// ── Always read CSRF from meta tag at request time (never stale) ─────────────
function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

// ── Select an option ──────────────────────────────────────────────────────────

function selectPref(btn) {
    document.querySelectorAll('.pref-option').forEach(o => o.classList.remove('selected'));
    btn.classList.add('selected');
    selectedPref = btn.dataset.value;
    document.getElementById('pref-save').disabled = false;
}

// ── Save preference ───────────────────────────────────────────────────────────

async function savePref() {
    if (!selectedPref) return;

    const saveBtn = document.getElementById('pref-save');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    const url  = window.PREF_STORE_URL;
    const csrf = getCsrf();

    console.log('[Pref] POST to:', url);
    console.log('[Pref] CSRF:', csrf ? csrf.substring(0, 10) + '...' : 'MISSING');
    console.log('[Pref] Value:', selectedPref);

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept':       'application/json',
            },
            body: JSON.stringify({ preferred_artwork_type: selectedPref }),
        });

        console.log('[Pref] Status:', res.status);

        const text = await res.text();
        console.log('[Pref] Raw (300):', text.substring(0, 300));

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            throw new Error('Non-JSON (status ' + res.status + '): ' + text.substring(0, 150));
        }

        if (!res.ok || !data.success) {
            throw new Error(
                data.message
                || (data.errors ? Object.values(data.errors).flat().join('\n') : null)
                || 'Failed with status ' + res.status
            );
        }

        closeModal(true);

    } catch (err) {
        console.error('[Pref]', err);
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-check"></i> Save Preference';
        alert('Error: ' + err.message);
    }
}

// ── Skip ─────────────────────────────────────────────────────────────────────

async function skipPref() {
    try {
        await fetch(window.PREF_SKIP_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
                'Accept':       'application/json',
            },
        });
    } catch (err) {
        console.warn('[Pref] skip error (ignored):', err);
    }
    closeModal(false);
}

// ── Close ─────────────────────────────────────────────────────────────────────

function closeModal(reload) {
    const overlay = document.getElementById('pref-overlay');
    if (!overlay) return;
    overlay.style.transition = 'opacity .2s';
    overlay.style.opacity    = '0';
    setTimeout(() => {
        overlay.remove();
        if (reload) window.location.reload();
    }, 220);
}

// ── Close on overlay backdrop click ──────────────────────────────────────────

document.getElementById('pref-overlay')?.addEventListener('click', function (e) {
    if (e.target === this) skipPref();
});

// ── Close on Escape ───────────────────────────────────────────────────────────

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && document.getElementById('pref-overlay')) skipPref();
});