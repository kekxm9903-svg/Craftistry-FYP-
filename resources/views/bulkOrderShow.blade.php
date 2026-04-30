@extends('layouts.app')

@section('title', 'Bulk Order #' . $bulkOrder->id)

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary:   #667eea;
        --primary-2: #764ba2;
        --lavender:  #ede9fe;
        --ink:       #1a1a2e;
        --muted:     #6b6b8a;
        --border:    #e0e0ee;
        --divider:   #f0f0f5;
        --bg:        #f0f0f5;
        --white:     #ffffff;
        --success:   #2e7d32;
        --danger:    #dc2626;

        --fs-sm:   12px;
        --fs-base: 13px;
        --fs-md:   15px;
        --fs-lg:   18px;

        --sp-xs:  6px;
        --sp-sm:  10px;
        --sp-md:  16px;
        --sp-lg:  20px;
        --sp-xl:  24px;

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
        max-width: 860px;
        margin: 0 auto;
        padding: 0 var(--sp-lg);
        display: flex;
        align-items: center;
        gap: var(--sp-xs);
    }
    .bc-inner a { color: var(--muted); text-decoration: none; }
    .bc-inner a:hover { color: var(--primary); }
    .bc-inner .sep { color: #ccc; }
    .bc-inner .cur { color: var(--ink); font-weight: 500; }

    .bo-page {
        max-width: 860px;
        margin: 0 auto;
        padding: var(--sp-md) var(--sp-lg) 60px;
        display: flex;
        flex-direction: column;
        gap: var(--sp-sm);
    }

    .bo-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: 0 1px 3px rgba(0,0,0,.07);
        overflow: hidden;
    }

    .bo-card-header {
        padding: var(--sp-md) var(--sp-lg);
        border-bottom: 1px solid var(--divider);
        display: flex;
        align-items: center;
        gap: var(--sp-sm);
        font-size: var(--fs-base);
        font-weight: 700;
        color: var(--ink);
    }

    .hline {
        width: 3px;
        height: 14px;
        background: linear-gradient(180deg, var(--primary), var(--primary-2));
        border-radius: 2px;
        flex-shrink: 0;
    }

    .bo-card-body { padding: var(--sp-xl); }

    /* Status badge */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px var(--sp-sm);
        border-radius: 20px;
        font-size: var(--fs-sm);
        font-weight: 700;
    }
    .status-pending  { background: #fef3c7; color: #92400e; }
    .status-accepted { background: #d1fae5; color: #065f46; }
    .status-refused  { background: #fee2e2; color: #991b1b; }

    /* Detail rows */
    .bo-detail-grid {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: var(--sp-sm) var(--sp-lg);
        margin-bottom: var(--sp-xl);
    }

    .bo-detail-key { color: var(--muted); font-weight: 400; }
    .bo-detail-val { font-weight: 500; color: var(--ink); }

    /* Price summary */
    .bo-price-box {
        background: linear-gradient(135deg, #f5f3ff, #faf9ff);
        border: 1px solid #ece8ff;
        border-radius: var(--radius-md);
        padding: var(--sp-md) var(--sp-lg);
        margin-bottom: var(--sp-xl);
    }

    .bo-price-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        font-size: var(--fs-base);
    }

    .bo-price-row-key { color: var(--muted); }
    .bo-price-row-val { font-weight: 600; }
    .bo-price-row-val.green { color: #16a34a; }

    .bo-price-divider { height: 1px; background: #ece8ff; margin: var(--sp-xs) 0; }

    .bo-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-top: var(--sp-xs);
    }
    .bo-total-label { font-weight: 700; color: var(--ink); }
    .bo-total-val {
        font-size: 22px;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Notes */
    .bo-notes {
        background: var(--divider);
        border-radius: var(--radius-sm);
        padding: var(--sp-md);
        font-size: var(--fs-base);
        color: #4b5563;
        line-height: 1.7;
        white-space: pre-line;
        margin-bottom: var(--sp-xl);
    }

    /* Actions */
    .bo-actions {
        display: flex;
        gap: var(--sp-sm);
    }

    .btn-back {
        padding: 10px var(--sp-lg);
        background: var(--divider);
        color: var(--muted);
        border: none;
        border-radius: var(--radius-sm);
        font-family: 'Inter', sans-serif;
        font-size: var(--fs-base);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: var(--sp-xs);
        transition: all .15s;
    }
    .btn-back:hover { background: var(--border); color: var(--ink); }
</style>
@endsection

@section('content')

<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">Bulk Order #{{ $bulkOrder->id }}</span>
    </div>
</div>

<div class="bo-page">

    @if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:var(--radius-md);padding:var(--sp-md) var(--sp-lg);display:flex;align-items:center;gap:var(--sp-sm);font-size:var(--fs-base);font-weight:600;color:#065f46;">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    <div class="bo-card">
        <div class="bo-card-header">
            <div class="hline"></div>
            Bulk Order #{{ $bulkOrder->id }}
            <span class="status-pill status-{{ $bulkOrder->status }}" style="margin-left:auto;">
                @if($bulkOrder->status === 'pending')   <i class="fas fa-clock"></i>
                @elseif($bulkOrder->status === 'accepted') <i class="fas fa-check-circle"></i>
                @else <i class="fas fa-times-circle"></i>
                @endif
                {{ $bulkOrder->status_label }}
            </span>
        </div>

        <div class="bo-card-body">

            {{-- Product info --}}
            <div class="bo-detail-grid">
                <span class="bo-detail-key">Product</span>
                <span class="bo-detail-val">
                    <a href="{{ route('product.show', $bulkOrder->artworkSell->id) }}"
                       style="color:var(--primary);text-decoration:none;font-weight:600;">
                        {{ $bulkOrder->artworkSell->product_name }}
                    </a>
                </span>

                <span class="bo-detail-key">Seller</span>
                <span class="bo-detail-val">{{ $bulkOrder->artworkSell->artist->user->fullname ?? '—' }}</span>

                <span class="bo-detail-key">Quantity</span>
                <span class="bo-detail-val">{{ number_format($bulkOrder->quantity) }} pcs</span>

                <span class="bo-detail-key">Last Ship Date</span>
                <span class="bo-detail-val">{{ $bulkOrder->last_ship_date->format('d M Y') }}</span>

                <span class="bo-detail-key">Submitted</span>
                <span class="bo-detail-val">{{ $bulkOrder->created_at->format('d M Y, h:i A') }}</span>
            </div>

            {{-- Price breakdown --}}
            <div class="bo-price-box">
                <div class="bo-price-row">
                    <span class="bo-price-row-key">Unit Price</span>
                    <span class="bo-price-row-val">RM {{ number_format($bulkOrder->unit_price, 2) }}</span>
                </div>
                @if($bulkOrder->is_discounted)
                <div class="bo-price-row">
                    <span class="bo-price-row-key">Discounted Price per piece</span>
                    <span class="bo-price-row-val green">RM {{ number_format($bulkOrder->discounted_price, 2) }}</span>
                </div>
                @endif
                <div class="bo-price-row">
                    <span class="bo-price-row-key">Quantity</span>
                    <span class="bo-price-row-val">× {{ number_format($bulkOrder->quantity) }}</span>
                </div>
                <div class="bo-price-divider"></div>
                <div class="bo-total">
                    <span class="bo-total-label">Estimated Total</span>
                    <span class="bo-total-val">RM {{ number_format($bulkOrder->total_price, 2) }}</span>
                </div>
            </div>

            {{-- Notes --}}
            @if($bulkOrder->description)
            <div style="font-size:var(--fs-sm);font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:var(--sp-xs);">Notes</div>
            <div class="bo-notes">{{ $bulkOrder->description }}</div>
            @endif

            <div class="bo-actions">
                <a href="{{ route('artist.browse') }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Browse More
                </a>
            </div>

        </div>
    </div>

</div>

@endsection