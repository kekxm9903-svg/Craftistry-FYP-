@extends('layouts.app')

@section('title', $artwork->product_name . ' - Craftistry')

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

    .bc-bar {
        background: var(--white);
        border-bottom: 1px solid var(--border);
        padding: var(--sp-xs) 0;
        font-size: var(--fs-sm);
        color: var(--muted);
    }
    .bc-inner {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 var(--sp-lg);
        display: flex;
        align-items: center;
        gap: var(--sp-xs);
    }
    .bc-inner a { color: var(--muted); text-decoration: none; transition: color .15s; }
    .bc-inner a:hover { color: var(--primary); }
    .bc-inner .sep { color: #ccc; }
    .bc-inner .cur { color: var(--ink); font-weight: 500; }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: var(--sp-xs);
        color: var(--muted);
        text-decoration: none;
        font-size: var(--fs-base);
        font-weight: 600;
        transition: color .15s;
        margin-bottom: var(--sp-xs);
    }
    .back-btn:hover { color: var(--primary); }
    .back-btn svg { flex-shrink: 0; }

    .sp-page {
        max-width: 1100px;
        margin: 0 auto;
        padding: var(--sp-md) var(--sp-lg) 60px;
        display: flex;
        flex-direction: column;
        gap: var(--sp-sm);
    }

    .sp-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: 0 1px 3px rgba(0,0,0,.07);
        overflow: hidden;
    }
    .sp-card-header {
        padding: var(--sp-md) var(--sp-lg);
        font-size: var(--fs-base);
        font-weight: 700;
        color: var(--ink);
        border-bottom: 1px solid var(--divider);
        display: flex;
        align-items: center;
        gap: var(--sp-sm);
    }
    .sp-card-header .hline {
        width: 3px;
        height: 14px;
        background: linear-gradient(180deg, var(--primary), var(--primary-2));
        border-radius: 2px;
        flex-shrink: 0;
    }
    .sp-card-body { padding: var(--sp-lg); }

    .sp-product-card {
        display: grid;
        grid-template-columns: 360px 1fr;
    }

    .sp-img-pane {
        padding: var(--sp-lg);
        border-right: 1px solid var(--divider);
        display: flex;
        flex-direction: column;
        gap: var(--sp-sm);
    }

    .sp-main-img {
        width: 100%;
        aspect-ratio: 1;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
        background: #fafafa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sp-main-img img { width: 100%; height: 100%; object-fit: contain; display: block; }
    .sp-img-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #ede9fe, #ddd6fe);
        color: var(--primary-2);
        font-size: 3.5rem;
    }

    .sp-artist-strip {
        display: flex;
        align-items: center;
        gap: var(--sp-sm);
        padding: var(--sp-sm) var(--sp-md);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        background: #fafafa;
    }
    .sp-artist-ava {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: var(--fs-sm);
        flex-shrink: 0; overflow: hidden;
    }
    .sp-artist-ava img { width: 100%; height: 100%; object-fit: cover; }
    .sp-artist-info { flex: 1; min-width: 0; }
    .sp-artist-name { font-size: var(--fs-base); font-weight: 600; color: var(--ink); }
    .sp-artist-role { font-size: var(--fs-sm); color: var(--muted); margin-top: 2px; }
    .sp-artist-btn {
        flex-shrink: 0;
        padding: var(--sp-xs) var(--sp-sm);
        border: 1.5px solid var(--primary);
        border-radius: var(--radius-sm);
        color: var(--primary);
        font-size: var(--fs-sm);
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: all .15s;
    }
    .sp-artist-btn:hover { background: var(--primary); color: #fff; }

    .sp-info-pane {
        padding: var(--sp-xl);
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .sp-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: var(--sp-md);
        margin-bottom: var(--sp-sm);
    }

    .sp-cat-tag {
        display: inline-block;
        font-size: var(--fs-sm);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--primary-2);
        background: var(--lavender);
        padding: 3px var(--sp-sm);
        border-radius: 20px;
        margin-bottom: var(--sp-sm);
    }

    .sp-title {
        font-size: var(--fs-lg);
        font-weight: 700;
        color: var(--ink);
        line-height: 1.3;
        flex: 1;
        min-width: 0;
    }

    /* ── Favourite button ── */
    .btn-fav-product {
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 1.5px solid var(--border);
        background: var(--white);
        color: var(--muted);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        cursor: pointer;
        transition: all .2s;
        position: relative;
        top: 2px;
    }
    .btn-fav-product:hover {
        border-color: #fca5a5;
        background: #fef2f2;
        color: #ef4444;
    }
    .btn-fav-product.is-fav {
        border-color: #fca5a5;
        background: #fef2f2;
        color: #ef4444;
    }
    .btn-fav-product.is-fav:hover {
        background: #ef4444;
        color: #fff;
        border-color: #ef4444;
    }
    .btn-fav-product.loading {
        pointer-events: none;
        opacity: .6;
    }

    .sp-rating-row {
        display: flex;
        align-items: center;
        gap: var(--sp-sm);
        padding-bottom: var(--sp-md);
        border-bottom: 1px solid var(--divider);
    }
    .sp-stars { display: flex; gap: 2px; }
    .sp-stars i { font-size: var(--fs-sm); }
    .sp-rating-score {
        font-size: var(--fs-base);
        font-weight: 700;
        color: var(--ink);
        border-bottom: 1px solid var(--ink);
        line-height: 1.2;
    }
    .sp-dot { color: #ddd; font-size: 8px; }
    .sp-rating-link { font-size: var(--fs-sm); color: var(--muted); text-decoration: none; }
    .sp-rating-link:hover { color: var(--primary); }

    .sp-price-strip {
        background: linear-gradient(135deg, #f5f3ff 0%, #faf9ff 100%);
        padding: var(--sp-md) var(--sp-xl);
        margin: 0 calc(-1 * var(--sp-xl));
        display: flex;
        align-items: baseline;
        gap: var(--sp-xs);
        border-top: 1px solid #ece8ff;
        border-bottom: 1px solid #ece8ff;
    }
    .sp-price-label {
        font-size: var(--fs-sm);
        color: var(--muted);
        width: var(--label-w);
        flex-shrink: 0;
    }
    .sp-price-rm {
        font-size: var(--fs-base);
        font-weight: 700;
        color: var(--primary);
        align-self: flex-start;
        margin-top: 5px;
    }
    .sp-price-val {
        font-size: var(--fs-xl);
        font-weight: 800;
        line-height: 1;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .sp-bulk-banner {
        display: flex;
        align-items: center;
        gap: var(--sp-sm);
        padding: var(--sp-sm) var(--sp-xl);
        margin: 0 calc(-1 * var(--sp-xl));
        background: var(--lavender);
        border-bottom: 1px solid #ddd6fe;
        font-size: var(--fs-sm);
        color: var(--primary-2);
    }
    .sp-bulk-banner i { color: var(--primary); font-size: 12px; flex-shrink: 0; }
    .sp-bulk-banner strong { font-weight: 700; }

    .sp-meta-section {
        padding: var(--sp-md) 0;
        border-bottom: 1px solid var(--divider);
    }
    .sp-row {
        display: flex;
        align-items: flex-start;
        padding: var(--sp-xs) 0;
        font-size: var(--fs-base);
        line-height: 1.5;
    }
    .sp-row-key {
        width: var(--label-w);
        flex-shrink: 0;
        color: var(--muted);
        font-weight: 400;
    }
    .sp-row-val { color: var(--ink); flex: 1; font-weight: 500; }
    .sp-row-val .in-stock  { color: var(--success); font-weight: 600; }
    .sp-row-val .sold-out  { color: #c62828; font-weight: 600; }

    .sp-purchase-section {
        padding: var(--sp-md) 0;
        border-bottom: 1px solid var(--divider);
        display: flex;
        flex-direction: column;
        gap: var(--sp-md);
    }
    .sp-qty-stepper {
        display: inline-flex;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        overflow: hidden;
    }
    .sp-qty-stepper.disabled { opacity: .4; pointer-events: none; }
    .sp-qty-btn {
        width: 32px; height: 32px;
        background: #fafafa;
        border: none;
        font-size: 1rem;
        font-weight: 600;
        color: var(--ink);
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background .12s;
    }
    .sp-qty-btn + .sp-qty-num { border-left: 1px solid var(--border); }
    .sp-qty-num + .sp-qty-btn { border-left: 1px solid var(--border); }
    .sp-qty-btn:hover:not(:disabled) { background: var(--lavender); color: var(--primary); }
    .sp-qty-btn:disabled { opacity: .35; cursor: not-allowed; }
    .sp-qty-num {
        width: 52px; height: 32px;
        text-align: center;
        font-size: var(--fs-base);
        font-weight: 600;
        color: var(--ink);
        border: none;
        outline: none;
        background: transparent;
        font-family: 'Inter', sans-serif;
        -moz-appearance: textfield;
    }
    .sp-qty-num::-webkit-inner-spin-button,
    .sp-qty-num::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .sp-qty-note { font-size: var(--fs-sm); color: var(--muted); margin-left: var(--sp-sm); }

    .sp-total-price {
        font-size: var(--fs-xl);
        font-weight: 800;
        line-height: 1;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .sp-total-hint {
        font-size: var(--fs-sm);
        color: var(--muted);
        margin-left: var(--sp-sm);
        font-weight: 400;
    }

    .sp-cta-row {
        display: flex;
        gap: var(--sp-sm);
        padding-top: var(--sp-md);
        align-items: stretch;
    }
    .sp-cta-row-bulk { padding-top: var(--sp-xs); }

    .sp-btn {
        display: flex; align-items: center; justify-content: center; gap: var(--sp-xs);
        padding: 11px var(--sp-md);
        border-radius: var(--radius-sm);
        font-family: 'Inter', sans-serif;
        font-size: var(--fs-base);
        font-weight: 700;
        cursor: pointer;
        transition: all .15s;
        border: none;
        white-space: nowrap;
        text-decoration: none;
    }
    .sp-btn-cart {
        flex: 1;
        background: var(--lavender);
        color: var(--primary);
        border: 1.5px solid var(--primary);
    }
    .sp-btn-cart:hover { background: #ddd6fe; }
    .sp-btn-cart:disabled { opacity: .5; cursor: not-allowed; }
    .sp-btn-cart.added { background: #ecfdf5; border-color: var(--success); color: var(--success); }

    .sp-btn-buy {
        flex: 1;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        color: #fff;
        box-shadow: 0 3px 10px rgba(102,126,234,.28);
    }
    .sp-btn-buy:hover { opacity: .9; box-shadow: 0 5px 16px rgba(102,126,234,.38); }
    .sp-btn-buy:disabled { opacity: .5; cursor: not-allowed; box-shadow: none; }

    .sp-btn-bulk {
        width: 100%;
        background: #faf9ff;
        color: var(--primary);
        border: 1.5px solid var(--primary);
        justify-content: center;
        gap: var(--sp-sm);
        box-shadow: none;
    }
    .sp-btn-bulk:hover { background: var(--lavender); color: var(--primary-2); }

    .bulk-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--lavender);
        color: var(--primary-2);
        padding: 2px var(--sp-sm);
        border-radius: 20px;
        font-size: var(--fs-sm);
        font-weight: 700;
        border: 1px solid #ddd6fe;
    }

    .sp-sold-notice {
        display: flex; align-items: center; gap: var(--sp-xs);
        background: #fff5f5; border: 1px solid #fecaca;
        border-radius: var(--radius-sm); padding: 11px var(--sp-md);
        color: #dc2626; font-weight: 700; font-size: var(--fs-base);
        flex: 1;
    }
    .sp-btn-sold {
        flex: 1; padding: 11px var(--sp-md);
        background: #e5e7eb; color: #9ca3af;
        border: none; border-radius: var(--radius-sm);
        font-family: 'Inter', sans-serif;
        font-size: var(--fs-base); font-weight: 700;
        cursor: not-allowed;
        display: flex; align-items: center; justify-content: center; gap: var(--sp-xs);
    }

    .sp-spec-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    .sp-spec-cell {
        display: flex;
        align-items: flex-start;
        padding: var(--sp-sm) 0;
        border-bottom: 1px solid var(--divider);
        font-size: var(--fs-base);
    }
    .sp-spec-cell:nth-last-child(-n+2) { border-bottom: none; }
    .sp-spec-cell:nth-child(odd) { padding-right: var(--sp-xl); }
    .sp-spec-key { width: var(--label-w); flex-shrink: 0; color: var(--muted); font-weight: 400; }
    .sp-spec-val { color: var(--ink); font-weight: 500; flex: 1; }

    .sp-desc {
        font-size: var(--fs-base);
        line-height: 1.8;
        color: #4b5563;
        white-space: pre-line;
    }

    .sp-reviews-summary {
        display: flex;
        align-items: center;
        gap: var(--sp-xl);
        padding: var(--sp-lg) var(--sp-xl);
        background: linear-gradient(135deg, #f5f3ff, #faf9ff);
        border-bottom: 1px solid #ece8ff;
    }
    .sp-score-block { text-align: center; flex-shrink: 0; }
    .sp-score-num {
        font-size: 2.4rem;
        font-weight: 800;
        line-height: 1;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .sp-score-stars { display: flex; justify-content: center; gap: 3px; margin: var(--sp-xs) 0; }
    .sp-score-stars i { font-size: 13px; }
    .sp-score-label { font-size: var(--fs-sm); color: var(--muted); }

    .sp-bars { flex: 1; display: flex; flex-direction: column; gap: var(--sp-xs); }
    .sp-bar-row { display: flex; align-items: center; gap: var(--sp-sm); font-size: var(--fs-sm); }
    .sp-bar-star { width: 30px; text-align: right; color: var(--muted); flex-shrink: 0; }
    .sp-bar-track { flex: 1; height: 6px; background: var(--divider); border-radius: 10px; overflow: hidden; }
    .sp-bar-fill { height: 100%; background: linear-gradient(90deg, var(--primary), var(--primary-2)); border-radius: 10px; }
    .sp-bar-count { width: 20px; color: var(--muted); flex-shrink: 0; }

    .sp-review-list { padding: 0 var(--sp-xl); }
    .sp-review-item { padding: var(--sp-md) 0; border-bottom: 1px solid var(--divider); }
    .sp-review-item:last-child { border-bottom: none; }

    .sp-reviewer-row { display: flex; align-items: center; gap: var(--sp-sm); margin-bottom: var(--sp-sm); }
    .sp-reviewer-ava {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 700; font-size: var(--fs-sm);
        flex-shrink: 0; overflow: hidden;
    }
    .sp-reviewer-ava img { width: 100%; height: 100%; object-fit: cover; }
    .sp-reviewer-name { font-size: var(--fs-base); font-weight: 600; color: var(--ink); }
    .sp-reviewer-meta { display: flex; align-items: center; gap: var(--sp-xs); margin-top: 2px; }
    .sp-rev-stars { display: flex; gap: 1px; }
    .sp-rev-stars i { font-size: 11px; }
    .sp-rev-date { font-size: var(--fs-sm); color: var(--muted); }

    .sp-review-text { font-size: var(--fs-base); color: #4b5563; line-height: 1.7; margin-bottom: var(--sp-sm); }

    .sp-review-imgs { display: flex; gap: var(--sp-xs); flex-wrap: wrap; }
    .sp-rev-img {
        width: 76px; height: 76px;
        border-radius: var(--radius-sm); overflow: hidden;
        cursor: pointer; position: relative;
        border: 1px solid var(--border);
    }
    .sp-rev-img img, .sp-rev-img video { width: 100%; height: 100%; object-fit: cover; }
    .sp-rev-vid-ov {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(0,0,0,.28);
    }
    .sp-rev-vid-ov i { color: #fff; font-size: var(--fs-base); }

    .sp-no-reviews { text-align: center; padding: 36px var(--sp-lg); color: var(--muted); }
    .sp-no-reviews i { font-size: 2rem; color: #d1d5db; display: block; margin-bottom: var(--sp-sm); }
    .sp-no-reviews h4 { font-size: var(--fs-base); font-weight: 700; color: var(--ink); margin-bottom: var(--sp-xs); }
    .sp-no-reviews p { font-size: var(--fs-sm); }

    .lightbox { display: none; position: fixed; inset: 0; z-index: 9998; background: rgba(0,0,0,.93); align-items: center; justify-content: center; padding: var(--sp-xl); }
    .lightbox.open { display: flex; }
    .lightbox img, .lightbox video { max-width: 90vw; max-height: 85vh; border-radius: var(--radius-md); object-fit: contain; }
    .lightbox-close { position: absolute; top: var(--sp-md); right: var(--sp-lg); color: #fff; font-size: 1.6rem; cursor: pointer; background: none; border: none; line-height: 1; opacity: .7; transition: opacity .15s; }
    .lightbox-close:hover { opacity: 1; }

    .toast {
        position: fixed; bottom: var(--sp-xl); right: var(--sp-xl);
        background: var(--ink); color: #fff;
        padding: var(--sp-sm) var(--sp-md);
        border-radius: var(--radius-md);
        font-size: var(--fs-base); font-weight: 600;
        display: flex; align-items: center; gap: var(--sp-sm);
        box-shadow: 0 6px 20px rgba(0,0,0,.18);
        transform: translateY(80px); opacity: 0;
        transition: all .32s cubic-bezier(.34,1.56,.64,1);
        z-index: 9999; max-width: 300px;
    }
    .toast.show { transform: translateY(0); opacity: 1; }
    .t-success { color: #34d399; }
    .t-info    { color: #60a5fa; }
    .t-heart   { color: #f87171; }
    .toast-link { color: #a78bfa; font-weight: 700; font-size: var(--fs-sm); text-decoration: none; margin-left: var(--sp-xs); white-space: nowrap; }

    @media (max-width: 860px) {
        .sp-product-card { grid-template-columns: 1fr; }
        .sp-img-pane { border-right: none; border-bottom: 1px solid var(--divider); }
        .sp-price-strip, .sp-bulk-banner { margin: 0 calc(-1 * var(--sp-xl)); }
    }
    @media (max-width: 600px) {
        .sp-page { padding: var(--sp-sm) var(--sp-sm) 48px; }
        .sp-spec-grid { grid-template-columns: 1fr; }
        .sp-cta-row { flex-wrap: wrap; }
        .sp-reviews-summary { flex-direction: column; gap: var(--sp-md); }
    }
</style>
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.browse') }}">Artworks</a>
        @if($artwork->artist?->user)
            <span class="sep">/</span>
            <a href="{{ route('artist.browse.show', $artwork->artist->user_id) }}">
                {{ $artwork->artist->user->fullname ?? 'Artist' }}
            </a>
        @endif
        <span class="sep">/</span>
        <span class="cur">{{ Str::limit($artwork->product_name, 48) }}</span>
    </div>
</div>

{{-- Buy Now hidden form --}}
<form id="buy-now-form" action="{{ route('order.checkout.show') }}" method="GET" style="display:none;">
    <input type="hidden" name="buy_now"    value="1">
    <input type="hidden" name="artwork_id" value="{{ $artwork->id }}">
    <input type="hidden" name="qty"        id="buy-now-qty" value="1">
</form>

@php
    $isSoldOut  = in_array(strtolower($artwork->status ?? ''), ['sold', 'sold_out']);
    $isFavorited = auth()->check() && auth()->user()->favoriteProducts->contains($artwork->id);
@endphp

<div class="sp-page">
    <a href="javascript:history.back()" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back
    </a>

    {{-- ══ MAIN PRODUCT CARD ══ --}}
    <div class="sp-card sp-product-card">

        {{-- Image pane --}}
        <div class="sp-img-pane">
            <div class="sp-main-img">
                @if($artwork->image_path)
                    <img src="{{ asset('storage/' . $artwork->image_path) }}" alt="{{ $artwork->product_name }}">
                @else
                    <div class="sp-img-placeholder"><i class="fas fa-image"></i></div>
                @endif
            </div>

            @if($artwork->artist && $artwork->artist->user)
            @php
                $aUser = $artwork->artist->user;
                $aImg  = $artwork->artist->profile_image ?? $aUser->profile_image ?? null;
                $aInit = strtoupper(substr($aUser->fullname ?? 'A', 0, 1));
            @endphp
            <div class="sp-artist-strip">
                <div class="sp-artist-ava">
                    @if($aImg)
                        <img src="{{ asset('storage/' . $aImg) }}" alt="">
                    @else
                        {{ $aInit }}
                    @endif
                </div>
                <div class="sp-artist-info">
                    <div class="sp-artist-name">{{ $aUser->fullname ?? 'Unknown Artist' }}</div>
                    <div class="sp-artist-role">
                        @php $sellerRating = $aUser->seller_rating; @endphp
                        @if($sellerRating > 0)
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star" style="font-size:10px;color:{{ $i <= round($sellerRating) ? '#f59e0b' : '#e5e7eb' }}"></i>
                            @endfor
                            <span style="font-size:11px;color:var(--muted);margin-left:3px;">{{ number_format($sellerRating, 1) }}</span>
                        @else
                            <span>Craftistry Artist</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('artist.browse.show', $artwork->artist->user_id) }}" class="sp-artist-btn">
                    View Shop
                </a>
            </div>
            @endif
        </div>

        {{-- Info pane --}}
        <div class="sp-info-pane">

            @if($artwork->artwork_type)
                <span class="sp-cat-tag">{{ ucfirst($artwork->artwork_type) }}</span>
            @endif

            {{-- Title row with favourite button --}}
            <div class="sp-title-row">
                <h1 class="sp-title">{{ $artwork->product_name ?? 'Untitled Artwork' }}</h1>

                @auth
                <button class="btn-fav-product {{ $isFavorited ? 'is-fav' : '' }}"
                        id="btn-fav-product"
                        data-url="{{ route('product.favorite', $artwork->id) }}"
                        title="{{ $isFavorited ? 'Remove from favourites' : 'Add to favourites' }}"
                        aria-label="Toggle favourite"
                        onclick="toggleProductFav(this)">
                    <i class="fas fa-heart"></i>
                </button>
                @endauth
            </div>

            @if($reviewCount > 0)
            <div class="sp-rating-row">
                <span class="sp-rating-score">{{ $averageRating }}</span>
                <div class="sp-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="color:{{ $i <= round($averageRating) ? '#f59e0b' : '#e5e7eb' }}"></i>
                    @endfor
                </div>
                <span class="sp-dot">●</span>
                <a href="#sp-reviews" class="sp-rating-link">{{ $reviewCount }} Rating{{ $reviewCount !== 1 ? 's' : '' }}</a>
            </div>
            @endif

            {{-- Price --}}
            @if($artwork->product_price)
            <div class="sp-price-strip">
                <span class="sp-price-label">Price</span>
                <span class="sp-price-rm">RM</span>
                <span class="sp-price-val">{{ number_format($artwork->product_price, 2) }}</span>
            </div>
            @endif

            {{-- Bulk deal banner --}}
            @if($artwork->bulk_sell_enabled && $artwork->bulk_sell_min_qty && $artwork->bulk_sell_discount)
            <div class="sp-bulk-banner">
                <i class="fas fa-tags"></i>
                <span>Buy <strong>{{ $artwork->bulk_sell_min_qty }} or more</strong> and get <strong>{{ $artwork->bulk_sell_discount }}% off</strong> each item</span>
            </div>
            @endif

            {{-- Meta rows --}}
            <div class="sp-meta-section">
                <div class="sp-row">
                    <span class="sp-row-key">Shipping</span>
                    <span class="sp-row-val">
                        @if($artwork->shipping_fee && $artwork->shipping_fee > 0)
                            RM {{ number_format($artwork->shipping_fee, 2) }}
                        @else
                            Free Shipping
                        @endif
                    </span>
                </div>
                <div class="sp-row">
                    <span class="sp-row-key">Availability</span>
                    <span class="sp-row-val">
                        @if($isSoldOut)
                            <span class="sold-out">Sold Out</span>
                        @else
                            <span class="in-stock"><i class="fas fa-check-circle" style="font-size:11px;margin-right:3px;"></i>In Stock</span>
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

            {{-- Qty + total --}}
            <div class="sp-purchase-section">
                @if(!$isSoldOut)
                <div class="sp-row" style="align-items:center;">
                    <span class="sp-row-key">Quantity</span>
                    <div style="display:flex;align-items:center;">
                        <div class="sp-qty-stepper">
                            <button class="sp-qty-btn" id="qty-minus" onclick="changeQty(-1)" disabled>−</button>
                            <input class="sp-qty-num" id="qty-value"
                                   type="number" value="1" min="1"
                                   oninput="handleQtyInput(this)"
                                   onblur="handleQtyBlur(this)">
                            <button class="sp-qty-btn" id="qty-plus" onclick="changeQty(1)">+</button>
                        </div>
                        <span class="sp-qty-note">Available</span>
                    </div>
                </div>
                @endif

                <div class="sp-row" style="align-items:center;">
                    <span class="sp-row-key">Total</span>
                    <div style="display:flex;align-items:baseline;gap:var(--sp-xs);">
                        <span class="sp-total-price" id="total-price">
                            RM {{ number_format($artwork->product_price ?? 0, 2) }}
                        </span>
                        <span class="sp-total-hint" id="total-hint" style="display:none;">
                            (RM {{ number_format($artwork->product_price ?? 0, 2) }} × <span id="qty-hint-val">1</span>)
                        </span>
                        @if($artwork->bulk_sell_enabled && $artwork->bulk_sell_min_qty && $artwork->bulk_sell_discount)
                        <span id="bulk-discount-note" style="display:none;font-size:var(--fs-sm);color:var(--primary-2);font-weight:600;">
                            — {{ $artwork->bulk_sell_discount }}% bulk discount applied
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CTA row 1: Add to Cart + Buy Now --}}
            <div class="sp-cta-row">
                @if($isSoldOut)
                    <div class="sp-sold-notice"><i class="fas fa-ban"></i> This artwork has been sold</div>
                    <button class="sp-btn-sold" disabled><i class="fas fa-shopping-cart"></i> Sold Out</button>
                @else
                    <button class="sp-btn sp-btn-cart" id="add-cart-btn" onclick="handleAddToCart({{ $artwork->id }})">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                    <button class="sp-btn sp-btn-buy" id="buy-now-btn" onclick="handlePurchase()">
                        <i class="fas fa-bolt"></i> Buy Now
                    </button>
                @endif
            </div>

            {{-- CTA row 2: Bulk Order --}}
            @if(!$isSoldOut && $artwork->bulk_sell_enabled)
            <div class="sp-cta-row-bulk">
                @auth
                <a href="{{ route('bulk-orders.create', $artwork->id) }}" class="sp-btn sp-btn-bulk">
                    <i class="fas fa-boxes"></i>
                    Bulk Order
                    @if($artwork->bulk_sell_min_qty && $artwork->bulk_sell_discount)
                    <span class="bulk-badge">
                        <i class="fas fa-tag"></i>
                        {{ $artwork->bulk_sell_discount }}% off ≥{{ $artwork->bulk_sell_min_qty }} pcs
                    </span>
                    @endif
                </a>
                @else
                <a href="{{ route('login') }}" class="sp-btn sp-btn-bulk">
                    <i class="fas fa-boxes"></i>
                    Bulk Order
                    @if($artwork->bulk_sell_min_qty && $artwork->bulk_sell_discount)
                    <span class="bulk-badge">
                        <i class="fas fa-tag"></i>
                        {{ $artwork->bulk_sell_discount }}% off ≥{{ $artwork->bulk_sell_min_qty }} pcs
                    </span>
                    @endif
                </a>
                @endauth
            </div>
            @endif

        </div>{{-- /sp-info-pane --}}
    </div>{{-- /sp-product-card --}}

    {{-- ══ SPECS ══ --}}
    @if($artwork->material || ($artwork->width && $artwork->height) || $artwork->artwork_type)
    <div class="sp-card">
        <div class="sp-card-header"><div class="hline"></div> Product Specifications</div>
        <div class="sp-card-body">
            <div class="sp-spec-grid">
                @if($artwork->artwork_type)
                <div class="sp-spec-cell">
                    <span class="sp-spec-key">Type</span>
                    <span class="sp-spec-val">{{ ucfirst($artwork->artwork_type) }}</span>
                </div>
                @endif
                @if($artwork->material)
                <div class="sp-spec-cell">
                    <span class="sp-spec-key">Material</span>
                    <span class="sp-spec-val">{{ $artwork->material }}</span>
                </div>
                @endif
                @if($artwork->width && $artwork->height)
                <div class="sp-spec-cell">
                    <span class="sp-spec-key">Dimensions</span>
                    <span class="sp-spec-val">
                        {{ $artwork->width }} × {{ $artwork->height }}
                        @if($artwork->depth) × {{ $artwork->depth }} @endif
                        {{ $artwork->unit ?? 'cm' }}
                    </span>
                </div>
                @endif
                <div class="sp-spec-cell">
                    <span class="sp-spec-key">Status</span>
                    <span class="sp-spec-val">{{ $isSoldOut ? 'Sold Out' : 'Available' }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ DESCRIPTION ══ --}}
    @if($artwork->product_description)
    <div class="sp-card">
        <div class="sp-card-header"><div class="hline"></div> Product Description</div>
        <div class="sp-card-body">
            <p class="sp-desc">{{ $artwork->product_description }}</p>
        </div>
    </div>
    @endif

    {{-- ══ REVIEWS ══ --}}
    <div class="sp-card" id="sp-reviews">
        <div class="sp-card-header">
            <div class="hline"></div>
            Product Ratings &amp; Reviews
            @if($reviewCount > 0)
                <span style="font-size:var(--fs-sm);color:var(--muted);font-weight:500;">({{ $reviewCount }})</span>
            @endif
        </div>

        @if($reviewCount > 0)
        <div class="sp-reviews-summary">
            <div class="sp-score-block">
                <div class="sp-score-num">{{ $averageRating }}</div>
                <div class="sp-score-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="color:{{ $i <= round($averageRating) ? '#f59e0b' : '#e5e7eb' }}"></i>
                    @endfor
                </div>
                <div class="sp-score-label">out of 5</div>
            </div>
            <div class="sp-bars">
                @foreach($starCounts as $star => $count)
                @php $pct = $reviewCount > 0 ? ($count / $reviewCount) * 100 : 0; @endphp
                <div class="sp-bar-row">
                    <span class="sp-bar-star">{{ $star }} <i class="fas fa-star" style="color:#f59e0b;font-size:9px;"></i></span>
                    <div class="sp-bar-track"><div class="sp-bar-fill" style="width:{{ $pct }}%"></div></div>
                    <span class="sp-bar-count">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="sp-review-list">
            @foreach($reviews as $review)
            @php
                $dName  = $review->is_anonymous ? 'Anonymous' : ($review->user->fullname ?? 'Buyer');
                $init   = strtoupper(substr($dName, 0, 1));
                $avatar = (!$review->is_anonymous && ($review->user->profile_image ?? null)) ? $review->user->profile_image : null;
            @endphp
            <div class="sp-review-item">
                <div class="sp-reviewer-row">
                    <div class="sp-reviewer-ava">
                        @if($avatar)
                            <img src="{{ asset('storage/' . $avatar) }}" alt="{{ $dName }}">
                        @else
                            {{ $init }}
                        @endif
                    </div>
                    <div>
                        <div class="sp-reviewer-name">{{ $dName }}</div>
                        <div class="sp-reviewer-meta">
                            <div class="sp-rev-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star" style="color:{{ $i <= $review->rating ? '#f59e0b' : '#e5e7eb' }}"></i>
                                @endfor
                            </div>
                            <span class="sp-rev-date">· {{ $review->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
                @if($review->description)
                    <p class="sp-review-text">{{ $review->description }}</p>
                @endif
                @if($review->image_path || $review->video_path)
                <div class="sp-review-imgs">
                    @if($review->image_path)
                    <div class="sp-rev-img" onclick="openLightbox('image','{{ asset('storage/' . $review->image_path) }}')">
                        <img src="{{ asset('storage/' . $review->image_path) }}" alt="">
                    </div>
                    @endif
                    @if($review->video_path)
                    <div class="sp-rev-img" onclick="openLightbox('video','{{ asset('storage/' . $review->video_path) }}')">
                        <video muted preload="metadata"><source src="{{ asset('storage/' . $review->video_path) }}"></video>
                        <div class="sp-rev-vid-ov"><i class="fas fa-play"></i></div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>

        @else
        <div class="sp-no-reviews">
            <i class="fas fa-comment-slash"></i>
            <h4>No Ratings Yet</h4>
            <p>Be the first to rate this artwork after purchasing.</p>
        </div>
        @endif
    </div>

</div>{{-- /sp-page --}}

{{-- Lightbox --}}
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
    <img id="lb-img" src="" alt="" style="display:none;">
    <video id="lb-video" controls style="display:none;"><source id="lb-vsrc" src=""></video>
</div>

{{-- Toast --}}
<div class="toast" id="toast">
    <i id="toast-icon" class="fas fa-check-circle t-success"></i>
    <span id="toast-msg">Added to cart!</span>
    <a href="{{ route('cart.index') }}" class="toast-link" id="toast-link">View Cart →</a>
</div>

<script>
    const UNIT_PRICE    = {{ $artwork->product_price ?? 0 }};
    const BULK_ENABLED  = {{ $artwork->bulk_sell_enabled ? 'true' : 'false' }};
    const BULK_MIN_QTY  = {{ $artwork->bulk_sell_min_qty ?? 0 }};
    const BULK_DISCOUNT = {{ $artwork->bulk_sell_discount ?? 0 }};
    let qty = 1;

    function updateQtyDisplay() {
        const input = document.getElementById('qty-value');
        input.value = qty;
        document.getElementById('qty-minus').disabled = qty <= 1;

        let unitPrice = UNIT_PRICE;
        const bulkNote = document.getElementById('bulk-discount-note');
        if (BULK_ENABLED && BULK_MIN_QTY > 0 && qty >= BULK_MIN_QTY) {
            unitPrice = UNIT_PRICE * (1 - BULK_DISCOUNT / 100);
            if (bulkNote) bulkNote.style.display = 'inline';
        } else {
            if (bulkNote) bulkNote.style.display = 'none';
        }

        const total = unitPrice * qty;
        document.getElementById('total-price').textContent =
            'RM ' + total.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const hint = document.getElementById('total-hint');
        if (qty > 1) { hint.style.display = 'inline'; document.getElementById('qty-hint-val').textContent = qty; }
        else         { hint.style.display = 'none'; }
    }

    function changeQty(delta) {
        qty = Math.max(1, qty + delta);
        updateQtyDisplay();
    }

    function handleQtyInput(input) {
        const val = parseInt(input.value, 10);
        if (!isNaN(val) && val >= 1) {
            qty = val;
            updateQtyDisplay();
        }
    }

    function handleQtyBlur(input) {
        const val = parseInt(input.value, 10);
        qty = (!isNaN(val) && val >= 1) ? val : 1;
        updateQtyDisplay();
    }

    function handleAddToCart(artworkId) {
        const btn = document.getElementById('add-cart-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ artwork_id: artworkId, quantity: qty })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.classList.add('added');
                btn.innerHTML = '<i class="fas fa-check"></i> Added (' + qty + ')';
                if (typeof window.updateCartBadge === 'function') window.updateCartBadge(data.cart_count);
                showToast(data.message || 'Added to cart!', 'success', true);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
                showToast(data.message || 'Could not add to cart.', 'info', false);
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
            showToast('Something went wrong.', 'info', false);
        });
    }

    function handlePurchase() {
        document.getElementById('buy-now-qty').value = qty;
        document.getElementById('buy-now-form').submit();
    }

    // ── Toggle product favourite ──────────────────────────────────────────────
    async function toggleProductFav(btn) {
        if (btn.classList.contains('loading')) return;
        btn.classList.add('loading');

        const url      = btn.dataset.url;
        const wasFav   = btn.classList.contains('is-fav');

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept':       'application/json',
                },
            });

            if (!res.ok) throw new Error('Request failed');

            const data = await res.json();

            if (data.favorited) {
                btn.classList.add('is-fav');
                btn.title = 'Remove from favourites';
                showToast('Added to your favourites!', 'heart', false);
            } else {
                btn.classList.remove('is-fav');
                btn.title = 'Add to favourites';
                showToast('Removed from favourites', 'info', false);
            }
        } catch (err) {
            showToast('Something went wrong. Please try again.', 'info', false);
        } finally {
            btn.classList.remove('loading');
        }
    }

    function showToast(message, type = 'success', showLink = true) {
        const toast = document.getElementById('toast');
        document.getElementById('toast-msg').textContent = message;

        const iconEl = document.getElementById('toast-icon');
        if (type === 'heart')        iconEl.className = 'fas fa-heart t-heart';
        else if (type === 'info')    iconEl.className = 'fas fa-info-circle t-info';
        else                         iconEl.className = 'fas fa-check-circle t-success';

        document.getElementById('toast-link').style.display = showLink ? 'inline' : 'none';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    function openLightbox(type, src) {
        const lb  = document.getElementById('lightbox');
        const img = document.getElementById('lb-img');
        const vid = document.getElementById('lb-video');
        const vs  = document.getElementById('lb-vsrc');
        if (type === 'image') { img.src = src; img.style.display = 'block'; vid.style.display = 'none'; vid.pause(); }
        else { vs.src = src; vid.load(); vid.style.display = 'block'; img.style.display = 'none'; }
        lb.classList.add('open');
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('open');
        document.getElementById('lb-video').pause();
        document.getElementById('lb-img').src = '';
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
@endsection