// feedback.js

// ── Character counter ─────────────────────────────────────────────────────────

const messageEl  = document.getElementById('message');
const charCount  = document.getElementById('charCount');

if (messageEl && charCount) {
    const update = () => {
        const len = messageEl.value.length;
        charCount.textContent = len;
        charCount.style.color = len > 1800 ? '#ef4444' : len > 1500 ? '#f97316' : '#9ca3af';
    };
    messageEl.addEventListener('input', update);
    update(); // run on load for old() values
}

// ── Category card visual selection ───────────────────────────────────────────

document.querySelectorAll('.cat-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('is-selected'));
        if (radio.checked) {
            radio.closest('.cat-option').querySelector('.cat-card').classList.add('is-selected');
        }
    });
    // Mark already-checked on page load (for old() repopulation)
    if (radio.checked) {
        radio.closest('.cat-option').querySelector('.cat-card').classList.add('is-selected');
    }
});

// ── Auto-dismiss success alert after 6s ──────────────────────────────────────

const alertEl = document.getElementById('fb-alert');
if (alertEl) {
    setTimeout(() => {
        alertEl.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        alertEl.style.opacity    = '0';
        alertEl.style.transform  = 'translateY(-8px)';
        setTimeout(() => alertEl.remove(), 400);
    }, 6000);
}

// ── Prevent double-submit ─────────────────────────────────────────────────────

const form      = document.getElementById('feedbackForm');
const submitBtn = document.getElementById('submitBtn');

if (form && submitBtn) {
    form.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';
    });
}