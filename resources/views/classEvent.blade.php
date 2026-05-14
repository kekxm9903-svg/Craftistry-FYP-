@extends('layouts.app')

@section('title', 'Classes & Events - Craftistry')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/classEvent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/classEventParticipants.css') }}">
    <style>
        /* ── Craftistry-styled delete confirm modal ── */
        #deleteModal .modal-content {
            max-width: 420px !important;
        }
        .delete-confirm-body { padding: 36px 32px 28px; text-align: center; }
        .delete-modal-icon-wrap {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #fff5f5, #fed7d7);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            border: 2px solid #fca5a5;
            box-shadow: 0 4px 12px rgba(239,68,68,.15);
        }
        .delete-modal-icon-wrap i { color: #ef4444; font-size: 1.45rem; }
        .delete-modal-title  { font-size: 1.15rem; font-weight: 800; color: #1a202c; margin-bottom: 8px; }
        .delete-modal-name   {
            font-size: 0.88rem; font-weight: 600; color: #667eea;
            background: #ede9fe; border-radius: 6px;
            padding: 6px 14px; display: inline-block;
            margin-bottom: 10px; max-width: 100%;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .delete-modal-warning { font-size: 0.82rem; color: #718096; line-height: 1.6; margin-bottom: 0; }
        .delete-modal-btns { display: flex; gap: 10px; margin-top: 24px; }
        .delete-btn-cancel {
            flex: 1; padding: 12px; border-radius: 8px;
            border: 1.5px solid #e2e8f0; background: #fff;
            color: #4a5568; font-size: 0.88rem; font-weight: 600;
            cursor: pointer; font-family: 'Inter', sans-serif; transition: all .15s;
        }
        .delete-btn-cancel:hover { background: #f7fafc; border-color: #cbd5e0; }
        .delete-btn-confirm {
            flex: 1; padding: 12px; border-radius: 8px; border: none;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff; font-size: 0.88rem; font-weight: 700;
            cursor: pointer; font-family: 'Inter', sans-serif; transition: all .15s;
            box-shadow: 0 4px 14px rgba(239,68,68,.35);
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .delete-btn-confirm:hover { opacity: .88; transform: translateY(-1px); }
        @keyframes ceDeleteIn {
            from { opacity:0; transform:scale(.88) translateY(16px); }
            to   { opacity:1; transform:scale(1) translateY(0); }
        }
        #deleteModal.active .modal-content { animation: ceDeleteIn .22s cubic-bezier(.34,1.56,.64,1); }
    </style>
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <span class="cur">Classes & Events</span>
    </div>
</div>

<main class="main">

    <a href="javascript:history.back()" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back
    </a>

    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">Classes & Events</div>
            <div class="page-subtitle">Manage and organize your creative workshops and events</div>
        </div>
        <a href="{{ route('class.event.create') }}" class="upload-btn">
            <i class="bi bi-upload"></i>
            Upload
        </a>
    </div>

    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                My Classes & Events
            </div>
            @if($classEvents->total() > 0)
                <span class="section-count">{{ $classEvents->total() }} event{{ $classEvents->total() !== 1 ? 's' : '' }}</span>
            @endif
        </div>

        <div class="sp-card-body">
            <div class="class-grid" id="classGrid">
                @forelse($classEvents as $event)
                <div class="class-card" data-id="{{ $event->id }}">

                    <div class="class-image-wrapper">
                        <div class="class-image" style="background-image: url('{{ $event->poster_url }}');">
                            @if(!$event->poster_image)
                                <i class="bi bi-mortarboard-fill"></i>
                            @endif
                        </div>
                        <div class="action-buttons-overlay">
                            <button class="btn-action btn-participants"
                                    onclick="viewParticipants({{ $event->id }}, '{{ addslashes($event->title) }}')"
                                    title="View Participants">
                                <i class="bi bi-people-fill"></i>
                            </button>
                            <a class="btn-action btn-edit"
                               href="{{ route('class.event.edit', $event->id) }}"
                               title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <button class="btn-action btn-delete"
                                    onclick="confirmDelete({{ $event->id }}, '{{ addslashes($event->title) }}')"
                                    title="Delete">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="class-content">
                        <h3 class="class-title">{{ $event->title }}</h3>

                        <div class="card-badges-row">
                            @if($event->is_paid)
                                <span class="ce-pill ce-pill-paid"><i class="bi bi-tag-fill"></i> RM {{ number_format($event->price, 2) }}</span>
                            @else
                                <span class="ce-pill ce-pill-free"><i class="bi bi-gift-fill"></i> Free</span>
                            @endif
                            <span class="ce-pill ce-pill-parts"><i class="bi bi-people-fill"></i> {{ $event->bookings_count }}/{{ $event->max_participants ?? '∞' }}</span>
                        </div>

                        <div class="class-meta">
                            <div class="class-meta-item">
                                <i class="bi {{ $event->media_type === 'online' ? 'bi-laptop' : 'bi-geo-alt-fill' }}"></i>
                                <span class="meta-label">{{ ucfirst($event->media_type) }}</span>
                                <span class="meta-value">{{ $event->media_location }}</span>
                            </div>
                            <div class="class-meta-item">
                                <i class="bi bi-calendar-event-fill"></i>
                                <span class="meta-label">Date</span>
                                <span class="meta-value">{{ $event->formatted_date_range }}</span>
                            </div>
                            <div class="class-meta-item">
                                <i class="bi bi-clock-fill"></i>
                                <span class="meta-label">Time</span>
                                <span class="meta-value">
                                    {{ $event->formatted_time_range }}
                                    @if($event->duration_text)
                                        <span class="meta-duration">({{ $event->duration_text }})</span>
                                    @endif
                                </span>
                            </div>
                            @if($event->enrollment_deadline)
                            <div class="class-meta-item">
                                <i class="bi bi-hourglass-bottom"></i>
                                <span class="meta-label">Enroll by</span>
                                <span class="meta-value">{{ \Carbon\Carbon::parse($event->enrollment_deadline)->format('d M Y') }}</span>
                            </div>
                            @endif
                            @if($event->description)
                            <div class="class-meta-item class-meta-desc">
                                <i class="bi bi-text-left"></i>
                                <span class="meta-value meta-desc-text">{{ $event->description }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-mortarboard-fill"></i></div>
                    <h3 class="empty-title">No Classes or Events Yet</h3>
                    <p class="empty-description">Upload your classes and events to showcase your creative workshops and art events.</p>
                    <a href="{{ route('class.event.create') }}" class="btn-empty">
                        <i class="bi bi-plus-lg"></i>
                        Upload Your First Class/Event
                    </a>
                </div>
                @endforelse
            </div>

            @if($classEvents->hasPages())
            <div class="pagination-wrapper">
                {{ $classEvents->links() }}
            </div>
            @endif
        </div>
    </div>

</main>


{{-- ══ CRAFTISTRY-STYLED DELETE CONFIRM MODAL ══ --}}
<div class="modal modal-sm" id="deleteModal">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-content">
        <div class="delete-confirm-body">
            <div class="delete-modal-icon-wrap">
                <i class="bi bi-trash-fill"></i>
            </div>
            <h3 class="delete-modal-title">Delete Class/Event?</h3>
            <div class="delete-modal-name" id="deleteClassName"></div>
            <p class="delete-modal-warning">This action cannot be undone. All enrollments will also be removed.</p>
            <div class="delete-modal-btns">
                <button class="delete-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="delete-btn-confirm" onclick="deleteClass()">
                    <i class="bi bi-trash-fill"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ══ PARTICIPANTS MODAL ══ --}}
<div class="modal" id="participantsModal">
    <div class="modal-overlay" onclick="closeParticipantsModal()"></div>
    <div class="modal-content participants-modal-content">
        <div class="modal-header" style="align-items:flex-start;">
            <div class="participants-modal-title-group">
                <h2 class="modal-title">
                    <i class="bi bi-people-fill"></i> Participants
                </h2>
                <p class="participants-modal-subtitle" id="participantsEventTitle"></p>
            </div>
            <button class="modal-close" onclick="closeParticipantsModal()" style="margin-top:2px; flex-shrink:0;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="participants-summary">
                <div class="participants-summary-stat">
                    <span class="participants-summary-number" id="participantsTotalCount">0</span>
                    <span class="participants-summary-label">Total Participants</span>
                </div>
            </div>
            <div class="participants-loading" id="participantsLoading">
                <div class="participants-spinner"></div>
                <p>Loading participants...</p>
            </div>
            <div class="participants-empty" id="participantsEmpty" style="display:none;">
                <div class="participants-empty-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <h3>No Participants Yet</h3>
                <p id="participantsEmptyMsg">No one has booked this class/event yet.</p>
            </div>
            <div class="participants-table-wrapper" id="participantsTableWrapper" style="display:none;">
                <table class="participants-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Booking Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="participantsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- ══ SUCCESS / DELETE POPUPS — exact artistProfile style ══ --}}
<div class="success-popup" id="successPopup">
    <div class="success-content">
        <div class="success-icon"><i class="fas fa-check-circle"></i></div>
        <div><p id="successMessage">Success!</p></div>
    </div>
</div>

<div class="delete-popup" id="deletePopup">
    <div class="delete-content">
        <div class="delete-icon"><i class="fas fa-times-circle"></i></div>
        <div><p id="deleteMessage">Something went wrong.</p></div>
    </div>
</div>

</div>

@endsection

@section('scripts')
    <script>
    // ── Popup helpers — exact same pattern as artistProfile.js ──
    function showSuccessPopup(message) {
        const popup     = document.getElementById('successPopup');
        const messageEl = document.getElementById('successMessage');
        if (popup && messageEl) {
            messageEl.textContent = message;
            popup.classList.add('show');
            setTimeout(() => {
                popup.classList.add('hide');
                setTimeout(() => popup.classList.remove('show', 'hide'), 300);
            }, 3000);
        }
    }

    function showDeletePopup(message) {
        const popup     = document.getElementById('deletePopup');
        const messageEl = document.getElementById('deleteMessage');
        if (popup && messageEl) {
            messageEl.textContent = message;
            popup.classList.add('show');
            setTimeout(() => {
                popup.classList.add('hide');
                setTimeout(() => popup.classList.remove('show', 'hide'), 300);
            }, 3000);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showSuccessPopup(@json(session('success')));
        @endif
        @if(session('error'))
            showDeletePopup(@json(session('error')));
        @endif
    });
    </script>
    <script>
        const csrfToken             = "{{ csrf_token() }}";
        const participantsBaseRoute = "{{ url('class-event') }}";
        const indexRoute            = "{{ route('class.event.index') }}";
    </script>
    <script>
    // Bootstrap.Modal shim — this page doesn't load Bootstrap JS.
    // classEventParticipants.js calls bootstrap.Modal.getOrCreateInstance().show()
    // and bootstrap.Modal.getInstance().hide() — we override both with .active class toggle.
    window.bootstrap = {
        Modal: {
            getOrCreateInstance: function(el) {
                return {
                    show: function() {
                        el.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    }
                };
            },
            getInstance: function(el) {
                return {
                    hide: function() {
                        el.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                };
            }
        }
    };
    </script>
    <script src="{{ asset('js/classEventDeleteIndex.js') }}"></script>
    <script src="{{ asset('js/classEventParticipants.js') }}"></script>
@endsection