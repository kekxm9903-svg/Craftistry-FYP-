@extends('layouts.app')

@section('title', 'Browse Classes & Events')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ═══════════════════════════════════════════
   CRAFTISTRY — BROWSE CLASSES & EVENTS
   Shopee-style · Craftistry purple palette
═══════════════════════════════════════════ */

:root {
    --primary:    #667eea;
    --primary-2:  #764ba2;
    --lavender:   #ede9fe;
    --ink:        #1a1a2e;
    --muted:      #6b6b8a;
    --border:     #e0e0ee;
    --divider:    #f0f0f5;
    --bg:         #f0f0f5;
    --white:      #ffffff;
    --success:    #16a34a;
    --danger:     #dc2626;
    --warning:    #ca8a04;

    --fs-sm:      12px;
    --fs-base:    13px;
    --fs-md:      15px;

    --sp-xs:      6px;
    --sp-sm:      10px;
    --sp-md:      16px;
    --sp-lg:      20px;
    --sp-xl:      24px;

    --radius-sm:  6px;
    --radius-md:  10px;
    --radius-lg:  14px;
}

*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    background: var(--bg);
    color: var(--ink);
    -webkit-font-smoothing: antialiased;
    line-height: 1.5;
}


/* ── Breadcrumb ── */

.bc-bar {
    background: var(--white);
    border-bottom: 1px solid var(--border);
    padding: var(--sp-xs) 0;
    font-size: var(--fs-sm);
}

.bc-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 var(--sp-lg);
    display: flex;
    align-items: center;
    gap: var(--sp-xs);
}

.bc-inner a {
    color: var(--muted);
    text-decoration: none;
    transition: color .15s;
}

.bc-inner a:hover { color: var(--primary); }
.bc-inner .sep { color: #ccc; }
.bc-inner .cur { color: var(--ink); font-weight: 500; }


/* ── Category tab bar ── */

.category-bar {
    background: var(--white);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 100;
}

.category-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 var(--sp-lg);
    display: flex;
    align-items: center;
    gap: 0;
    overflow-x: auto;
    scrollbar-width: none;
}

.category-inner::-webkit-scrollbar { display: none; }

.cat-pill {
    flex-shrink: 0;
    padding: var(--sp-sm) var(--sp-md);
    font-size: var(--fs-base);
    font-weight: 500;
    color: var(--muted);
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: color .15s, border-color .15s;
    white-space: nowrap;
    cursor: pointer;
    background: none;
    border-top: none;
    border-left: none;
    border-right: none;
    font-family: 'Inter', sans-serif;
}

.cat-pill:hover { color: var(--primary); }

.cat-pill.active {
    color: var(--primary);
    font-weight: 700;
    border-bottom-color: var(--primary);
}


/* ── Page wrapper ── */

.browse-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: var(--sp-md) var(--sp-lg) 60px;
    display: flex;
    flex-direction: column;
    gap: var(--sp-sm);
}


/* ── White card ── */

.sp-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: 0 1px 3px rgba(0, 0, 0, .07);
    overflow: hidden;
}

.sp-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--sp-md) var(--sp-lg);
    border-bottom: 1px solid var(--divider);
    font-size: var(--fs-base);
    font-weight: 700;
    color: var(--ink);
}

.sp-card-header-left {
    display: flex;
    align-items: center;
    gap: var(--sp-sm);
}

.hline {
    width: 3px;
    height: 15px;
    background: linear-gradient(180deg, var(--primary), var(--primary-2));
    border-radius: 2px;
    flex-shrink: 0;
}

.section-count {
    font-size: var(--fs-sm);
    font-weight: 500;
    color: var(--muted);
    background: var(--divider);
    padding: 2px var(--sp-sm);
    border-radius: 20px;
}

.sp-card-body {
    padding: var(--sp-lg);
}


/* ── Filter card ── */

.filter-card { overflow: visible; }

