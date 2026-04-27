@extends('layouts.app')

@section('title', 'Payment Successful!')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ═══════════════════════════════════════════
   CRAFTISTRY — PAYMENT SUCCESS
   Shopee order-confirmation style · Craftistry purple
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
    --success:    #10b981;
    --success-bg: #d1fae5;

    --fs-sm:   12px;
    --fs-base: 13px;
    --fs-md:   15px;
    --fs-lg:   18px;

    --sp-xs: 6px;
    --sp-sm: 10px;
    --sp-md: 16px;
    --sp-lg: 20px;
    --sp-xl: 24px;

    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 14px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    background: var(--bg);
    color: var(--ink);
    -webkit-font-smoothing: antialiased;
    line-height: 1.5;
}

/* ── Page wrapper ── */
.success-page {
    max-width: 560px;
    margin: 0 auto;
    padding: var(--sp-lg) var(--sp-md) 60px;
    display: flex;
    flex-direction: column;
    gap: var(--sp-sm);
}

/* ══════════════════════════════
   SUCCESS BANNER  (Shopee "Order Placed!" green strip)
══════════════════════════════ */
.success-banner {
    background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    border-radius: var(--radius-lg);
    padding: var(--sp-xl) var(--sp-xl);
    display: flex;
    align-items: center;
    gap: var(--sp-lg);
    box-shadow: 0 4px 20px rgba(16,185,129,.25);
    animation: bannerIn .45s cubic-bezier(.22,.68,0,1.2) both;
}

@keyframes bannerIn {
    from { opacity: 0; transform: translateY(-12px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.success-banner-icon {
    width: 52px;
    height: 52px;
    background: rgba(255,255,255,.22);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #fff;
    flex-shrink: 0;
    animation: iconPop .5s .2s cubic-bezier(.22,.68,0,1.4) both;
}

@keyframes iconPop {
    from { transform: scale(0); }
    to   { transform: scale(1); }
}

.success-banner-text h1 {
    font-size: var(--fs-lg);
    font-weight: 800;
    color: #fff;
    margin-bottom: 3px;
}

.success-banner-text p {
    font-size: var(--fs-sm);
    color: rgba(255,255,255,.82);
}

/* ══════════════════════════════
   ORDER CARD  (Shopee order detail card)
══════════════════════════════ */
.order-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: 0 1px 3px rgba(0,0,0,.07);
    border: 1px solid var(--border);
    overflow: hidden;
    animation: cardIn .4s .1s ease both;
}

@keyframes cardIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Card header — Shopee "Seller" header style */
.order-card-header {
    display: flex;
    align-items: center;
    gap: var(--sp-sm);
    padding: var(--sp-md) var(--sp-lg);
    border-bottom: 1px solid var(--divider);
    background: var(--divider);
}

.order-card-header-icon {
    width: 28px;
    height: 28px;
    border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 11px;
    flex-shrink: 0;
}

.order-card-header-label {
    font-size: var(--fs-base);
    font-weight: 700;
    color: var(--ink);
}

.order-card-header-badge {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: var(--success-bg);
    color: var(--success);
    font-size: var(--fs-sm);
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}

/* Card body — Shopee class/item row */
.order-item-row {
    display: flex;
    align-items: center;
    gap: var(--sp-md);
    padding: var(--sp-lg);
    border-bottom: 1px solid var(--divider);
}

.order-item-thumb {
    width: 64px;
    height: 64px;
    border-radius: var(--radius-md);
    background: linear-gradient(135deg, var(--lavender), #ddd6fe);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 1.4rem;
    flex-shrink: 0;
    overflow: hidden;
    border: 1px solid var(--border);
}

.order-item-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.order-item-body { flex: 1; min-width: 0; }

.order-item-title {
    font-size: var(--fs-md);
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.order-item-sub {
    font-size: var(--fs-sm);
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 4px;
}

.order-item-sub i { font-size: 10px; color: var(--primary); }

.order-item-price {
    font-size: var(--fs-lg);
    font-weight: 800;
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    flex-shrink: 0;
    white-space: nowrap;
}

/* ══════════════════════════════
   DETAIL ROWS  (Shopee "Order Details")
══════════════════════════════ */
.detail-section {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: 0 1px 3px rgba(0,0,0,.07);
    border: 1px solid var(--border);
    overflow: hidden;
    animation: cardIn .4s .18s ease both;
}

.detail-section-header {
    display: flex;
    align-items: center;
    gap: var(--sp-sm);
    padding: var(--sp-md) var(--sp-lg);
    border-bottom: 1px solid var(--divider);
}

.hline {
    width: 3px;
    height: 15px;
    background: linear-gradient(180deg, var(--primary), var(--primary-2));
    border-radius: 2px;
    flex-shrink: 0;
}

.detail-section-title {
    font-size: var(--fs-base);
    font-weight: 700;
    color: var(--ink);
}

.detail-rows { padding: var(--sp-md) var(--sp-lg); display: flex; flex-direction: column; gap: var(--sp-sm); }

.detail-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: var(--fs-base);
    gap: var(--sp-sm);
}

.detail-row-key {
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: var(--sp-xs);
    flex-shrink: 0;
}

.detail-row-key i { color: var(--primary); font-size: 11px; width: 13px; text-align: center; }

.detail-row-val {
    font-weight: 600;
    color: var(--ink);
    text-align: right;
}

.detail-row-val.price {
    font-weight: 800;
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: var(--fs-md);
}

.detail-row-val.status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--success);
    font-weight: 700;
}

/* Divider line between rows */
.detail-divider {
    height: 1px;
    background: var(--divider);
    margin: var(--sp-xs) 0;
}

/* ══════════════════════════════
   WHAT'S NEXT STRIP  (Shopee "Next steps" banner)
══════════════════════════════ */
.next-strip {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: 0 1px 3px rgba(0,0,0,.07);
    border: 1px solid var(--border);
    padding: var(--sp-md) var(--sp-lg);
    display: flex;
    align-items: flex-start;
    gap: var(--sp-md);
    animation: cardIn .4s .25s ease both;
}

.next-strip-icon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    background: var(--lavender);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 14px;
    flex-shrink: 0;
}

