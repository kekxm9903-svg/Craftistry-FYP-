// notification.js

(function () {
    'use strict';

    const DROPDOWN_URL  = window.NOTI_DROPDOWN_URL;
    const MARK_ALL_URL  = window.NOTI_MARK_ALL_URL;
    const READ_URL_BASE = window.NOTI_READ_URL_BASE;  // e.g. /notifications/{id}/read
    const CSRF          = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let pollTimer  = null;
    let isOpen     = false;
    let isLoading  = false;

    const dropdown = document.getElementById('noti-dropdown');
    const btn      = document.getElementById('noti-btn');
    const panel    = document.getElementById('noti-panel');
    const badge    = document.getElementById('noti-badge');
    const list     = document.getElementById('noti-list');
    const markAll  = document.getElementById('noti-mark-all');

    if (!btn || !panel) return; // not logged in or nav not rendered

    // ── Toggle dropdown ───────────────────────────────────────────

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        isOpen ? closeDropdown() : openDropdown();
    });

    document.addEventListener('click', function (e) {
        if (dropdown && !dropdown.contains(e.target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDropdown();
    });

    function openDropdown() {
        isOpen = true;
        dropdown.classList.add('open');
        fetchNotifications();
    }

    function closeDropdown() {
        isOpen = false;
        dropdown.classList.remove('open');
    }

    // ── Fetch notifications ───────────────────────────────────────

    async function fetchNotifications() {
        if (isLoading) return;
        isLoading = true;

        if (list) {
            list.innerHTML = `
                <div class="noti-loading">
                    <i class="fas fa-spinner"></i>
                    Loading...
                </div>`;
        }

        try {
            const res  = await fetch(DROPDOWN_URL, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF() }
            });
            const data = await res.json();

            updateBadge(data.unread_count);
            renderList(data.notifications);

        } catch (err) {
            if (list) {
                list.innerHTML = '<div class="noti-empty"><p>Failed to load notifications.</p></div>';
            }
        } finally {
            isLoading = false;
        }
    }

    // ── Poll for unread count every 30s ──────────────────────────

    async function pollUnreadCount() {
        try {
            const res  = await fetch(DROPDOWN_URL, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF() }
            });
            const data = await res.json();
            updateBadge(data.unread_count);
        } catch (err) {
            // Silent fail
        }
    }

    pollUnreadCount(); // Run once on page load
    pollTimer = setInterval(pollUnreadCount, 30000); // Then every 30s

    // ── Update badge ──────────────────────────────────────────────

    function updateBadge(count) {
        if (!badge) return;
        badge.textContent = count > 99 ? '99+' : count;
        if (count > 0) {
            badge.classList.add('has-unread');
        } else {
            badge.classList.remove('has-unread');
        }
    }

    // ── Render notification list ──────────────────────────────────

    function renderList(notifications) {
        if (!list) return;

        if (!notifications || notifications.length === 0) {
            list.innerHTML = `
                <div class="noti-empty">
                    <i class="fas fa-bell-slash"></i>
                    <p>No notifications yet</p>
                </div>`;
            return;
        }

        list.innerHTML = notifications.map(n => `
            <div class="noti-item ${n.is_unread ? 'unread' : ''}"
                 data-id="${n.id}"
                 data-url="${n.url || ''}"
                 onclick="handleNotiClick(this)">
                <div class="noti-icon" style="background:${n.color}">
                    <i class="${n.icon}"></i>
                </div>
                <div class="noti-content">
                    <div class="noti-title">${escHtml(n.title)}</div>
                    <div class="noti-message">${escHtml(n.message)}</div>
                    <span class="noti-time">${escHtml(n.time)}</span>
                </div>
                ${n.is_unread ? '<div class="noti-unread-dot"></div>' : ''}
            </div>
        `).join('');
    }

    // ── Handle notification click ─────────────────────────────────

    window.handleNotiClick = async function (el) {
        const id  = el.dataset.id;
        const url = el.dataset.url;

        // Optimistically mark as read in UI
        el.classList.remove('unread');
        const dot = el.querySelector('.noti-unread-dot');
        if (dot) dot.remove();

        try {
            await fetch(READ_URL_BASE.replace('__ID__', id), {
                method: 'POST',
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': CSRF(),
                },
            });
        } catch (err) {
            // Silent fail
        }

        // Refresh badge count
        pollUnreadCount();

        // Navigate
        if (url) {
            window.location.href = url;
        }
    };

    // ── Mark all as read ──────────────────────────────────────────

    if (markAll) {
        markAll.addEventListener('click', async function () {
            try {
                await fetch(MARK_ALL_URL, {
                    method: 'POST',
                    headers: {
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': CSRF(),
                    },
                });
                // Re-fetch to update UI
                fetchNotifications();
                updateBadge(0);
            } catch (err) {
                // Silent fail
            }
        });
    }

    // ── Utility ───────────────────────────────────────────────────

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

})();