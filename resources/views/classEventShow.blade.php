@extends('layouts.app')

@section('title', $classEvent->title)

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('css/classEventShow.css') }}">
<style>
/* ── Form Modal ── */
.form-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s;
}
.form-modal-overlay.open {
    opacity: 1;
    pointer-events: all;
}
.form-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    overflow: hidden;
    transform: translateY(20px);
    transition: transform .25s;
}
.form-modal-overlay.open .form-modal {
    transform: translateY(0);
}
.form-modal-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.form-modal-header h3 {
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-modal-close {
    background: rgba(255,255,255,.2);
    border: none;
    color: #fff;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: background .15s;
}
.form-modal-close:hover { background: rgba(255,255,255,.35); }
.form-modal-body { padding: 24px; }
.form-modal-body p {
    font-size: 13px;
    color: #6b6b8a;
    line-height: 1.6;
    margin-bottom: 20px;
}
.form-modal-body strong { color: #1a1a2e; }
.form-steps {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}
.form-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 14px;
    background: #f8f7ff;
    border-radius: 10px;
    border: 1px solid #e0e0ee;
}
.form-step-num {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.form-step-text {
    font-size: 13px;
    color: #1a1a2e;
    line-height: 1.4;
    padding-top: 3px;
}
.form-modal-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.btn-open-form {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: opacity .15s;
}
.btn-open-form:hover { opacity: .88; }
.btn-confirm-enroll {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: opacity .15s;
    opacity: .45;
    pointer-events: none;
}
.btn-confirm-enroll.enabled {
    opacity: 1;
    pointer-events: all;
}
.btn-confirm-enroll:hover { opacity: .88; }
.form-confirm-check {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 14px;
    background: #f0fdf4;
    border: 1.5px solid #bbf7d0;
    border-radius: 10px;
    cursor: pointer;
    user-select: none;
}
.form-confirm-check input[type="checkbox"] {
    width: 16px;
    height: 16px;
    margin-top: 2px;
    flex-shrink: 0;
    cursor: pointer;
    accent-color: #16a34a;
}
.form-confirm-check label {
    font-size: 12px;
    color: #166534;
    font-weight: 600;
    cursor: pointer;
    line-height: 1.4;
}
.btn-cancel-modal {
    width: 100%;
    padding: 10px;
    background: none;
    border: 1.5px solid #e0e0ee;
    border-radius: 10px;
    font-size: 13px;
    color: #6b6b8a;
    font-weight: 600;
    cursor: pointer;
    transition: border-color .15s, color .15s;
}
.btn-cancel-modal:hover {
    border-color: #667eea;
    color: #667eea;
}

/* ── Confirm modals shared ── */
@keyframes ceModalIn {
    from { opacity:0; transform:scale(.88) translateY(16px); }
    to   { opacity:1; transform:scale(1)   translateY(0); }
}
.ce-confirm-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 99999;
    align-items: center;
    justify-content: center;
}
.ce-confirm-overlay.open { display: flex; }
.ce-confirm-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.48);
    backdrop-filter: blur(3px);
}
.ce-confirm-box {
    position: relative;
    background: #fff;
    border-radius: 16px;
    padding: 36px 32px 28px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 24px 64px rgba(102,126,234,.22), 0 4px 16px rgba(0,0,0,.08);
    text-align: center;
    z-index: 1;
    animation: ceModalIn .22s cubic-bezier(.34,1.56,.64,1);
}
.ce-confirm-icon {
    width: 60px; height: 60px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
}
.ce-confirm-icon.red {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border: 2px solid #fca5a5;
    box-shadow: 0 4px 12px rgba(239,68,68,.15);
}
.ce-confirm-icon.red i { color: #ef4444; font-size: 1.45rem; }
.ce-confirm-title { font-size: 1.15rem; font-weight: 800; color: #1a202c; margin-bottom: 8px; }
.ce-confirm-msg { font-size: 0.84rem; color: #718096; line-height: 1.65; margin-bottom: 28px; }
.ce-confirm-name {
    font-size: 0.88rem; font-weight: 600; color: #667eea;
    background: #ede9fe; border-radius: 6px;
    padding: 4px 12px; display: inline-block;
    margin-bottom: 10px;
}
.ce-confirm-btns { display: flex; gap: 10px; }
.ce-btn-keep {
    flex: 1; padding: 12px; border-radius: 8px;
    border: 1.5px solid #e2e8f0; background: #fff;
    color: #4a5568; font-size: 0.88rem; font-weight: 600;
    cursor: pointer; font-family: 'Inter', sans-serif; transition: all .15s;
}
.ce-btn-keep:hover { background: #f7fafc; border-color: #cbd5e0; }
.ce-btn-danger {
    flex: 1; padding: 12px; border-radius: 8px; border: none;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff; font-size: 0.88rem; font-weight: 700;
    cursor: pointer; font-family: 'Inter', sans-serif;
    box-shadow: 0 4px 14px rgba(239,68,68,.35);
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: all .15s;
}
.ce-btn-danger:hover { opacity: .88; transform: translateY(-1px); }
</style>
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
    <div class="two-col">

        {{-- ── LEFT: Poster + Social Links ── --}}
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

            @if($classEvent->instagram_url || $classEvent->facebook_url || $classEvent->x_url)
            <div class="sp-card" style="margin-top:16px;">
                <div class="sp-card-header">
                    <div class="sp-card-header-left">
                        <div class="hline"></div>
                        Follow &amp; Connect
                    </div>
                </div>
                <div class="sp-card-body">
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @if($classEvent->instagram_url)
                        <a href="{{ $classEvent->instagram_url }}" target="_blank" rel="noopener noreferrer"
                           style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:#fce4ec;text-decoration:none;transition:opacity .15s;"
                           onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                            <span style="width:34px;height:34px;border-radius:8px;background:#e1306c;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fab fa-instagram" style="color:#fff;font-size:1rem;"></i>
                            </span>
                            <div style="min-width:0;">
                                <div style="font-size:.75rem;color:#9e3a5a;font-weight:600;line-height:1;">Instagram</div>
                                <div style="font-size:.82rem;color:#e1306c;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $classEvent->instagram_url }}</div>
                            </div>
                            <i class="fas fa-external-link-alt" style="color:#e1306c;font-size:.75rem;margin-left:auto;flex-shrink:0;"></i>
                        </a>
                        @endif
                        @if($classEvent->facebook_url)
                        <a href="{{ $classEvent->facebook_url }}" target="_blank" rel="noopener noreferrer"
                           style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:#e3f0fd;text-decoration:none;transition:opacity .15s;"
                           onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                            <span style="width:34px;height:34px;border-radius:8px;background:#1877f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fab fa-facebook-f" style="color:#fff;font-size:1rem;"></i>
                            </span>
                            <div style="min-width:0;">
                                <div style="font-size:.75rem;color:#1a5faa;font-weight:600;line-height:1;">Facebook</div>
                                <div style="font-size:.82rem;color:#1877f2;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $classEvent->facebook_url }}</div>
                            </div>
                            <i class="fas fa-external-link-alt" style="color:#1877f2;font-size:.75rem;margin-left:auto;flex-shrink:0;"></i>
                        </a>
                        @endif
                        @if($classEvent->x_url)
                        <a href="{{ $classEvent->x_url }}" target="_blank" rel="noopener noreferrer"
                           style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:#f0f0f0;text-decoration:none;transition:opacity .15s;"
                           onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                            <span style="width:34px;height:34px;border-radius:8px;background:#000;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fab fa-x-twitter" style="color:#fff;font-size:1rem;"></i>
                            </span>
                            <div style="min-width:0;">
                                <div style="font-size:.75rem;color:#333;font-weight:600;line-height:1;">X (Twitter)</div>
                                <div style="font-size:.82rem;color:#555;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $classEvent->x_url }}</div>
                            </div>
                            <i class="fas fa-external-link-alt" style="color:#555;font-size:.75rem;margin-left:auto;flex-shrink:0;"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ── RIGHT: All info ── --}}
        <div class="right-col">

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
                                class="btn-enroll {{ $isEnrolled ? 'btn-enrolled' : (($classEvent->is_paid && $classEvent->price > 0 && !($classEvent->require_form && $classEvent->enrollment_form_url)) ? 'btn-paid' : '') }}"
                                id="enrollBtn"
                                onclick="handleEnroll()"
                                data-event-id="{{ $classEvent->id }}"
                                data-enrolled="{{ $isEnrolled ? 'true' : 'false' }}"
                                data-form-url="{{ $classEvent->enrollment_form_url ?? '' }}"
                                data-require-form="{{ $classEvent->require_form ? 'true' : 'false' }}"
                                data-cancellation-open="{{ $classEvent->is_cancellation_open ? 'true' : 'false' }}"
                                data-cancellation-deadline="{{ $classEvent->formatted_cancellation_deadline ?? '' }}"
                                data-is-paid="{{ $classEvent->is_paid && $classEvent->price > 0 ? 'true' : 'false' }}"
                                data-checkout-url="{{ route('class.checkout.show', $classEvent->id) }}">
                                @if($isEnrolled)
                                    <i class="fas fa-user-check" id="enrollIcon"></i>
                                    <span id="enrollText">Enrolled — Cancel</span>
                                @elseif($classEvent->require_form && $classEvent->enrollment_form_url && $classEvent->is_paid && $classEvent->price > 0)
                                    <i class="fas fa-file-alt" id="enrollIcon"></i>
                                    <span id="enrollText">Fill Form & Pay — RM {{ number_format($classEvent->price, 2) }}</span>
                                @elseif($classEvent->require_form && $classEvent->enrollment_form_url)
                                    <i class="fas fa-file-alt" id="enrollIcon"></i>
                                    <span id="enrollText">Fill Form & Enroll</span>
                                @elseif($classEvent->is_paid && $classEvent->price > 0)
                                    <i class="fas fa-lock" id="enrollIcon"></i>
                                    <span id="enrollText">Pay & Enroll — RM {{ number_format($classEvent->price, 2) }}</span>
                                @else
                                    <i class="fas fa-user-plus" id="enrollIcon"></i>
                                    <span id="enrollText">Enroll Now</span>
                                @endif
                            </button>

                            @if(!$isEnrolled && $classEvent->require_form && $classEvent->enrollment_form_url)
                            <p class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                @if($classEvent->is_paid && $classEvent->price > 0)
                                    An enrollment form is required. Fill it first, then you will be redirected to payment.
                                @else
                                    An enrollment form is required. You must fill it before your enrollment is confirmed.
                                @endif
                            </p>
                            @endif
                        @endif
                    </div>

                </div>
            </div>

        </div>{{-- /right-col --}}
    </div>{{-- /two-col --}}
</div>{{-- /show-page --}}

{{-- ══ Form Required Modal ══ --}}
<div class="form-modal-overlay" id="formModalOverlay">
    <div class="form-modal">
        <div class="form-modal-header">
            <h3><i class="fas fa-file-alt"></i> Enrollment Form Required</h3>
            <button class="form-modal-close" onclick="closeFormModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="form-modal-body">
            <p>The instructor requires you to <strong>fill in the enrollment form</strong> before enrolling in this class. Please complete the form first, then confirm your enrollment below.</p>
            <div class="form-steps">
                <div class="form-step">
                    <div class="form-step-num">1</div>
                    <div class="form-step-text">Click <strong>Open Form</strong> to open the enrollment form in a new tab.</div>
                </div>
                <div class="form-step">
                    <div class="form-step-num">2</div>
                    <div class="form-step-text">Fill in and <strong>submit</strong> the form completely.</div>
                </div>
                <div class="form-step">
                    <div class="form-step-num">3</div>
                    <div class="form-step-text">Come back here, tick the confirmation box, and click <strong>Confirm Enrollment</strong>.</div>
                </div>
            </div>
            <div class="form-modal-actions">
                <button class="btn-open-form" id="btnOpenForm" onclick="openEnrollForm()">
                    <i class="fas fa-external-link-alt"></i> Open Enrollment Form
                </button>
                <div class="form-confirm-check" id="formConfirmCheck" style="display:none;">
                    <input type="checkbox" id="formFilledCheck" onchange="toggleConfirmBtn(this)">
                    <label for="formFilledCheck">I have filled in and submitted the enrollment form.</label>
                </div>
                <button class="btn-confirm-enroll" id="btnConfirmEnroll" onclick="confirmEnrollAfterForm()">
                    <i class="fas fa-check-circle"></i> <span id="confirmBtnText">Confirm Enrollment</span>
                </button>
                <button class="btn-cancel-modal" onclick="closeFormModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>

{{-- ══ Cancel Enrollment Confirm Modal ══ --}}
<div class="ce-confirm-overlay" id="cancelEnrollModal">
    <div class="ce-confirm-backdrop" onclick="closeCancelEnrollModal()"></div>
    <div class="ce-confirm-box">
        <div class="ce-confirm-icon red">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="ce-confirm-title">Cancel Enrollment?</div>
        <div class="ce-confirm-msg">
            Are you sure you want to cancel your enrollment?<br>
            A <strong style="color:#1a202c;">refund</strong> will be issued to your original payment method within 5–10 business days.
        </div>
        <div class="ce-confirm-btns">
            <button class="ce-btn-keep" onclick="closeCancelEnrollModal()">Keep Enrollment</button>
            <button class="ce-btn-danger" onclick="confirmCancelEnrollment()">
                <i class="fas fa-times-circle"></i> Yes, Cancel
            </button>
        </div>
    </div>
</div>

<script>
const EVENT_ID     = {{ $classEvent->id }};
const CSRF_TOKEN   = '{{ csrf_token() }}';
const ENROLL_URL   = '{{ route("class.event.enroll", $classEvent->id) }}';
const UNENROLL_URL = '{{ route("class.event.unenroll", $classEvent->id) }}';

// ── Toast ──────────────────────────────────────────────────────────────
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

// ── Cancel Enrollment Modal ────────────────────────────────────────────
function openCancelEnrollModal() {
    document.getElementById('cancelEnrollModal').classList.add('open');
}
function closeCancelEnrollModal() {
    document.getElementById('cancelEnrollModal').classList.remove('open');
}
function confirmCancelEnrollment() {
    closeCancelEnrollModal();
    doEnrollRequest(true, false, '');
}

// ── Form Modal ─────────────────────────────────────────────────────────
function openFormModal() {
    const btn    = document.getElementById('enrollBtn');
    const isPaid = btn.dataset.isPaid === 'true';
    document.getElementById('formFilledCheck').checked = false;
    document.getElementById('formConfirmCheck').style.display = 'none';
    document.getElementById('btnConfirmEnroll').classList.remove('enabled');
    document.getElementById('confirmBtnText').textContent = isPaid
        ? 'Confirm & Proceed to Payment'
        : 'Confirm Enrollment';
    document.getElementById('formModalOverlay').classList.add('open');
}
function closeFormModal() {
    document.getElementById('formModalOverlay').classList.remove('open');
}
function openEnrollForm() {
    const btn = document.getElementById('enrollBtn');
    window.open(btn.dataset.formUrl, '_blank');
    document.getElementById('formConfirmCheck').style.display = 'flex';
}
function toggleConfirmBtn(checkbox) {
    const confirmBtn = document.getElementById('btnConfirmEnroll');
    checkbox.checked ? confirmBtn.classList.add('enabled') : confirmBtn.classList.remove('enabled');
}
document.getElementById('formModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeFormModal();
});

// ── Main enroll handler ────────────────────────────────────────────────
function handleEnroll() {
    const btn         = document.getElementById('enrollBtn');
    const isEnrolled  = btn.dataset.enrolled === 'true';
    const isPaid      = btn.dataset.isPaid === 'true';
    const requireForm = btn.dataset.requireForm === 'true';
    const formUrl     = btn.dataset.formUrl || '';

    if (isEnrolled) {
        const cancellationOpen     = btn.dataset.cancellationOpen !== 'false';
        const cancellationDeadline = btn.dataset.cancellationDeadline || '';
        if (!cancellationOpen) {
            showToast('Cancellation is no longer allowed. The deadline was ' + cancellationDeadline + '.', 'error');
            return;
        }
        if (isPaid) {
            openCancelEnrollModal();
            return;
        }
        doEnrollRequest(true, false, '');
        return;
    }

    if (requireForm && formUrl) {
        openFormModal();
        return;
    }

    if (isPaid) {
        window.location.href = btn.dataset.checkoutUrl;
        return;
    }

    doEnrollRequest(false, false, '');
}

function confirmEnrollAfterForm() {
    const btn    = document.getElementById('enrollBtn');
    const isPaid = btn.dataset.isPaid === 'true';
    closeFormModal();
    if (isPaid) {
        window.location.href = btn.dataset.checkoutUrl;
        return;
    }
    doEnrollRequest(false, true, btn.dataset.formUrl);
}

// ── Core enroll/unenroll fetch ─────────────────────────────────────────
async function doEnrollRequest(isUnenroll, hasForm, formUrl) {
    const btn    = document.getElementById('enrollBtn');
    const iconEl = document.getElementById('enrollIcon');
    const textEl = document.getElementById('enrollText');
    const isPaid = btn.dataset.isPaid === 'true';

    btn.disabled       = true;
    iconEl.className   = 'fas fa-spinner fa-spin';
    textEl.textContent = isUnenroll ? 'Cancelling...' : 'Enrolling...';

    try {
        const response = await fetch(isUnenroll ? UNENROLL_URL : ENROLL_URL, {
            method: isUnenroll ? 'DELETE' : 'POST',
            headers: {
                'X-CSRF-TOKEN'    : CSRF_TOKEN,
                'Accept'          : 'application/json',
                'Content-Type'    : 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json();

        if (data.requires_payment && data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        if (data.success) {
            setEnrollButton(data.is_enrolled, hasForm, isPaid);
            updateParticipantCount(data.participant_count);
            showToast(data.message, 'success');
        } else {
            setEnrollButton(isUnenroll ? true : false, hasForm, isPaid);
            showToast(data.message || 'Something went wrong. Please try again.', 'error');
        }
    } catch (err) {
        setEnrollButton(isUnenroll ? true : false, hasForm, isPaid);
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
        btn.className      = 'btn-enroll btn-enrolled';
        iconEl.className   = 'fas fa-user-check';
        textEl.textContent = 'Enrolled — Cancel';
    } else if (isPaid) {
        btn.className      = 'btn-enroll btn-paid';
        iconEl.className   = 'fas fa-lock';
        textEl.textContent = 'Pay & Enroll';
    } else if (hasForm) {
        btn.className      = 'btn-enroll';
        iconEl.className   = 'fas fa-file-alt';
        textEl.textContent = 'Fill Form & Enroll';
    } else {
        btn.className      = 'btn-enroll';
        iconEl.className   = 'fas fa-user-plus';
        textEl.textContent = 'Enroll Now';
    }
}

function updateParticipantCount(count) {
    const el = document.getElementById('participantCountDisplay');
    if (el) el.textContent = count + ' participant' + (count !== 1 ? 's' : '') + ' enrolled';
}

// Close modals on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCancelEnrollModal();
        closeFormModal();
    }
});
</script>

@endsection