.filter-form {
    display: flex;
    align-items: center;
    gap: var(--sp-sm);
    padding: var(--sp-md) var(--sp-lg);
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 200px;
    position: relative;
}

.search-icon {
    position: absolute;
    left: var(--sp-sm);
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    font-size: var(--fs-sm);
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 8px var(--sp-sm) 8px 30px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    color: var(--ink);
    background: var(--white);
    transition: border-color .15s;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, .1);
}

.search-input::placeholder { color: #bbb; }

.btn-search {
    padding: 8px var(--sp-lg);
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: var(--sp-xs);
    white-space: nowrap;
    transition: opacity .15s, box-shadow .15s;
    box-shadow: 0 3px 10px rgba(102, 126, 234, .25);
}

.btn-search:hover {
    opacity: .9;
    box-shadow: 0 5px 16px rgba(102, 126, 234, .38);
}

/* Filter label */
.filter-label {
    font-size: var(--fs-sm);
    font-weight: 600;
    color: var(--muted);
    white-space: nowrap;
}

.filter-divider {
    width: 1px;
    height: 20px;
    background: var(--border);
    flex-shrink: 0;
}

/* Filter toggle buttons */
.filter-btn {
    padding: 6px var(--sp-sm);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: var(--fs-sm);
    font-family: 'Inter', sans-serif;
    background: var(--white);
    cursor: pointer;
    transition: all .15s;
    font-weight: 500;
    color: var(--muted);
    white-space: nowrap;
}

.filter-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.filter-btn.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    color: #fff;
    border-color: transparent;
}

