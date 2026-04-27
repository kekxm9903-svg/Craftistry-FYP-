/**
 * classEventParticipants.js
 * Handles the participants modal — open, fetch, render, close.
 */

// ── Open modal & fetch participants ─────────────────────────────────────────

function viewParticipants(eventId, eventTitle) {
    // Reset state
    document.getElementById('participantsEventTitle').textContent = eventTitle;
    document.getElementById('participantsTotalCount').textContent  = '0';
    document.getElementById('participantsLoading').style.display   = 'flex';
    document.getElementById('participantsEmpty').style.display     = 'none';
    document.getElementById('participantsTableWrapper').style.display = 'none';
    document.getElementById('participantsTableBody').innerHTML     = '';
    document.getElementById('participantsEmptyMsg').textContent    = 'No one has booked this class/event yet.';

    // Store current event ID on modal element for drop function
    document.getElementById('participantsModal').dataset.eventId = eventId;

    // Open modal
    document.getElementById('participantsModal').classList.add('active');
    document.body.style.overflow = 'hidden';

    // Fetch from controller
    fetch(`${participantsBaseRoute}/${eventId}/participants`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept':           'application/json',
        },
    })
    .then(function(response) {
        if (!response.ok) throw new Error('Failed to fetch participants');
        return response.json();
    })
    .then(function(data) {
        if (!data.success) throw new Error(data.message || 'Error loading participants');
        renderParticipants(data);
    })
    .catch(function(error) {
        console.error('Participants fetch error:', error);
        document.getElementById('participantsLoading').style.display = 'none';
        document.getElementById('participantsEmpty').style.display   = 'flex';
        document.getElementById('participantsEmptyMsg').textContent  = 'Failed to load participants. Please try again.';
    });
}

// ── Render participants into the table ───────────────────────────────────────

function renderParticipants(data) {
    var loading      = document.getElementById('participantsLoading');
    var empty        = document.getElementById('participantsEmpty');
    var tableWrapper = document.getElementById('participantsTableWrapper');
    var tbody        = document.getElementById('participantsTableBody');
    var totalCount   = document.getElementById('participantsTotalCount');

    loading.style.display = 'none';
    totalCount.textContent = data.participant_count;

    if (data.participant_count === 0) {
        empty.style.display        = 'flex';
        tableWrapper.style.display = 'none';
        return;
    }

    empty.style.display        = 'none';
    tableWrapper.style.display = 'block';

    tbody.innerHTML = data.participants.map(function(p, index) {
        return (
            '<tr id="participant-row-' + p.booking_id + '">' +
                '<td class="participants-row-number">' + (index + 1) + '</td>' +
                '<td>' +
                    '<div class="participants-name-cell">' +
                        '<div class="participants-avatar">' + getInitials(p.name) + '</div>' +
                        '<span>' + escapeHtml(p.name) + '</span>' +
                    '</div>' +
                '</td>' +
                '<td class="participants-email-cell">' + escapeHtml(p.email) + '</td>' +
                '<td class="participants-date-cell">' +
                    '<i class="fas fa-calendar-check"></i>' +
                    escapeHtml(p.booked_at) +
                '</td>' +
                '<td class="participants-action-cell">' +
                    '<button class="btn-drop-participant" ' +
                        'onclick="dropParticipant(' + p.booking_id + ', \'' + escapeHtml(p.name) + '\')" ' +
                        'title="Remove participant">' +
                        '<i class="fas fa-user-minus"></i>' +
                    '</button>' +
                '</td>' +
            '</tr>'
        );
    }).join('');
}

// ── Close modal ──────────────────────────────────────────────────────────────

function closeParticipantsModal() {
    document.getElementById('participantsModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Close on Escape key (add alongside existing Escape handler in classEvent.js)
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeParticipantsModal();
    }
});

// ── Helpers ──────────────────────────────────────────────────────────────────

function getInitials(name) {
    if (!name) return '?';
    var parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,  '&amp;')
        .replace(/</g,  '&lt;')
        .replace(/>/g,  '&gt;')
        .replace(/"/g,  '&quot;')
        .replace(/'/g,  '&#39;');
}


// ── Drop a participant (artist only) ────────────────────────────────────────

async function dropParticipant(bookingId, participantName) {
    if (!confirm('Remove ' + participantName + ' from this class/event?')) return;

    const modal   = document.getElementById('participantsModal');
    const eventId = modal.dataset.eventId;
    const row     = document.getElementById('participant-row-' + bookingId);
    const btn     = row ? row.querySelector('.btn-drop-participant') : null;

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    try {
        const response = await fetch(participantsBaseRoute + '/' + eventId + '/participants/' + bookingId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN':     csrfToken,
                'Accept':           'application/json',
                'Content-Type':     'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json();

        if (data.success) {
            // Fade out and remove the row
            if (row) {
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity    = '0';
                row.style.transform  = 'translateX(20px)';
                setTimeout(function() {
                    row.remove();
                    // Re-number remaining rows
                    var rows = document.querySelectorAll('#participantsTableBody tr');
                    rows.forEach(function(r, i) {
                        var numCell = r.querySelector('.participants-row-number');
                        if (numCell) numCell.textContent = i + 1;
                    });
                    // Update total count
                    document.getElementById('participantsTotalCount').textContent = data.participant_count;
                    // Show empty state if no participants left
                    if (data.participant_count === 0) {
                        document.getElementById('participantsTableWrapper').style.display = 'none';
                        document.getElementById('participantsEmpty').style.display        = 'flex';
                        document.getElementById('participantsEmptyMsg').textContent       = 'No one has booked this class/event yet.';
                    }
                    // Also update the participant count badge on the card
                    var card = document.querySelector('.class-card[data-id="' + eventId + '"]');
                    if (card) {
                        var badge = card.querySelector('.participant-count-badge span');
                        if (badge) {
                            var n = data.participant_count;
                            badge.textContent = n + ' participant' + (n !== 1 ? 's' : '');
                        }
                    }
                }, 300);
            }
        } else {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-user-minus"></i>';
            }
            alert(data.message || 'Failed to remove participant.');
        }
    } catch (err) {
        console.error('Drop participant error:', err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-minus"></i>';
        }
        alert('Network error. Please try again.');
    }
}

// Export
window.viewParticipants       = viewParticipants;
window.closeParticipantsModal = closeParticipantsModal;