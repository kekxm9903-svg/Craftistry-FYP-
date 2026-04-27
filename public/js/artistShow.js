(function () {
    'use strict';

    /* ═══════════════════════════════════════════
       TOAST
    ═══════════════════════════════════════════ */
    const toast     = document.getElementById('fav-toast');
    let toastTimer;

    function showToast(msg, icon = 'heart') {
        clearTimeout(toastTimer);
        toast.innerHTML = `<i class="fas fa-${icon}"></i>${msg}`;
        toast.classList.add('show');
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
    }


    /* ═══════════════════════════════════════════
       FAVOURITE BUTTON
    ═══════════════════════════════════════════ */
    const btnFav = document.getElementById('btn-favorite');

    if (btnFav) {
        function applyFavState(favorited) {
            btnFav.dataset.favorited = favorited ? 'true' : 'false';
            btnFav.classList.toggle('active', favorited);

            const icon = btnFav.querySelector('i');
            icon.className = favorited ? 'fas fa-heart' : 'far fa-heart';

            btnFav.querySelector('span').textContent = favorited ? 'Favourited' : 'Favourite';
            btnFav.setAttribute('aria-label', favorited ? 'Remove from favourites' : 'Add to favourites');

            // Pop animation
            btnFav.classList.remove('pop');
            void btnFav.offsetWidth; // reflow
            btnFav.classList.add('pop');
        }

        btnFav.addEventListener('click', async function () {
            const wasFavorited = btnFav.dataset.favorited === 'true';
            const url          = btnFav.dataset.url;

            applyFavState(!wasFavorited); // Optimistic

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                });

                if (!res.ok) throw new Error('Failed');

                const data = await res.json();
                applyFavState(data.favorited);

                showToast(
                    data.favorited ? 'Added to your favourites!' : 'Removed from favourites',
                    data.favorited ? 'heart' : 'heart-broken'
                );
            } catch {
                applyFavState(wasFavorited); // Revert
                showToast('Something went wrong. Try again.', 'exclamation-circle');
            }
        });
    }


    /* ═══════════════════════════════════════════
       MORE (⋯) DROPDOWN
    ═══════════════════════════════════════════ */
    const btnMore    = document.getElementById('btn-more');
    const dropMenu   = document.getElementById('dropdown-menu');

    if (btnMore && dropMenu) {
        function openDropdown() {
            dropMenu.classList.add('open');
            btnMore.setAttribute('aria-expanded', 'true');
        }

        function closeDropdown() {
            dropMenu.classList.remove('open');
            btnMore.setAttribute('aria-expanded', 'false');
        }

        function toggleDropdown() {
            dropMenu.classList.contains('open') ? closeDropdown() : openDropdown();
        }

        btnMore.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleDropdown();
        });

        // Close when clicking outside
        document.addEventListener('click', function (e) {
            if (!btnMore.contains(e.target) && !dropMenu.contains(e.target)) {
                closeDropdown();
            }
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDropdown();
        });
    }


    /* ═══════════════════════════════════════════
       COPY PROFILE LINK
    ═══════════════════════════════════════════ */
    const btnCopy = document.getElementById('btn-copy-link');

    if (btnCopy) {
        btnCopy.addEventListener('click', async function () {
            if (dropMenu) dropMenu.classList.remove('open');
            if (btnMore)  btnMore.setAttribute('aria-expanded', 'false');

            const url = window.location.href;

            try {
                await navigator.clipboard.writeText(url);
                showToast('Profile link copied!', 'check-circle');
            } catch {
                // Fallback for older browsers
                const ta = document.createElement('textarea');
                ta.value = url;
                ta.style.cssText = 'position:fixed;opacity:0;';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast('Profile link copied!', 'check-circle');
            }
        });
    }


    /* ═══════════════════════════════════════════
       REPORT MODAL
    ═══════════════════════════════════════════ */
    const btnReport       = document.getElementById('btn-report');
    const reportOverlay   = document.getElementById('report-modal-overlay');
    const btnCloseReport  = document.getElementById('report-modal-close');
    const btnCancelReport = document.getElementById('btn-report-cancel');
    const btnSubmitReport = document.getElementById('btn-report-submit');

    function openReportModal() {
        // Close dropdown first
        if (dropMenu) dropMenu.classList.remove('open');
        if (btnMore)  btnMore.setAttribute('aria-expanded', 'false');

        reportOverlay.classList.add('open');
        reportOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeReportModal() {
        reportOverlay.classList.remove('open');
        reportOverlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        // Reset form
        document.querySelectorAll('input[name="report_reason"]').forEach(r => r.checked = false);
        const ta = document.getElementById('report-details');
        if (ta) ta.value = '';
        if (btnSubmitReport) {
            btnSubmitReport.disabled = false;
            btnSubmitReport.innerHTML = '<i class="fas fa-flag"></i> Submit Report';
        }
    }

    if (btnReport)       btnReport.addEventListener('click', openReportModal);
    if (btnCloseReport)  btnCloseReport.addEventListener('click', closeReportModal);
    if (btnCancelReport) btnCancelReport.addEventListener('click', closeReportModal);

    // Close on overlay backdrop click
    if (reportOverlay) {
        reportOverlay.addEventListener('click', function (e) {
            if (e.target === reportOverlay) closeReportModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && reportOverlay.classList.contains('open')) {
                closeReportModal();
            }
        });
    }

    // Submit Report
    if (btnSubmitReport) {
        btnSubmitReport.addEventListener('click', async function () {
            const selected = document.querySelector('input[name="report_reason"]:checked');

            if (!selected) {
                showToast('Please select a reason to report.', 'exclamation-triangle');
                return;
            }

            const reason  = selected.value;
            const details = (document.getElementById('report-details')?.value ?? '').trim();
            const url     = btnReport?.dataset.url;

            if (!url) return;

            btnSubmitReport.disabled = true;
            btnSubmitReport.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({ reason, details }),
                });

                const data = await res.json();

                if (!res.ok) {
                    btnSubmitReport.disabled = false;
                    btnSubmitReport.innerHTML = '<i class="fas fa-flag"></i> Submit Report';
                    showToast(data.error ?? 'Failed to submit report. Try again.', 'exclamation-circle');
                    return;
                }

                closeReportModal();
                showToast('Report submitted. Thank you!', 'flag');
            } catch {
                btnSubmitReport.disabled = false;
                btnSubmitReport.innerHTML = '<i class="fas fa-flag"></i> Submit Report';
                showToast('Failed to submit report. Try again.', 'exclamation-circle');
            }
        });
    }

})();