.filter-btn.status-active.active   { background: linear-gradient(135deg, #10b981, #059669); }
.filter-btn.status-upcoming.active { background: linear-gradient(135deg, #f59e0b, #d97706); }
.filter-btn.status-expired.active  { background: linear-gradient(135deg, #94a3b8, #64748b); }
.filter-btn.status-full.active     { background: linear-gradient(135deg, #ef4444, #dc2626); }


/* ── Results meta ── */

.results-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: var(--fs-sm);
    color: var(--muted);
    padding: var(--sp-xs) 0 0;
}

.results-meta strong { color: var(--ink); }

.results-meta a {
    color: var(--primary);
    font-weight: 600;
    text-decoration: none;
    font-size: var(--fs-sm);
}


/* ── Classes grid ── */

.classes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: var(--sp-md);
    margin-bottom: var(--sp-lg);
}


/* ── Class card ── */

.class-card {
    background: var(--white);
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid var(--border);
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    transition: box-shadow .18s, transform .18s, border-color .18s;
}

.class-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(102, 126, 234, .13);
    border-color: #c4b5fd;
}

/* Cover image */
.class-cover {
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, var(--lavender), #ddd6fe);
    flex-shrink: 0;
}

.class-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Ended overlay */
.ended-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, .5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--fs-base);
    font-weight: 800;
    color: #fff;
    letter-spacing: 2px;
}

/* Card body */
.class-body {
    padding: var(--sp-sm) var(--sp-md) var(--sp-md);
    display: flex;
    flex-direction: column;
    gap: var(--sp-xs);
    flex: 1;
}

/* Type + Status row */
.card-tags {
    display: flex;
    align-items: center;
    gap: var(--sp-xs);
    flex-wrap: wrap;
}

.tag-type {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px var(--sp-xs);
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.tag-type.online   { background: #dbeafe; color: #3b82f6; }
.tag-type.physical { background: var(--lavender); color: var(--primary-2); }

.tag-status {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 2px var(--sp-xs);
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.tag-status.active   { background: #dcfce7; color: #16a34a; }
.tag-status.upcoming { background: #fef9c3; color: #ca8a04; }
.tag-status.expired  { background: #f1f5f9; color: #64748b; }
.tag-status.full     { background: #fee2e2; color: #dc2626; }

/* Title */
.class-title {
    font-size: var(--fs-md);
    font-weight: 700;
    color: var(--ink);
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Description */
.class-desc {
    font-size: var(--fs-sm);
    color: var(--muted);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Artist strip */
.class-artist {
    display: flex;
    align-items: center;
    gap: var(--sp-xs);
    padding-bottom: var(--sp-xs);
    border-bottom: 1px solid var(--divider);
}

.artist-ava {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 10px;
}

.artist-ava img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.artist-ava-name {
    font-size: var(--fs-sm);
    font-weight: 600;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Detail rows */
.class-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-row {
    display: flex;
    align-items: flex-start;
    gap: var(--sp-xs);
    font-size: var(--fs-sm);
    color: var(--muted);
}

.detail-row .di {
    flex-shrink: 0;
    width: 14px;
    text-align: center;
    font-size: 11px;
    margin-top: 1px;
}

/* Fee */
.class-fee {
    font-size: var(--fs-md);
    font-weight: 800;
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
}

.class-fee.free {
    background: none;
    -webkit-text-fill-color: #16a34a;
    color: #16a34a;
}

/* Participant bar */
.participant-bar {
    background: var(--divider);
    border-radius: var(--radius-sm);
    padding: var(--sp-sm);
}

.p-bar-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
    font-size: var(--fs-sm);
}

.p-bar-label {
    color: var(--muted);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
}

.p-bar-count {
    font-weight: 700;
    color: var(--ink);
}

.p-bar-count .of {
    font-weight: 400;
    color: var(--muted);
}

.progress-track {
    height: 5px;
    background: var(--border);
    border-radius: 99px;
    overflow: hidden;
    margin-bottom: 4px;
}

.progress-fill {
    height: 100%;
    border-radius: 99px;
    transition: width .4s ease;
}

.progress-fill.low  { background: linear-gradient(90deg, #10b981, #34d399); }
.progress-fill.mid  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.progress-fill.high { background: linear-gradient(90deg, #ef4444, #f87171); }
.progress-fill.full { background: linear-gradient(90deg, #94a3b8, #64748b); }

.p-bar-sub {
    font-size: 11px;
    color: var(--muted);
    font-weight: 500;
}

.p-bar-sub.urgent {
    color: #ef4444;
    font-weight: 700;
}

/* CTA button */
.class-btn {
    width: 100%;
    padding: 9px;
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-xs);
    transition: opacity .15s;
    margin-top: auto;
}

.class-btn:hover { opacity: .88; }
.class-btn.expired { background: linear-gradient(135deg, #94a3b8, #64748b); }
.class-btn.full    { background: linear-gradient(135deg, #ef4444, #dc2626); }


/* ── Pagination ── */

.pagination-row {
    display: flex;
    justify-content: center;
    padding-top: var(--sp-lg);
    border-top: 1px solid var(--divider);
}

.pagination-nav {
    display: flex;
    align-items: center;
    gap: var(--sp-xs);
    flex-wrap: wrap;
    justify-content: center;
}

/* Override Laravel default pagination */
.pagination-row nav {
    display: flex;
    justify-content: center;
    width: 100%;
}

.pagination-row svg {
    width: 16px !important;
    height: 16px !important;
}

.pagination-row p { display: none; }

.pagination-row a,
.pagination-row span {
    min-width: 36px;
    height: 36px;
    padding: 0 var(--sp-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--white);
    color: var(--ink);
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    font-weight: 600;
    text-decoration: none;
    transition: all .15s;
}

.pagination-row a:hover {
    border-color: var(--primary);
    color: var(--primary);
    background: var(--lavender);
}

.pagination-row span[aria-current="page"] {
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    border-color: transparent;
    color: #fff;
}

.pagination-row span.disabled,
.pagination-row span:not([aria-current]) {
    opacity: .4;
    pointer-events: none;
}


/* ── Empty state ── */

.empty-state {
    text-align: center;
    padding: 48px var(--sp-lg);
    color: var(--muted);
}

.empty-state i {
    font-size: 2.2rem;
    color: #d1d5db;
    display: block;
    margin-bottom: var(--sp-sm);
}

.empty-state h3 {
    font-size: var(--fs-md);
    font-weight: 700;
    color: var(--ink);
    margin-bottom: var(--sp-xs);
}

.empty-state p {
    font-size: var(--fs-sm);
}


/* ── Responsive ── */

@media (max-width: 860px) {
    .classes-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

@media (max-width: 600px) {
    .browse-page {
        padding: var(--sp-sm) var(--sp-sm) 48px;
    }

    .filter-form {
        padding: var(--sp-md);
    }

    .search-box {
        min-width: 100%;
    }

    .classes-grid {
        grid-template-columns: 1fr 1fr;
        gap: var(--sp-sm);
    }

    .sp-card-body {
        padding: var(--sp-sm);
    }
}

@media (max-width: 420px) {
    .classes-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">Browse Classes & Events</span>
    </div>
</div>

{{-- Category / Status tabs --}}
<div class="category-bar">
    <div class="category-inner">
        <form action="{{ route('class.event.browse') }}" method="GET" id="tabForm" style="display:contents;">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="type"   id="tab-type"   value="{{ request('type', 'all') }}">
            <input type="hidden" name="status" id="tab-status" value="{{ request('status', 'all') }}">

            <button type="button" class="cat-pill {{ request('type', 'all') == 'all' && request('status', 'all') == 'all' ? 'active' : '' }}"
                    onclick="setTab('all','all')">All</button>
            <button type="button" class="cat-pill {{ request('type') == 'online' ? 'active' : '' }}"
                    onclick="setTab('online','all')">💻 Online</button>
            <button type="button" class="cat-pill {{ request('type') == 'physical' ? 'active' : '' }}"
                    onclick="setTab('physical','all')">📍 Physical</button>
            <button type="button" class="cat-pill {{ request('status') == 'active' ? 'active' : '' }}"
                    onclick="setTab('all','active')">🟢 Active</button>
            <button type="button" class="cat-pill {{ request('status') == 'upcoming' ? 'active' : '' }}"
                    onclick="setTab('all','upcoming')">🟡 Upcoming</button>
            <button type="button" class="cat-pill {{ request('status') == 'full' ? 'active' : '' }}"
                    onclick="setTab('all','full')">🔴 Full</button>
            <button type="button" class="cat-pill {{ request('status') == 'expired' ? 'active' : '' }}"
                    onclick="setTab('all','expired')">⚫ Expired</button>
        </form>
    </div>
</div>

<div class="browse-page">

    {{-- Filter bar --}}
    <div class="sp-card filter-card">
        <form action="{{ route('class.event.browse') }}" method="GET" id="searchForm" class="filter-form">
            <input type="hidden" name="type"   value="{{ request('type', 'all') }}">
            <input type="hidden" name="status" value="{{ request('status', 'all') }}">

            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       name="search"
                       class="search-input"
                       placeholder="Search classes or events..."
                       value="{{ request('search') }}">
            </div>

            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Search
            </button>

            @if(request('search') || (request('type') && request('type') != 'all') || (request('status') && request('status') != 'all'))
                <a href="{{ route('class.event.browse') }}"
                   style="font-size:var(--fs-sm);color:var(--muted);text-decoration:none;white-space:nowrap;">
                    <i class="fas fa-times"></i> Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Results card --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Browse Classes & Events
            </div>
            @if($classEvents->total() > 0)
                <span class="section-count">{{ $classEvents->total() }} event{{ $classEvents->total() !== 1 ? 'es' : '' }}</span>
            @endif
        </div>

        <div class="sp-card-body">

            {{-- Results meta --}}
            @if($classEvents->count() > 0)
            <div class="results-meta" style="margin-bottom:var(--sp-md);">
                <span>
                    Showing <strong>{{ $classEvents->firstItem() }}–{{ $classEvents->lastItem() }}</strong>
                    of <strong>{{ $classEvents->total() }}</strong>
                    @if(request('search')) for "<strong>{{ request('search') }}</strong>" @endif
                </span>
            </div>
            @endif

            @if($classEvents->count() > 0)

            <div class="classes-grid">
                @foreach($classEvents as $class)
                @php
                    $now          = \Carbon\Carbon::now();
                    $startDate    = \Carbon\Carbon::parse($class->start_date);
                    $endDate      = $class->end_date ? \Carbon\Carbon::parse($class->end_date) : null;
                    $maxSlots     = $class->max_participants ?? null;
                    $enrolled     = $class->bookings_count ?? 0;
                    $spotsLeft    = $maxSlots ? max(0, $maxSlots - $enrolled) : null;
                    $isFull       = $maxSlots && $enrolled >= $maxSlots;
                    $deadlineDate = $class->enrollment_deadline ? \Carbon\Carbon::parse($class->enrollment_deadline) : null;
                    $isExpired    = $deadlineDate ? $now->gt($deadlineDate) : ($endDate ? $now->gt($endDate) : $startDate->lt($now->subDays(1)));
                    $isUpcoming   = $startDate->gt($now);
                    $fillPct      = $maxSlots > 0 ? min(100, round(($enrolled / $maxSlots) * 100)) : 0;

                    if ($isFull)         $statusKey = 'full';
                    elseif ($isExpired)  $statusKey = 'expired';
                    elseif ($isUpcoming) $statusKey = 'upcoming';
                    else                 $statusKey = 'active';

                    $statusLabels = [
                        'active'   => '🟢 Active',
                        'upcoming' => '🟡 Upcoming',
                        'expired'  => '⚫ Expired',
                        'full'     => '🔴 Full',
                    ];

                    if ($fillPct >= 100)    $barClass = 'full';
                    elseif ($fillPct >= 75) $barClass = 'high';
                    elseif ($fillPct >= 40) $barClass = 'mid';
                    else                   $barClass = 'low';
                @endphp

                <a href="{{ route('class.event.show', $class->id) }}" class="class-card">

                    {{-- Cover image --}}
                    <div class="class-cover">
                        <img src="{{ $class->poster_image ? asset('storage/' . $class->poster_image) : asset('images/placeholder-class.jpg') }}"
                             alt="{{ $class->title }}"
                             onerror="this.style.display='none'">
                        @if($isExpired)
                            <div class="ended-overlay">ENDED</div>
                        @endif
                    </div>

                    <div class="class-body">

                        {{-- Tags --}}
                        <div class="card-tags">
                            <span class="tag-type {{ $class->media_type }}">
                                {{ $class->media_type == 'online' ? '💻 Online' : '📍 Physical' }}
                            </span>
                            <span class="tag-status {{ $statusKey }}">
                                {{ $statusLabels[$statusKey] }}
                            </span>
                        </div>

                        {{-- Title --}}
                        <h3 class="class-title">{{ $class->title }}</h3>

                        {{-- Description --}}
                        @if($class->description)
                            <p class="class-desc">{{ $class->description }}</p>
                        @endif

                        {{-- Artist --}}
                        <div class="class-artist">
                            <div class="artist-ava">
                                @if($class->user && $class->user->profile_image)
                                    <img src="{{ asset('storage/' . $class->user->profile_image) }}"
                                         alt="{{ $class->user->fullname }}">
                                @else
                                    {{ strtoupper(substr($class->user->fullname ?? 'U', 0, 1)) }}
                                @endif
                            </div>
                            <span class="artist-ava-name">{{ $class->user->fullname ?? 'Unknown Artist' }}</span>
                        </div>

                        {{-- Details --}}
                        <div class="class-details">
                            <div class="detail-row">
                                <span class="di">📅</span>
                                <span>{{ $startDate->format('d M Y') }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="di">⏰</span>
                                <span>{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}</span>
                            </div>
                            @if($class->media_type == 'online' && $class->platform)
                                <div class="detail-row">
                                    <span class="di">💻</span>
                                    <span>{{ $class->platform }}</span>
                                </div>
                            @elseif($class->media_type == 'physical' && $class->location)
                                <div class="detail-row">
                                    <span class="di">📍</span>
                                    <span>{{ $class->location }}</span>
                                </div>
                            @endif
                            @if($class->enrollment_deadline)
                                <div class="detail-row">
                                    <span class="di">⏳</span>
                                    <span style="{{ $isExpired ? 'color:#ef4444;font-weight:600;' : '' }}">
                                        Enroll by {{ \Carbon\Carbon::parse($class->enrollment_deadline)->format('d M Y') }}
                                    </span>
                                </div>
                            @endif
                            <div class="detail-row">
                                <span class="di">💰</span>
                                @if($class->fee ?? $class->price ?? null)
                                    <span class="class-fee">RM {{ number_format($class->fee ?? $class->price, 2) }}</span>
                                @else
                                    <span class="class-fee free">Free</span>
                                @endif
                            </div>
                        </div>

                        {{-- Participant bar --}}
                        @if($maxSlots)
                        <div class="participant-bar">
                            <div class="p-bar-top">
                                <span class="p-bar-label">
                                    <i class="fas fa-users"></i> Participants
                                </span>
                                <span class="p-bar-count">
                                    {{ $enrolled }}<span class="of"> / {{ $maxSlots }}</span>
                                </span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill {{ $barClass }}" style="width:{{ $fillPct }}%"></div>
                            </div>
                            <div class="p-bar-sub {{ $spotsLeft !== null && $spotsLeft <= 3 && !$isFull ? 'urgent' : '' }}">
                                @if($isFull)
                                    <i class="fas fa-ban"></i> No spots available
                                @elseif($spotsLeft !== null && $spotsLeft <= 3)
                                    <i class="fas fa-fire"></i> Only {{ $spotsLeft }} spot{{ $spotsLeft == 1 ? '' : 's' }} left!
                                @elseif($spotsLeft !== null)
                                    {{ $spotsLeft }} spot{{ $spotsLeft == 1 ? '' : 's' }} remaining
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="participant-bar">
                            <div class="p-bar-top">
                                <span class="p-bar-label"><i class="fas fa-users"></i> Enrolled</span>
                                <span class="p-bar-count">{{ $enrolled }} joined</span>
                            </div>
                            <div class="p-bar-sub">No participant limit</div>
                        </div>
                        @endif

                        {{-- CTA --}}
                        <button class="class-btn {{ $isExpired ? 'expired' : ($isFull ? 'full' : '') }}">
                            @if($isExpired)
                                <i class="fas fa-eye"></i> View Summary
                            @elseif($isFull)
                                <i class="fas fa-ban"></i> Class Full
                            @elseif($isUpcoming)
                                <i class="fas fa-bell"></i> View & Enroll
                            @else
                                <i class="fas fa-arrow-right"></i> View Details
                            @endif
                        </button>

                    </div>
                </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($classEvents->hasPages())
            <div class="pagination-row">
                {{ $classEvents->appends(request()->query())->links() }}
            </div>
            @endif

            @else
            <div class="empty-state">
                <i class="fas fa-calendar-alt"></i>
                <h3>No Classes or Events Found</h3>
                <p>
                    @if(request('search'))
                        No results for "{{ request('search') }}". Try a different keyword.
                    @elseif(request('status') == 'active')
                        No active classes right now.
                    @elseif(request('status') == 'upcoming')
                        No upcoming classes scheduled yet.
                    @else
                        No classes or events available at the moment.
                    @endif
                </p>
            </div>
            @endif

        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
function setTab(type, status) {
    document.getElementById('tab-type').value   = type;
    document.getElementById('tab-status').value = status;
    document.getElementById('tabForm').submit();
}
</script>
@endsection