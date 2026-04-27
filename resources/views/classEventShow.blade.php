@extends('layouts.app')

@section('title', $classEvent->title)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/classEventShow.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('class.event.browse') }}">Classes & Events</a>
        <span class="sep">/</span>
        <span class="cur">{{ Str::limit($classEvent->title, 40) }}</span>
    </div>
</div>

{{-- Back button --}}
<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="{{ route('class.event.browse') }}" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Browse
    </a>
</div>

<div class="show-page">

    {{-- ══ TWO COLUMN: big poster left, all info right ══ --}}
    <div class="two-col">

        {{-- ── LEFT: Poster only ── --}}
        <div class="left-col">
            <div class="poster-card">
                @if($classEvent->poster_image)
                    <img src="{{ asset('storage/' . $classEvent->poster_image) }}"
                         alt="{{ $classEvent->title }}"
                         class="poster-img"
                         onload="this.style.opacity='1';"
                         style="opacity:0;transition:opacity .3s;">
                @else
                    <div class="poster-placeholder">
                        <i class="fas fa-image"></i>
                        <p>No Image Available</p>
                    </div>
                @endif
                <span class="type-badge type-{{ $classEvent->media_type }}">
                    @if($classEvent->media_type == 'online') 🖥️ Online Class
                    @else 📍 Physical Event @endif
                </span>
            </div>
        </div>

        {{-- ── RIGHT: All info ── --}}
        <div class="right-col">

            {{-- Title + instructor card --}}
            <div class="sp-card">
                <div class="sp-card-header">
                    <div class="sp-card-header-left">
                        <div class="hline"></div>
                        Class Details
                    </div>
                </div>
                <div class="sp-card-body">

                    <h1 class="class-title">{{ $classEvent->title }}</h1>

                    <div class="artist-row">
                        <div class="artist-avatar">
                            @if($classEvent->user && $classEvent->user->profile_image)
                                <img src="{{ asset('storage/' . $classEvent->user->profile_image) }}"
                                     alt="{{ $classEvent->user->fullname }}"
                                     onerror="this.style.display='none'; this.parentElement.textContent='{{ strtoupper(substr($classEvent->user->fullname ?? 'U', 0, 1)) }}';">
                            @else
                                {{ strtoupper(substr($classEvent->user->fullname ?? $classEvent->user->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <div class="artist-details">
                            <div class="artist-name">{{ $classEvent->user->fullname ?? $classEvent->user->name ?? 'Unknown Artist' }}</div>
                            <div class="artist-role">Instructor</div>
                        </div>
                    </div>

                    @if($classEvent->description)
                    <div class="desc-section">
                        <div class="desc-label">About This Class</div>
                        <p class="desc-content">{{ $classEvent->description }}</p>
                    </div>
                    @endif

                </div>
            </div>

            {{-- Enrollment card --}}
            <div class="sp-card">
                <div class="sp-card-header">
                    <div class="sp-card-header-left">
                        <div class="hline"></div>
                        Enrollment
                    </div>
                </div>
                <div class="sp-card-body">

                    <div class="price-row">
                        <i class="fas fa-tag"></i>
                        <span class="price-label">Enrollment Fee</span>
                        @if($classEvent->is_paid && $classEvent->price > 0)
                            <span class="price-amount">RM {{ number_format($classEvent->price, 2) }}</span>
                        @else
                            <span class="price-free">Free</span>
                        @endif
                    </div>

                    <div class="participant-row">
                        <i class="fas fa-users"></i>
                        <span id="participantCountDisplay">
                            {{ $classEvent->bookings_count }} participant{{ $classEvent->bookings_count != 1 ? 's' : '' }} enrolled
                        </span>
                    </div>

                    <div class="info-list">

                        <div class="info-item">
                            <div class="info-icon"><i class="far fa-calendar-alt"></i></div>
                            <div class="info-content">
                                <span class="info-label">Date</span>
                                <span class="info-value">
                                    {{ \Carbon\Carbon::parse($classEvent->start_date)->format('M d, Y') }}
                                    @if($classEvent->start_date != $classEvent->end_date)
                                        – {{ \Carbon\Carbon::parse($classEvent->end_date)->format('M d, Y') }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon"><i class="far fa-clock"></i></div>
                            <div class="info-content">
                                <span class="info-label">Time</span>
                                <span class="info-value">
                                    {{ \Carbon\Carbon::parse($classEvent->start_time)->format('g:i A') }} –
                                    {{ \Carbon\Carbon::parse($classEvent->end_time)->format('g:i A') }}
                                </span>
                            </div>
                        </div>

                        @if($classEvent->duration_weeks)
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-calendar-week"></i></div>
                            <div class="info-content">
                                <span class="info-label">Duration</span>
                                <span class="info-value">{{ $classEvent->duration_weeks }} {{ $classEvent->duration_weeks == 1 ? 'week' : 'weeks' }}</span>
                            </div>
                        </div>
                        @endif

                        @if($classEvent->duration_hours || $classEvent->duration_minutes)
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-hourglass-half"></i></div>
                            <div class="info-content">
                                <span class="info-label">Session Duration</span>
                                <span class="info-value">{{ $classEvent->duration_text }}</span>
                            </div>
                        </div>
                        @endif

                        @if($classEvent->cancellation_deadline)
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-ban"></i></div>
                            <div class="info-content">
                                <span class="info-label">Cancellation Deadline</span>
                                <span class="info-value">
                                    {{ $classEvent->formatted_cancellation_deadline }}
                                    @if(!$classEvent->is_cancellation_open)
                                        <span class="deadline-badge deadline-closed">Closed</span>
                                    @elseif(\Carbon\Carbon::parse($classEvent->cancellation_deadline)->diffInDays(now()) <= 3 && $classEvent->is_cancellation_open)
                                        <span class="deadline-badge deadline-soon">{{ (int)\Carbon\Carbon::now()->diffInDays($classEvent->cancellation_deadline) }}d left</span>
                                    @else
                                        <span class="deadline-badge deadline-open">Open</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif

                        @if($classEvent->max_participants)
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-users"></i></div>
                            <div class="info-content">
                                <span class="info-label">Spots</span>
                                @if($classEvent->spots_remaining === 0 && !$isEnrolled)
                                    <span class="info-value" style="color:var(--danger);font-weight:700;">
                                        <i class="fas fa-user-slash"></i> Fully Booked
                                    </span>
                                @else
                                    <span class="info-value">{{ $classEvent->spots_remaining }} / {{ $classEvent->max_participants }} spots left</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if($classEvent->enrollment_deadline)
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-user-clock"></i></div>
                            <div class="info-content">
                                <span class="info-label">Enrollment Deadline</span>
                                <span class="info-value">
                                    {{ $classEvent->formatted_deadline }}
                                    @if(!$classEvent->is_enrollment_open)
                                        <span class="deadline-badge deadline-closed">Closed</span>
                                    @elseif($classEvent->days_until_deadline <= 3)
                                        <span class="deadline-badge deadline-soon">{{ $classEvent->days_until_deadline }}d left</span>
                                    @else
                                        <span class="deadline-badge deadline-open">Open</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif

                        <div class="info-item">
                            @if($classEvent->media_type == 'online')
                                <div class="info-icon"><i class="fas fa-laptop"></i></div>
                                <div class="info-content">
                                    <span class="info-label">Platform</span>
                                    <span class="info-value">{{ $classEvent->platform ?? 'To Be Announced' }}</span>
                                </div>
                            @else
                                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div class="info-content">
                                    <span class="info-label">Location</span>
                                    <span class="info-value">{{ $classEvent->location ?? 'To Be Announced' }}</span>
                                </div>
                            @endif
                        </div>

                    </div>

                    <div class="action-area">
                        @if($isOwner)
                            <div class="info-box info-box-owner">
                                <i class="fas fa-crown"></i>
                                You are the instructor of this class/event.
                            </div>
                        @elseif($classEvent->is_full && !$isEnrolled)
                            <div class="info-box info-box-danger">
                                <i class="fas fa-users-slash"></i>
                                This class/event is fully booked ({{ $classEvent->max_participants }}/{{ $classEvent->max_participants }} participants).
                            </div>
                        @elseif(!$classEvent->is_enrollment_open && !$isEnrolled)
                            <div class="info-box info-box-danger">
                                <i class="fas fa-lock"></i>
                                Enrollment is closed.
                                @if($classEvent->enrollment_deadline) The deadline was {{ $classEvent->formatted_deadline }}. @endif
                            </div>
                        @else
                            <button
                                class="btn-enroll {{ $isEnrolled ? 'btn-enrolled' : ($classEvent->is_paid && $classEvent->price > 0 ? 'btn-paid' : '') }}"
                                id="enrollBtn"
                                onclick="handleEnroll()"
                                data-event-id="{{ $classEvent->id }}"
                                data-enrolled="{{ $isEnrolled ? 'true' : 'false' }}"
                                data-form-url="{{ $classEvent->enrollment_form_url ?? '' }}"
                                data-cancellation-open="{{ $classEvent->is_cancellation_open ? 'true' : 'false' }}"
                                data-cancellation-deadline="{{ $classEvent->formatted_cancellation_deadline ?? '' }}"
                                data-is-paid="{{ $classEvent->is_paid && $classEvent->price > 0 ? 'true' : 'false' }}"
                                data-checkout-url="{{ route('class.checkout.show', $classEvent->id) }}">
                                @if($isEnrolled)
                                    <i class="fas fa-user-check" id="enrollIcon"></i>
                                    <span id="enrollText">Enrolled — Cancel</span>
                                @elseif($classEvent->is_paid && $classEvent->price > 0)
                                    <i class="fas fa-lock" id="enrollIcon"></i>
                                    <span id="enrollText">Pay & Enroll — RM {{ number_format($classEvent->price, 2) }}</span>
                                @else
                                    <i class="fas fa-user-plus" id="enrollIcon"></i>
                                    <span id="enrollText">
                                        @if($classEvent->requires_form) Enroll via Form @else Enroll Now @endif
                                    </span>
                                @endif
                            </button>
                            @if(!$isEnrolled && $classEvent->requires_form && !($classEvent->is_paid && $classEvent->price > 0))
                            <p class="form-hint"><i class="fas fa-info-circle"></i> An enrollment form will open in a new tab after you enroll.</p>
                            @endif
                        @endif

                        <button class="btn-contact" onclick="handleContact()">
                            <i class="fas fa-envelope"></i> Contact Instructor
                        </button>
                    </div>

                </div>
            </div>

        </div>{{-- /right-col --}}

    </div>{{-- /two-col --}}

</div>{{-- /show-page --}}

<script>
const EVENT_ID     = {{ $classEvent->id }};
const CSRF_TOKEN   = '{{ csrf_token() }}';
const ENROLL_URL   = '{{ route("class.event.enroll", $classEvent->id) }}';
const UNENROLL_URL = '{{ route("class.event.unenroll", $classEvent->id) }}';

function showToast(message, type) {
    const existing = document.getElementById('ceShowToast');
    if (existing) existing.remove();

    const bg   = type === 'success' ? '#10b981' : '#ef4444';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

    const toast = document.createElement('div');
    toast.id = 'ceShowToast';
    toast.style.cssText = [
        'position:fixed','top:80px','right:20px','z-index:99999',
        'background:'+bg,'color:#fff','padding:12px 20px',
        'border-radius:10px','font-size:13px','font-weight:600',
        'display:flex','align-items:center','gap:10px',
        'box-shadow:0 8px 24px rgba(0,0,0,.18)',
        'min-width:280px','max-width:400px','line-height:1.4',
        'transition:opacity .4s,transform .4s',
        'opacity:0','transform:translateY(-10px)'
    ].join(';');
    toast.innerHTML = '<i class="fas '+icon+'"></i><span>'+message+'</span>';
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

async function handleEnroll() {
    const btn         = document.getElementById('enrollBtn');
    const iconEl      = document.getElementById('enrollIcon');
    const textEl      = document.getElementById('enrollText');
    const isEnrolled  = btn.dataset.enrolled === 'true';
    const isPaid      = btn.dataset.isPaid === 'true';
    const checkoutUrl = btn.dataset.checkoutUrl || '';
    const formUrl     = btn.dataset.formUrl || '';

    if (isPaid && !isEnrolled) { window.location.href = checkoutUrl; return; }

    if (isEnrolled) {
        const cancellationOpen     = btn.dataset.cancellationOpen !== 'false';
        const cancellationDeadline = btn.dataset.cancellationDeadline || '';
        if (!cancellationOpen) {
            showToast('Cancellation is no longer allowed. The deadline was ' + cancellationDeadline + '.', 'error');
            return;
        }
        if (isPaid) {
            const confirmed = confirm('Are you sure you want to cancel?\n\nA refund will be issued to your original payment method.');
            if (!confirmed) return;
        }
    }

    btn.disabled       = true;
    iconEl.className   = 'fas fa-spinner fa-spin';
    textEl.textContent = isEnrolled ? 'Cancelling...' : 'Enrolling...';

    try {
        const response = await fetch(isEnrolled ? UNENROLL_URL : ENROLL_URL, {
            method: isEnrolled ? 'DELETE' : 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json',
                'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json();

        if (data.requires_payment && data.redirect) { window.location.href = data.redirect; return; }

        if (data.success) {
            const nowEnrolled = data.is_enrolled;
            setEnrollButton(nowEnrolled, !!formUrl, isPaid);
            updateParticipantCount(data.participant_count);
            if (nowEnrolled && formUrl) {
                showToast('Enrolled! Please complete the enrollment form that just opened.', 'success');
                setTimeout(() => window.open(formUrl, '_blank'), 400);
            } else {
                showToast(data.message, 'success');
            }
        } else {
            setEnrollButton(isEnrolled, !!formUrl, isPaid);
            showToast(data.message || 'Something went wrong. Please try again.', 'error');
        }
    } catch (err) {
        setEnrollButton(isEnrolled, !!formUrl, isPaid);
        showToast('Network error. Please check your connection and try again.', 'error');
    } finally {
        btn.disabled = false;
    }
}

function setEnrollButton(enrolled, hasForm, isPaid) {
    const btn    = document.getElementById('enrollBtn');
    const iconEl = document.getElementById('enrollIcon');
    const textEl = document.getElementById('enrollText');
    if (!btn) return;
    btn.dataset.enrolled = enrolled ? 'true' : 'false';
    if (enrolled) {
        btn.className = 'btn-enroll btn-enrolled';
        iconEl.className = 'fas fa-user-check';
        textEl.textContent = 'Enrolled — Cancel';
    } else if (isPaid) {
        btn.className = 'btn-enroll btn-paid';
        iconEl.className = 'fas fa-lock';
        textEl.textContent = 'Pay & Enroll';
    } else {
        btn.className = 'btn-enroll';
        iconEl.className = 'fas fa-user-plus';
        textEl.textContent = hasForm ? 'Enroll via Form' : 'Enroll Now';
    }
}

function updateParticipantCount(count) {
    const el = document.getElementById('participantCountDisplay');
    if (el) el.textContent = count + ' participant' + (count !== 1 ? 's' : '') + ' enrolled';
}

function handleContact() {
    alert('Contact feature coming soon!');
}
</script>

@endsection