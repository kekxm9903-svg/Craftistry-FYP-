/**
 * dashboard.js — Craftistry Buyer Dashboard
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Smooth hover lift on stat cards ──────────────────────────────────────
    document.querySelectorAll('.stat-card').forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            this.style.willChange = 'transform';
        });
        card.addEventListener('mouseleave', function () {
            this.style.willChange = 'auto';
        });
    });

    // ── Product card image lazy-load fallback ─────────────────────────────────
    document.querySelectorAll('.product-img img, .asc-cover img, .asc-avatar img').forEach(function (img) {
        img.addEventListener('error', function () {
            this.style.display = 'none';
        });
    });

    // ── Artist scroll row — drag to scroll ───────────────────────────────────
    var scrollRow = document.querySelector('.artist-scroll-row');
    if (scrollRow) {
        var isDown   = false;
        var startX   = 0;
        var scrollLeft = 0;

        scrollRow.addEventListener('mousedown', function (e) {
            isDown = true;
            scrollRow.style.cursor = 'grabbing';
            startX     = e.pageX - scrollRow.offsetLeft;
            scrollLeft = scrollRow.scrollLeft;
        });

        scrollRow.addEventListener('mouseleave', function () {
            isDown = false;
            scrollRow.style.cursor = '';
        });

        scrollRow.addEventListener('mouseup', function () {
            isDown = false;
            scrollRow.style.cursor = '';
        });

        scrollRow.addEventListener('mousemove', function (e) {
            if (!isDown) return;
            e.preventDefault();
            var x    = e.pageX - scrollRow.offsetLeft;
            var walk = (x - startX) * 1.5;
            scrollRow.scrollLeft = scrollLeft - walk;
        });
    }

});