@extends('layouts.app')

@section('title', 'My Classes — Craftistry')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/myClasses.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">My Classes</span>
    </div>
</div>

{{-- Back button --}}
<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="{{ route('dashboard') }}" class="back-btn">
        ← Back
    </a>
</div>

<div class="classes-page">

    {{-- ══ PAGE HEADER CARD ══ --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">My Classes</div>
            <div class="page-subtitle">
                @if($bookings->total() > 0)
                    {{ $bookings->total() }} {{ Str::plural('class', $bookings->total()) }} enrolled
                @else
                    Start learning from talented Malaysian artists
                @endif
            </div>
        </div>
        <a href="{{ route('class.event.browse') }}" class="btn-browse">
            <i class="fas fa-compass"></i> Browse Classes
        </a>
    </div>

    {{-- ══ FILTER CARD ══ --}}
    @if($bookings->total() > 0)
    <div class="filter-card">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text"
                   id="cls-search"
                   placeholder="Search by class title or instructor..."
                   autocomplete="off">
            <button class="search-clear" id="search-clear" style="display:none" aria-label="Clear">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <select id="cls-type" class="filter-select">
            <option value="all">All Types</option>
            <option value="online">🖥️ Online</option>
            <option value="physical">📍 Physical</option>
        </select>

        <select id="cls-sort" class="filter-select">
            <option value="newest">Latest Enrolled</option>
            <option value="oldest">Oldest Enrolled</option>
            <option value="title-az">Title A–Z</option>
            <option value="upcoming">Upcoming First</option>
        </select>
    </div>
    @endif

    {{-- ══ CLASS CARDS GRID ══ --}}
    @if($bookings->total() > 0)

        <div class="class-grid" id="cls-grid">
            @foreach($bookings as $booking)
                @php $event = $booking->classEvent; @endphp
                @if($event)
                <div class="class-card"
                     data-title="{{ strtolower($event->title) }}"
                     data-instructor="{{ strtolower($event->user->fullname ?? $event->user->name ?? '') }}"
                     data-type="{{ $event->media_type }}"
                     data-enrolled-at="{{ $booking->booked_at ?? $booking->created_at }}"
                     data-start-date="{{ $event->start_date ?? '' }}"
                     style="--i: {{ $loop->index }}">

                    {{-- Poster image --}}
                    <div class="class-poster">
                        @if($event->poster_image)
                            <img src="{{ asset('storage/' . $event->poster_image) }}"
                                 alt="{{ $event->title }}" loading="lazy">
                        @else
                            <div class="poster-empty">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                        @endif

                        {{-- Type badge --}}
                        <span class="type-badge {{ $event->media_type }}">
                            {{ $event->media_type === 'online' ? '🖥️ Online' : '📍 Physical' }}
                        </span>

                        {{-- Enrolled stamp --}}
                        <span class="enrolled-stamp">
                            <i class="fas fa-check"></i> Enrolled
                        </span>

                        {{-- Deadline chip --}}
                        @if($event->enrollment_deadline)
                            @if(!$event->is_enrollment_open)
                                <span class="deadline-chip closed">Closed</span>
                            @elseif($event->days_until_deadline <= 3)
                                <span class="deadline-chip soon">{{ $event->days_until_deadline }}d left</span>
                            @else
                                <span class="deadline-chip open">Open</span>
                            @endif
                        @endif
                    </div>

                    {{-- Instructor info row --}}
                    <a href="{{ route('class.event.show', $event->id) }}" class="instructor-row">
                        <div class="instructor-avatar">
                            @if($event->user && $event->user->profile_image)
                                <img src="{{ asset('storage/' . $event->user->profile_image) }}"
                                     alt="{{ $event->user->fullname }}" loading="lazy">
                            @else
                                <div class="avatar-placeholder">
                                    {{ strtoupper(substr($event->user->fullname ?? $event->user->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="instructor-meta">
                            <div class="class-title-text">{{ $event->title }}</div>
                            @if($event->user)
                                <span class="instructor-name">
                                    <i class="fas fa-user"></i>
                                    {{ $event->user->fullname ?? $event->user->name ?? 'Unknown Instructor' }}
                                </span>
                            @endif
                        </div>
                    </a>

                    {{-- Schedule strip --}}
                    <div class="schedule-strip">
                        <div class="schedule-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>{{ $event->formatted_date_range }}</span>
                        </div>
                        <div class="schedule-item">
                            <i class="fas fa-clock"></i>
                            <span>{{ $event->formatted_time_range }}</span>
                        </div>
                        <div class="schedule-item">
                            @if($event->media_type === 'online')
                                <i class="fas fa-laptop"></i>
                                <span>{{ Str::limit($event->platform ?? 'Platform TBA', 22) }}</span>
                            @else
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ Str::limit($event->location ?? 'Venue TBA', 22) }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Card footer --}}
                    <div class="card-footer">
                        <span class="enrolled-date">
                            <i class="fas fa-graduation-cap" style="color:var(--primary);font-size:10px;"></i>
                            Enrolled {{ \Carbon\Carbon::parse($booking->booked_at ?? $booking->created_at)
                                ->timezone('Asia/Kuala_Lumpur')
                                ->diffForHumans() }}
                        </span>

                        @if($event->enrollment_form_url)
                            <a href="{{ $event->enrollment_form_url }}" target="_blank"
                               class="btn-view" onclick="event.stopPropagation()">
                                <i class="fas fa-wpforms"></i> Form
                            </a>
                        @else
                            <a href="{{ route('class.event.show', $event->id) }}" class="btn-view">
                                View <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif
                    </div>

                </div>
                @endif
            @endforeach
        </div>

        {{-- No search results --}}
        <div class="empty-search" id="empty-search" style="display:none">
            <i class="fas fa-search"></i>
            <h3>No classes found</h3>
            <p>Try searching with a different title or instructor name</p>
            <button id="clear-search-btn" class="btn-clear-search">Clear Search</button>
        </div>

        {{-- Pagination --}}
        @if($bookings->hasPages())
        <div class="pagination-wrapper">
            {{ $bookings->links() }}
        </div>
        @endif

    @else

        {{-- ══ EMPTY STATE ══ --}}
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3>No classes enrolled yet</h3>
            <p>Discover classes taught by talented local artists and enrol to start your creative journey.</p>
            <a href="{{ route('class.event.browse') }}" class="btn-browse-empty">
                <i class="fas fa-compass"></i> Browse Classes
            </a>
        </div>

    @endif

</div>

@endsection

@section('scripts')
<script src="{{ asset('js/my-classes.js') }}"></script>
@endsection