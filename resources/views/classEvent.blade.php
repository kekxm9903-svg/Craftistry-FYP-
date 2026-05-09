@extends('layouts.app')

@section('title', 'Classes & Events - Craftistry')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/classEvent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/classEventParticipants.css') }}">
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

    @if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle-fill"></i>
        {{ session('error') }}
    </div>
    @endif

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
            <div class="class-grid">
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


{{-- ══ DELETE MODAL ══ --}}
<div class="modal modal-sm" id="deleteModal">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-content">
        <div class="modal-body delete-confirm-body">
            <div class="delete-modal-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <h3 class="delete-modal-title">Delete Class/Event?</h3>
            <p class="delete-modal-name" id="deleteClassName"></p>
            <p class="delete-modal-warning">This action cannot be undone.</p>
            <div class="form-actions" style="margin-top:1.5rem;">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn-delete-confirm" onclick="deleteClass()">
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
        <div class="modal-header">
            <div class="participants-modal-title-group">
                <h2 class="modal-title">
                    <i class="bi bi-people-fill"></i> Participants
                </h2>
                <p class="participants-modal-subtitle" id="participantsEventTitle"></p>
            </div>
            <button class="modal-close" onclick="closeParticipantsModal()">
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
                <div class="participants-empty-icon"><i class="bi bi-person-slash"></i></div>
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


{{-- ══ SUCCESS / ERROR POPUPS ══ --}}
<div class="success-popup" id="successPopup">
    <div class="success-content">
        <div class="success-icon"><i class="bi bi-check-lg"></i></div>
        <p id="successPopupMsg">Done!</p>
    </div>
</div>
<div class="error-popup" id="errorNotification">
    <div class="error-content">
        <div class="error-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
        <p id="errorPopupMsg">Something went wrong.</p>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        const csrfToken             = "{{ csrf_token() }}";
        const participantsBaseRoute = "{{ url('class-event') }}";
        const indexRoute            = "{{ route('class.event.index') }}";
    </script>
    <script src="{{ asset('js/classEventDeleteIndex.js') }}"></script>
    <script src="{{ asset('js/classEventParticipants.js') }}"></script>
@endsection