.next-strip-body { flex: 1; }

.next-strip-title {
    font-size: var(--fs-base);
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 2px;
}

.next-strip-desc {
    font-size: var(--fs-sm);
    color: var(--muted);
    line-height: 1.5;
}

/* ══════════════════════════════
   ACTION BUTTONS  (Shopee CTA row)
══════════════════════════════ */
.action-row {
    display: flex;
    gap: var(--sp-sm);
    animation: cardIn .4s .32s ease both;
}

.btn-primary {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-xs);
    padding: 12px var(--sp-lg);
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    color: #fff;
    border-radius: var(--radius-sm);
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    font-weight: 700;
    text-decoration: none;
    transition: opacity .15s, box-shadow .15s;
    box-shadow: 0 3px 10px rgba(102,126,234,.28);
    white-space: nowrap;
}

.btn-primary:hover {
    opacity: .9;
    color: #fff;
    box-shadow: 0 5px 16px rgba(102,126,234,.38);
    text-decoration: none;
}

.btn-secondary {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-xs);
    padding: 12px var(--sp-lg);
    background: var(--white);
    color: var(--muted);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    font-weight: 600;
    text-decoration: none;
    transition: all .15s;
    white-space: nowrap;
}

.btn-secondary:hover {
    border-color: var(--primary);
    color: var(--primary);
    text-decoration: none;
}

/* ── Responsive ── */
@media (max-width: 480px) {
    .success-page { padding: var(--sp-sm) var(--sp-sm) 60px; }
    .success-banner { flex-direction: column; gap: var(--sp-sm); text-align: center; }
    .action-row { flex-direction: column; }
    .order-item-row { flex-wrap: wrap; }
}
</style>
@endsection

