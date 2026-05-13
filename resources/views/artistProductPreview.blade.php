@extends('layouts.app')

@section('title', 'Preview: ' . $artwork->product_name . ' - Craftistry')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
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
        --success:    #2e7d32;

        --fs-sm:   12px;
        --fs-base: 13px;
        --fs-md:   15px;
        --fs-lg:   20px;
        --fs-xl:   28px;

        --sp-xs:  6px;
        --sp-sm:  10px;
        --sp-md:  16px;
        --sp-lg:  20px;
        --sp-xl:  24px;

        --radius-sm:  6px;
        --radius-md: 10px;
        --radius-lg: 14px;

        --label-w: 110px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Inter', sans-serif;
        font-size: var(--fs-base);
        background: var(--bg);
        color: var(--ink);
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
    }

    /* ── PREVIEW BANNER ── */
    .preview-banner {
        position: fixed;
        left: 0;
        right: 0;
        z-index: 200;
        height: 46px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 2px 16px rgba(102,126,234,.35);
        /* top is set by JS */
    }
    .preview-banner-inner {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 var(--sp-lg);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--sp-md);
    }
    .preview-banner-left { display: flex; align-items: center; gap: var(--sp-sm); }
    .preview-eye-icon {
        width: 28px; height: 28px;
        background: rgba(255,255,255,.2);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; color: #fff; flex-shrink: 0;
    }
    .preview-label { display: block; font-size: 0.84rem; font-weight: 700; color: #fff; line-height: 1.2; }
    .preview-sub   { display: block; font-size: 0.68rem; color: rgba(255,255,255,.78); }
    .preview-banner-right { display: flex; gap: var(--sp-sm); align-items: center; }
    .preview-back-btn, .preview-edit-btn {
        display: flex; align-items: center; gap: var(--sp-xs);
        padding: 5px 12px; border-radius: var(--radius-sm);
        font-size: var(--fs-sm); font-weight: 600; text-decoration: none; transition: all .18s;
    }
    .preview-back-btn {
        background: rgba(255,255,255,.15); color: #fff;
        border: 1px solid rgba(255,255,255,.3);
    }
    .preview-back-btn:hover { background: rgba(255,255,255,.25); color: #fff; }
    .preview-edit-btn { background: #fff; color: var(--primary-2); border: 1px solid transparent; }
    .preview-edit-btn:hover { background: var(--lavender); }

    /* ── BREADCRUMB ── */
    /* all positioning handled by JS to beat layouts.app !important rules */
    .bc-bar {
        height: 31px;
        background: var(--white) !important;
        border-bottom: 1px solid var(--border) !important;
        box-shadow: 0 1px 4px rgba(0,0,0,.06);
        display: flex !important;
        align-items: center !important;
        padding: 0 !important;
    }
    .bc-inner {
        max-width: 1100px;
        width: 100%;
        margin: 0 auto;
        padding: 0 var(--sp-lg);
        display: flex;
        align-items: center;
        gap: var(--sp-xs);
        font-size: var(--fs-sm);
        color: var(--muted);
    }
    .bc-inner a { color: var(--muted); text-decoration: none; transition: color .15s; }
    .bc-inner a:hover { color: var(--primary); }
    .bc-inner .sep { color: #ccc; }
    .bc-inner .cur { color: var(--ink); font-weight: 500; }

    /* ── Page content offset — set by JS too ── */
    .preview-page-wrap { }

    /* ── DISABLED CTA ── */
    .preview-cta-disabled .sp-cta-row button,
    .preview-cta-disabled .sp-cta-row-bulk a {
        opacity: 0.45;
        cursor: not-allowed;
        pointer-events: none;
    }
    .preview-cta-note {
        font-size: var(--fs-sm); color: var(--muted);
        display: flex; align-items: center; gap: 5px;
        margin-top: var(--sp-xs); padding: var(--sp-xs) var(--sp-sm);
        background: #f5f3ff; border-radius: var(--radius-sm); border: 1px solid #ddd6fe;
    }
    .preview-cta-note i { color: var(--primary); }

    .back-btn {
        display: inline-flex; align-items: center; gap: var(--sp-xs);
        color: var(--muted); text-decoration: none;
        font-size: var(--fs-base); font-weight: 600;
        transition: color .15s; margin-bottom: var(--sp-xs);
    }
    .back-btn:hover { color: var(--primary); }

    .sp-page {
        max-width: 1100px; margin: 0 auto;
        padding: var(--sp-md) var(--sp-lg) 60px;
        display: flex; flex-direction: column; gap: var(--sp-sm);
    }

    .sp-card {
        background: var(--white); border-radius: var(--radius-lg);
        box-shadow: 0 1px 3px rgba(0,0,0,.07); overflow: hidden;
    }
    .sp-card-header {
        padding: var(--sp-md) var(--sp-lg); font-size: var(--fs-base);
        font-weight: 700; color: var(--ink); border-bottom: 1px solid var(--divider);
        display: flex; align-items: center; gap: var(--sp-sm);
    }
    .sp-card-header .hline {
        width: 3px; height: 14px;
        background: linear-gradient(180deg, var(--primary), var(--primary-2));
        border-radius: 2px; flex-shrink: 0;
    }
    .sp-card-body { padding: var(--sp-lg); }

    .sp-product-card { display: grid; grid-template-columns: 360px 1fr; }

    .sp-img-pane {
        padding: var(--sp-lg); border-right: 1px solid var(--divider);
        display: flex; flex-direction: column; gap: var(--sp-sm);
    }

    .sp-thumbs { display: flex; gap: var(--sp-xs); flex-wrap: wrap; }
    .sp-thumb {
        width: 60px; height: 60px; border: 2px solid var(--border);
        border-radius: var(--radius-sm); overflow: hidden; cursor: pointer; flex-shrink: 0;
        transition: border-color .15s, transform .12s; background: #fafafa;
    }
    .sp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .sp-thumb:hover { border-color: var(--primary); transform: scale(1.04); }
    .sp-thumb.active { border-color: var(--primary); box-shadow: 0 0 0 2px #c4b5fd; }

    .sp-main-img {
        width: 100%; aspect-ratio: 1; border: 1px solid var(--border);
        border-radius: var(--radius-md); overflow: hidden; background: #fafafa;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .sp-main-img img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .sp-img-placeholder {
        width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: var(--primary-2); font-size: 3.5rem;
    }
    .sp-promo-img-badge {
        position: absolute; top: 10px; left: 10px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff; font-size: 11px; font-weight: 800;
        padding: 3px 8px; border-radius: 20px; letter-spacing: .5px;
        z-index: 2; box-shadow: 0 2px 6px rgba(220,38,38,.35);
    }

    .sp-artist-strip {
        display: flex; align-items: center; gap: var(--sp-sm);
        padding: var(--sp-sm) var(--sp-md); border: 1px solid var(--border);
        border-radius: var(--radius-md); background: #fafafa;
    }
    .sp-artist-ava {
        width: 38px; height: 38px; border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: var(--fs-sm);
        flex-shrink: 0; overflow: hidden;
    }
    .sp-artist-ava img { width: 100%; height: 100%; object-fit: cover; }
    .sp-artist-info { flex: 1; min-width: 0; }
    .sp-artist-name { font-size: var(--fs-base); font-weight: 600; color: var(--ink); }
    .sp-artist-role { font-size: var(--fs-sm); color: var(--muted); margin-top: 2px; }

    .sp-info-pane { padding: var(--sp-xl); display: flex; flex-direction: column; gap: 0; }

    .sp-title-row {
        display: flex; align-items: flex-start;
        justify-content: space-between; gap: var(--sp-md); margin-bottom: var(--sp-sm);
    }
    .sp-cat-tag {
        display: inline-block; font-size: var(--fs-sm); font-weight: 700;
        text-transform: uppercase; letter-spacing: .8px;
        color: var(--primary-2); background: var(--lavender);
        padding: 3px var(--sp-sm); border-radius: 20px; margin-bottom: var(--sp-sm);
    }
    .sp-title { font-size: var(--fs-lg); font-weight: 700; color: var(--ink); line-height: 1.3; flex: 1; min-width: 0; }

    .sp-no-rating {
        font-size: var(--fs-sm); color: var(--muted);
        padding-bottom: var(--sp-md); border-bottom: 1px solid var(--divider);
    }

    .sp-price-strip {
        background: linear-gradient(135deg, #f5f3ff 0%, #faf9ff 100%);
        padding: var(--sp-md) var(--sp-xl); margin: 0 calc(-1 * var(--sp-xl));
        display: flex; align-items: baseline; gap: var(--sp-xs);
        border-top: 1px solid #ece8ff; border-bottom: 1px solid #ece8ff;
    }
    .sp-price-label { font-size: var(--fs-sm); color: var(--muted); width: var(--label-w); flex-shrink: 0; }
    .sp-price-rm { font-size: var(--fs-base); font-weight: 700; color: var(--primary); align-self: flex-start; margin-top: 5px; }
    .sp-price-val {
        font-size: var(--fs-xl); font-weight: 800; line-height: 1;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .sp-price-strip.promo-active {
        background: linear-gradient(135deg, #fff5f5 0%, #fef9f9 100%);
        border-top-color: #fecaca; border-bottom-color: #fecaca; flex-wrap: wrap; row-gap: 4px;
    }
    .sp-promo-price-rm { font-size: var(--fs-base); font-weight: 700; color: #dc2626; align-self: flex-start; margin-top: 5px; }
    .sp-promo-price-val {
        font-size: var(--fs-xl); font-weight: 800; line-height: 1;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .sp-promo-original { align-self: flex-end; margin-bottom: 3px; font-size: var(--fs-base); color: var(--muted); text-decoration: line-through; margin-left: var(--sp-xs); }
    .sp-promo-badge-strip {
        align-self: flex-end; margin-bottom: 3px; margin-left: var(--sp-xs);
        background: #ef4444; color: #fff; font-size: 11px; font-weight: 800;
        padding: 2px 7px; border-radius: 20px; letter-spacing: .4px;
    }

    .sp-bulk-banner {
        display: flex; align-items: center; gap: var(--sp-sm);
        padding: var(--sp-sm) var(--sp-xl); margin: 0 calc(-1 * var(--sp-xl));
        background: var(--lavender); border-bottom: 1px solid #ddd6fe;
        font-size: var(--fs-sm); color: var(--primary-2);
    }
    .sp-bulk-banner i { color: var(--primary); font-size: 12px; flex-shrink: 0; }
    .sp-bulk-banner strong { font-weight: 700; }

    .sp-meta-section { padding: var(--sp-md) 0; border-bottom: 1px solid var(--divider); }
    .sp-row { display: flex; align-items: flex-start; padding: var(--sp-xs) 0; font-size: var(--fs-base); line-height: 1.5; }
    .sp-row-key { width: var(--label-w); flex-shrink: 0; color: var(--muted); font-weight: 400; }
    .sp-row-val { color: var(--ink); flex: 1; font-weight: 500; }
    .sp-row-val .in-stock { color: var(--success); font-weight: 600; }
    .sp-row-val .sold-out { color: #c62828; font-weight: 600; }

    .sp-purchase-section { padding: var(--sp-md) 0; border-bottom: 1px solid var(--divider); display: flex; flex-direction: column; gap: var(--sp-md); }

    .sp-total-price {
        font-size: var(--fs-xl); font-weight: 800; line-height: 1;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .sp-total-price.promo {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        -webkit-background-clip: text; background-clip: text;
    }

    .sp-cta-row { display: flex; gap: var(--sp-sm); padding-top: var(--sp-md); align-items: stretch; }
    .sp-cta-row-bulk { padding-top: var(--sp-xs); }

    .sp-btn {
        display: flex; align-items: center; justify-content: center; gap: var(--sp-xs);
        padding: 11px var(--sp-md); border-radius: var(--radius-sm);
        font-family: 'Inter', sans-serif; font-size: var(--fs-base); font-weight: 700;
        cursor: pointer; transition: all .15s; border: none; white-space: nowrap; text-decoration: none;
    }
    .sp-btn-cart { flex: 1; background: var(--lavender); color: var(--primary); border: 1.5px solid var(--primary); }
    .sp-btn-buy  { flex: 1; background: linear-gradient(135deg, var(--primary), var(--primary-2)); color: #fff; box-shadow: 0 3px 10px rgba(102,126,234,.28); }
    .sp-btn-bulk { width: 100%; background: #faf9ff; color: var(--primary); border: 1.5px solid var(--primary); justify-content: center; gap: var(--sp-sm); }
    .bulk-badge {
        display: inline-flex; align-items: center; gap: 4px;
        background: var(--lavender); color: var(--primary-2);
        padding: 2px var(--sp-sm); border-radius: 20px;
        font-size: var(--fs-sm); font-weight: 700; border: 1px solid #ddd6fe;
    }

    .sp-spec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
    .sp-spec-cell { display: flex; align-items: flex-start; padding: var(--sp-sm) 0; border-bottom: 1px solid var(--divider); font-size: var(--fs-base); }
    .sp-spec-cell:nth-last-child(-n+2) { border-bottom: none; }
    .sp-spec-cell:nth-child(odd) { padding-right: var(--sp-xl); }
    .sp-spec-key { width: var(--label-w); flex-shrink: 0; color: var(--muted); font-weight: 400; }
    .sp-spec-val { color: var(--ink); font-weight: 500; flex: 1; }

    .sp-desc { font-size: var(--fs-base); line-height: 1.8; color: #4b5563; white-space: pre-line; }

    .sp-no-reviews { text-align: center; padding: 36px var(--sp-lg); color: var(--muted); }
    .sp-no-reviews i { font-size: 2rem; color: #d1d5db; display: block; margin-bottom: var(--sp-sm); }
    .sp-no-reviews h4 { font-size: var(--fs-base); font-weight: 700; color: var(--ink); margin-bottom: var(--sp-xs); }
    .sp-no-reviews p { font-size: var(--fs-sm); }

    @media (max-width: 860px) {
        .sp-product-card { grid-template-columns: 1fr; }
        .sp-img-pane { border-right: none; border-bottom: 1px solid var(--divider); }
        .sp-price-strip, .sp-bulk-banner { margin: 0 calc(-1 * var(--sp-xl)); }
    }
    @media (max-width: 600px) {
        .sp-page { padding: var(--sp-sm) var(--sp-sm) 48px; }
        .sp-spec-grid { grid-template-columns: 1fr; }
        .sp-cta-row { flex-wrap: wrap; }
        .preview-banner-inner { flex-wrap: wrap; }
    }
</style>
@endsection

@section('content')

{{-- PREVIEW BANNER --}}
<div class="preview-banner" id="previewBanner">
    <div class="preview-banner-inner">
        <div class="preview-banner-left">
            <div class="preview-eye-icon"><i class="fas fa-eye"></i></div>
            <div>
                <span class="preview-label">Preview Mode</span>
                <span class="preview-sub">This is how buyers see your listing</span>
            </div>
        </div>
        <div class="preview-banner-right">
            <a href="{{ route('artist.profile') }}" class="preview-back-btn">
                <i class="fas fa-arrow-left"></i> Back to Studio
            </a>
            <a href="{{ route('artist.artwork.edit.page', $artwork->id) }}" class="preview-edit-btn">
                <i class="fas fa-pencil-alt"></i> Edit Listing
            </a>
        </div>
    </div>
</div>

{{-- BREADCRUMB --}}
<div class="bc-bar preview-bc" id="previewBcBar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <span class="cur">{{ Str::limit($artwork->product_name, 48) }}</span>
    </div>
</div>

@php
    $isSoldOut    = in_array(strtolower($artwork->status ?? ''), ['sold', 'sold_out']);
    $promoEnabled = (bool) ($artwork->getRawOriginal('promotion_enabled') ?? false);
    $promoDiscount= (float) ($artwork->getRawOriginal('promotion_discount') ?? 0);
    $origPrice    = (float) ($artwork->getRawOriginal('product_price') ?? 0);
    $promoStarts  = $artwork->getRawOriginal('promotion_starts_at');
    $promoEnds    = $artwork->getRawOriginal('promotion_ends_at');
    $now          = now();
    $promoActive  = $promoEnabled
                    && $promoDiscount > 0
                    && $origPrice > 0
                    && (!$promoStarts || $now->gte(\Carbon\Carbon::parse($promoStarts)))
                    && (!$promoEnds   || $now->lte(\Carbon\Carbon::parse($promoEnds)));
    $promoPrice   = $promoActive ? round($origPrice * (1 - $promoDiscount / 100), 2) : null;
    $unitPrice    = $promoPrice ?? $origPrice;

    $allImages = array_values(array_filter(
        array_merge([$artwork->image_path], $artwork->extra_images ?? [])
    ));
@endphp

<div class="preview-page-wrap" id="previewPageWrap">
<div class="sp-page">

    <a href="{{ route('artist.profile') }}" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to Studio
    </a>

    {{-- MAIN PRODUCT CARD --}}
    <div class="sp-card sp-product-card">

        <div class="sp-img-pane">
            <div class="sp-main-img">
                @if(count($allImages))
                    <img id="sp-main-img" src="{{ asset('storage/' . $allImages[0]) }}" alt="{{ $artwork->product_name }}">
                @else
                    <div class="sp-img-placeholder"><i class="fas fa-image"></i></div>
                @endif
                @if($promoPrice !== null)
                    <div class="sp-promo-img-badge">-{{ number_format($promoDiscount, 0) }}% OFF</div>
                @endif
            </div>

            @if(count($allImages) > 1)
            <div class="sp-thumbs">
                @foreach($allImages as $i => $img)
                <div class="sp-thumb {{ $i === 0 ? 'active' : '' }}" onclick="switchImg(this, '{{ asset('storage/' . $img) }}')">
                    <img src="{{ asset('storage/' . $img) }}" alt="Image {{ $i + 1 }}">
                </div>
                @endforeach
            </div>
            @endif

            @if($artwork->artist && $artwork->artist->user)
            @php
                $aUser = $artwork->artist->user;
                $aImg  = $artwork->artist->profile_image ?? $aUser->profile_image ?? null;
                $aInit = strtoupper(substr($aUser->fullname ?? 'A', 0, 1));
            @endphp
            <div class="sp-artist-strip">
                <div class="sp-artist-ava">
                    @if($aImg) <img src="{{ asset('storage/' . $aImg) }}" alt="">
                    @else {{ $aInit }} @endif
                </div>
                <div class="sp-artist-info">
                    <div class="sp-artist-name">{{ $aUser->fullname ?? 'Unknown Artist' }}</div>
                    <div class="sp-artist-role">Craftistry Artist</div>
                </div>
                <span style="cursor:default;opacity:.5;pointer-events:none;padding:6px 10px;border:1.5px solid var(--primary);border-radius:6px;color:var(--primary);font-size:12px;font-weight:600;">View Shop</span>
            </div>
            @endif
        </div>

        <div class="sp-info-pane">
            @if($artwork->artwork_type)
                <span class="sp-cat-tag">{{ ucfirst($artwork->artwork_type) }}</span>
            @endif

            <div class="sp-title-row">
                <h1 class="sp-title">{{ $artwork->product_name ?? 'Untitled Artwork' }}</h1>
            </div>

            <div class="sp-no-rating">
                <i class="fas fa-star" style="color:#e5e7eb;"></i> No reviews yet
            </div>

            @if($promoPrice !== null)
                <div class="sp-price-strip promo-active">
                    <span class="sp-price-label">Price</span>
                    <span class="sp-promo-price-rm">RM</span>
                    <span class="sp-promo-price-val">{{ number_format($promoPrice, 2) }}</span>
                    <span class="sp-promo-original">RM {{ number_format($artwork->product_price, 2) }}</span>
                    <span class="sp-promo-badge-strip">-{{ number_format($artwork->promotion_discount, 0) }}%</span>
                </div>
            @else
                <div class="sp-price-strip">
                    <span class="sp-price-label">Price</span>
                    <span class="sp-price-rm">RM</span>
                    <span class="sp-price-val">{{ number_format($artwork->product_price, 2) }}</span>
                </div>
            @endif

            @if($artwork->bulk_sell_enabled && $artwork->bulk_sell_min_qty && $artwork->bulk_sell_discount)
            <div class="sp-bulk-banner">
                <i class="fas fa-tags"></i>
                <span>Buy <strong>{{ $artwork->bulk_sell_min_qty }} or more</strong> and get <strong>{{ $artwork->bulk_sell_discount }}% off</strong> each item</span>
            </div>
            @endif

            <div class="sp-meta-section">
                <div class="sp-row">
                    <span class="sp-row-key">Shipping</span>
                    <span class="sp-row-val">
                        @if($artwork->shipping_fee && $artwork->shipping_fee > 0)
                            RM {{ number_format($artwork->shipping_fee, 2) }}
                        @else Free Shipping @endif
                    </span>
                </div>
                <div class="sp-row">
                    <span class="sp-row-key">Availability</span>
                    <span class="sp-row-val">
                        @if($isSoldOut) <span class="sold-out">Sold Out</span>
                        @else <span class="in-stock"><i class="fas fa-check-circle" style="font-size:11px;margin-right:3px;"></i>In Stock</span>
                        @endif
                    </span>
                </div>
                @if($artwork->material)
                <div class="sp-row">
                    <span class="sp-row-key">Material</span>
                    <span class="sp-row-val">{{ $artwork->material }}</span>
                </div>
                @endif
            </div>

            <div class="sp-purchase-section">
                <div class="sp-row" style="align-items:center;">
                    <span class="sp-row-key">Total</span>
                    <span class="sp-total-price{{ $promoPrice !== null ? ' promo' : '' }}">
                        RM {{ number_format($unitPrice, 2) }}
                    </span>
                </div>
            </div>

            <div class="preview-cta-disabled">
                <div class="sp-cta-row">
                    <button class="sp-btn sp-btn-cart" disabled><i class="fas fa-cart-plus"></i> Add to Cart</button>
                    <button class="sp-btn sp-btn-buy" disabled><i class="fas fa-bolt"></i> Buy Now</button>
                </div>
                @if(!$isSoldOut && $artwork->bulk_sell_enabled)
                <div class="sp-cta-row-bulk">
                    <a href="#" class="sp-btn sp-btn-bulk" onclick="return false;">
                        <i class="fas fa-boxes"></i> Bulk Order
                        @if($artwork->bulk_sell_min_qty && $artwork->bulk_sell_discount)
                        <span class="bulk-badge"><i class="fas fa-tag"></i> {{ $artwork->bulk_sell_discount }}% off &ge;{{ $artwork->bulk_sell_min_qty }} pcs</span>
                        @endif
                    </a>
                </div>
                @endif
                <div class="preview-cta-note">
                    <i class="fas fa-info-circle"></i>
                    Buttons are disabled in preview — buyers can interact with the real listing.
                </div>
            </div>
        </div>
    </div>

    @if($artwork->material || ($artwork->width && $artwork->height) || $artwork->artwork_type)
    <div class="sp-card">
        <div class="sp-card-header"><div class="hline"></div> Product Specifications</div>
        <div class="sp-card-body">
            <div class="sp-spec-grid">
                @if($artwork->artwork_type)
                <div class="sp-spec-cell"><span class="sp-spec-key">Type</span><span class="sp-spec-val">{{ ucfirst($artwork->artwork_type) }}</span></div>
                @endif
                @if($artwork->material)
                <div class="sp-spec-cell"><span class="sp-spec-key">Material</span><span class="sp-spec-val">{{ $artwork->material }}</span></div>
                @endif
                @if($artwork->width && $artwork->height)
                <div class="sp-spec-cell">
                    <span class="sp-spec-key">Dimensions</span>
                    <span class="sp-spec-val">{{ $artwork->width }} x {{ $artwork->height }}@if($artwork->depth) x {{ $artwork->depth }}@endif {{ $artwork->unit ?? 'cm' }}</span>
                </div>
                @endif
                <div class="sp-spec-cell"><span class="sp-spec-key">Status</span><span class="sp-spec-val">{{ $isSoldOut ? 'Sold Out' : 'Available' }}</span></div>
            </div>
        </div>
    </div>
    @endif

    @if($artwork->product_description)
    <div class="sp-card">
        <div class="sp-card-header"><div class="hline"></div> Product Description</div>
        <div class="sp-card-body"><p class="sp-desc">{{ $artwork->product_description }}</p></div>
    </div>
    @endif

    <div class="sp-card">
        <div class="sp-card-header"><div class="hline"></div> Product Ratings &amp; Reviews</div>
        <div class="sp-no-reviews">
            <i class="fas fa-comment-slash"></i>
            <h4>No Ratings Yet</h4>
            <p>Reviews from buyers will appear here after purchase.</p>
        </div>
    </div>

</div>
</div>

@endsection

@section('scripts')
<script>
function switchImg(thumb, src) {
    document.getElementById('sp-main-img').src = src;
    document.querySelectorAll('.sp-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

function positionBars() {
    const navbar = document.querySelector('.header');
    const banner = document.getElementById('previewBanner');
    const bcBar  = document.getElementById('previewBcBar');
    const wrap   = document.getElementById('previewPageWrap');

    if (!navbar || !banner || !bcBar || !wrap) return;

    const navH = navbar.offsetHeight;

    // Temporarily make fixed elements static to measure their natural heights
    const bannerPos = banner.style.position;
    const bcPos     = bcBar.style.position;
    banner.style.setProperty('position', 'relative', 'important');
    bcBar.style.setProperty('position', 'relative', 'important');

    const bannerH = banner.offsetHeight;
    const bcH     = bcBar.offsetHeight;

    // Restore fixed positioning
    banner.style.setProperty('position', 'fixed', 'important');
    banner.style.setProperty('top', navH + 'px', 'important');
    banner.style.setProperty('left', '0', 'important');
    banner.style.setProperty('right', '0', 'important');
    banner.style.setProperty('z-index', '200', 'important');

    bcBar.style.setProperty('position', 'fixed', 'important');
    bcBar.style.setProperty('top', (navH + bannerH) + 'px', 'important');
    bcBar.style.setProperty('left', '0', 'important');
    bcBar.style.setProperty('right', '0', 'important');
    bcBar.style.setProperty('z-index', '199', 'important');

    wrap.style.setProperty('padding-top', (navH + bannerH + bcH) + 'px', 'important');
}

window.addEventListener('load', positionBars);
window.addEventListener('resize', positionBars);
</script>
@endsection