@section('content')
<div class="success-page">

    {{-- ══ SUCCESS BANNER ══ --}}
    <div class="success-banner">
        <div class="success-banner-icon">
            <i class="fas fa-check"></i>
        </div>
        <div class="success-banner-text">
            <h1>Payment Successful!</h1>
            <p>You have successfully enrolled. See you at the class!</p>
        </div>
    </div>

    {{-- ══ ORDER CARD ══ --}}
    @if($booking && $booking->classEvent)
    <div class="order-card">

        {{-- Card header --}}
        <div class="order-card-header">
            <div class="order-card-header-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span class="order-card-header-label">Class Enrolled</span>
            <span class="order-card-header-badge">
                <i class="fas fa-check-circle"></i> Confirmed
            </span>
        </div>

        {{-- Class item row --}}
        <div class="order-item-row">
            <div class="order-item-thumb">
                @if($booking->classEvent->poster_image)
                    <img src="{{ asset('storage/' . $booking->classEvent->poster_image) }}"
                         alt="{{ $booking->classEvent->title }}">
                @else
                    <i class="fas fa-graduation-cap"></i>
                @endif
            </div>
            <div class="order-item-body">
                <div class="order-item-title">{{ $booking->classEvent->title }}</div>
                <div class="order-item-sub">
                    <i class="fas fa-{{ $booking->classEvent->media_type === 'online' ? 'laptop' : 'map-marker-alt' }}"></i>
                    {{ $booking->classEvent->media_type === 'online' ? 'Online Class' : 'Physical Class' }}
                </div>
            </div>
            <div class="order-item-price">
                RM {{ number_format($booking->amount_paid, 2) }}
            </div>
        </div>

    </div>
    @endif

    {{-- ══ BOOKING DETAILS ══ --}}
    @if($booking && $booking->classEvent)
    <div class="detail-section">
        <div class="detail-section-header">
            <div class="hline"></div>
            <span class="detail-section-title">Booking Details</span>
        </div>
        <div class="detail-rows">

            <div class="detail-row">
                <span class="detail-row-key">
                    <i class="fas fa-calendar-alt"></i> Date
                </span>
                <span class="detail-row-val">
                    {{ \Carbon\Carbon::parse($booking->classEvent->start_date)->format('d M Y') }}
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-row-key">
                    <i class="fas fa-clock"></i> Time
                </span>
                <span class="detail-row-val">
                    {{ \Carbon\Carbon::parse($booking->classEvent->start_time)->format('g:i A') }}
                </span>
            </div>

            @if($booking->classEvent->media_type === 'online' && $booking->classEvent->platform)
            <div class="detail-row">
                <span class="detail-row-key">
                    <i class="fas fa-laptop"></i> Platform
                </span>
                <span class="detail-row-val">{{ $booking->classEvent->platform }}</span>
            </div>
            @elseif($booking->classEvent->media_type === 'physical' && $booking->classEvent->location)
            <div class="detail-row">
                <span class="detail-row-key">
                    <i class="fas fa-map-marker-alt"></i> Venue
                </span>
                <span class="detail-row-val">{{ $booking->classEvent->location }}</span>
            </div>
            @endif

            <div class="detail-row">
                <span class="detail-row-key">
                    <i class="fas fa-user"></i> Instructor
                </span>
                <span class="detail-row-val">
                    {{ $booking->classEvent->user->fullname ?? $booking->classEvent->user->name ?? '—' }}
                </span>
            </div>

            <div class="detail-divider"></div>

            <div class="detail-row">
                <span class="detail-row-key">
                    <i class="fas fa-wallet"></i> Amount Paid
                </span>
                <span class="detail-row-val price">
                    RM {{ number_format($booking->amount_paid, 2) }}
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-row-key">
                    <i class="fas fa-circle-check"></i> Status
                </span>
                <span class="detail-row-val status">
                    <i class="fas fa-check-circle"></i> Confirmed
                </span>
            </div>

        </div>
    </div>
    @endif

    {{-- ══ WHAT'S NEXT ══ --}}
    @if($booking && $booking->classEvent && $booking->classEvent->enrollment_form_url)
    <div class="next-strip">
        <div class="next-strip-icon">
            <i class="fas fa-wpforms"></i>
        </div>
        <div class="next-strip-body">
            <div class="next-strip-title">Complete Your Enrollment Form</div>
            <div class="next-strip-desc">
                The instructor has attached an enrollment form. Please fill it in before the class starts.
                <br>
                <a href="{{ $booking->classEvent->enrollment_form_url }}" target="_blank"
                   style="color:var(--primary);font-weight:600;font-size:12px;">
                    Fill Enrollment Form <i class="fas fa-external-link-alt" style="font-size:10px;"></i>
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="next-strip">
        <div class="next-strip-icon">
            <i class="fas fa-bell"></i>
        </div>
        <div class="next-strip-body">
            <div class="next-strip-title">What Happens Next?</div>
            <div class="next-strip-desc">
                Check <strong>My Classes</strong> for class details. The instructor may share platform links or venue info closer to the date.
            </div>
        </div>
    </div>
    @endif

    {{-- ══ ACTION BUTTONS ══ --}}
    <div class="action-row">
        <a href="{{ route('my.classes') }}" class="btn-primary">
            <i class="fas fa-graduation-cap"></i> My Classes
        </a>
        <a href="{{ route('class.event.browse') }}" class="btn-secondary">
            <i class="fas fa-compass"></i> Browse More
        </a>
    </div>

</div>
@endsection