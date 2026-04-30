@extends('layouts.app')

@section('title', 'Bulk Order — ' . $artwork->product_name)

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

    /* Breadcrumb */
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
    .bc-inner a { color: var(--muted); text-decoration: none; transition: color .15s; }
    .bc-inner a:hover { color: var(--primary); }
    .bc-inner .sep { color: #ccc; }
    .bc-inner .cur { color: var(--ink); font-weight: 500; }

    /* Page */
    .bo-page {
        max-width: 860px;
        margin: 0 auto;
        padding: var(--sp-md) var(--sp-lg) 60px;
        display: flex;
        flex-direction: column;
        gap: var(--sp-sm);
    }

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

    /* Card */
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

    /* Product summary strip */
    .bo-product-strip {
        display: flex;
        align-items: center;
        gap: var(--sp-md);
        padding: var(--sp-md);
        background: var(--bg);
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        margin-bottom: var(--sp-xl);
    }

    .bo-product-img {
        width: 64px;
        height: 64px;
        border-radius: var(--radius-sm);
        overflow: hidden;
        flex-shrink: 0;
        background: var(--divider);
        border: 1px solid var(--border);
    }

    .bo-product-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .bo-product-info { flex: 1; min-width: 0; }

    .bo-product-name {
        font-size: var(--fs-md);
        font-weight: 700;
        color: var(--ink);
        margin-bottom: 2px;
    }

    .bo-product-artist {
        font-size: var(--fs-sm);
        color: var(--muted);
    }

    .bo-product-price {
        font-size: var(--fs-md);
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        flex-shrink: 0;
    }

    /* Bulk deal info */
    .bo-deal-banner {
        display: flex;
        align-items: center;
        gap: var(--sp-sm);
        padding: var(--sp-sm) var(--sp-md);
        background: var(--lavender);
        border: 1px solid #ddd6fe;
        border-radius: var(--radius-md);
        font-size: var(--fs-sm);
        color: var(--primary-2);
        font-weight: 600;
        margin-bottom: var(--sp-xl);
    }

    .bo-deal-banner i { color: var(--primary); }

    /* Form */
    .bo-form { display: flex; flex-direction: column; gap: var(--sp-lg); }

    .form-group { display: flex; flex-direction: column; gap: var(--sp-xs); }

    .form-group label {
        font-size: var(--fs-base);
        font-weight: 600;
        color: var(--ink);
    }

    .form-group .hint {
        font-size: var(--fs-sm);
        color: var(--muted);
        margin-top: -2px;
    }

    .form-group input,
    .form-group textarea {
        padding: 10px var(--sp-md);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-family: 'Inter', sans-serif;
        font-size: var(--fs-base);
        color: var(--ink);
        background: var(--white);
        transition: border-color .15s;
        width: 100%;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(102,126,234,.1);
    }

    .form-group textarea { resize: vertical; line-height: 1.6; }

    .required { color: var(--danger); }

    /* Live price preview */
    .bo-price-preview {
        background: linear-gradient(135deg, #f5f3ff, #faf9ff);
        border: 1px solid #ece8ff;
        border-radius: var(--radius-md);
        padding: var(--sp-md) var(--sp-lg);
    }

    .bo-price-preview-title {
        font-size: var(--fs-sm);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--muted);
        margin-bottom: var(--sp-sm);
    }

    .bo-price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: var(--fs-base);
        padding: 3px 0;
    }

    .bo-price-row-key { color: var(--muted); }
    .bo-price-row-val { font-weight: 600; color: var(--ink); }

    .bo-price-row-val.discount { color: #16a34a; }

    .bo-price-divider { height: 1px; background: #ece8ff; margin: var(--sp-xs) 0; }

    .bo-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-top: var(--sp-xs);
    }

    .bo-total-label { font-size: var(--fs-base); font-weight: 700; color: var(--ink); }

    .bo-total-val {
        font-size: 22px;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), var(--primary-2));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Error */
    .field-error {
        font-size: var(--fs-sm);
        color: var(--danger);
        margin-top: 2px;
    }

    /* Submit */
    .bo-submit-row {
        display: flex;
        gap: var(--sp-sm);
        padding-top: var(--sp-xs);
    }

    .btn-cancel {
        padding: 11px var(--sp-lg);
        background: var(--divider);
        color: var(--muted);
        border: none;
        border-radius: var(--radius-sm);
        font-family: 'Inter', sans-serif;
        font-size: var(--fs-base);
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all .15s;
    }

    .btn-cancel:hover { background: var(--border); color: var(--ink); }

    .btn-submit {
        flex: 1;
        padding: 11px var(--sp-lg);
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
        box-shadow: 0 3px 10px rgba(102,126,234,.28);
        transition: all .15s;
    }

    .btn-submit:hover { opacity: .9; box-shadow: 0 5px 16px rgba(102,126,234,.38); }

    @media (max-width: 600px) {
        .bo-page { padding: var(--sp-sm) var(--sp-sm) 48px; }
        .bo-card-body { padding: var(--sp-md); }
        .bo-submit-row { flex-direction: column; }
        .btn-cancel { justify-content: center; }
    }
</style>
@endsection

@section('content')

<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.browse') }}">Artworks</a>
        <span class="sep">/</span>
        <a href="{{ route('product.show', $artwork->id) }}">{{ Str::limit($artwork->product_name, 30) }}</a>
        <span class="sep">/</span>
        <span class="cur">Bulk Order</span>
    </div>
</div>

<div class="bo-page">
    <a href="{{ route('product.show', $artwork->id) }}" class="back-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to Product
    </a>

    <div class="bo-card">
        <div class="bo-card-header">
            <div class="hline"></div>
            Bulk Order Request
        </div>

        <div class="bo-card-body">

            {{-- Product strip --}}
            <div class="bo-product-strip">
                <div class="bo-product-img">
                    @if($artwork->image_path)
                        <img src="{{ asset('storage/' . $artwork->image_path) }}" alt="{{ $artwork->product_name }}">
                    @endif
                </div>
                <div class="bo-product-info">
                    <div class="bo-product-name">{{ $artwork->product_name }}</div>
                    <div class="bo-product-artist">
                        by {{ $artwork->artist->user->fullname ?? 'Unknown Artist' }}
                    </div>
                </div>
                <div class="bo-product-price">RM {{ number_format($artwork->product_price, 2) }}</div>
            </div>

            {{-- Bulk deal info --}}
            @if($artwork->bulk_sell_min_qty && $artwork->bulk_sell_discount)
            <div class="bo-deal-banner">
                <i class="fas fa-tags"></i>
                Order <strong>{{ $artwork->bulk_sell_min_qty }} or more</strong> pieces to get
                <strong>{{ $artwork->bulk_sell_discount }}% off</strong> each item
            </div>
            @endif

            {{-- Errors --}}
            @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius-sm);padding:var(--sp-md);margin-bottom:var(--sp-lg);">
                <ul style="list-style:none;display:flex;flex-direction:column;gap:4px;">
                    @foreach($errors->all() as $error)
                        <li style="font-size:var(--fs-sm);color:var(--danger);"><i class="fas fa-exclamation-circle"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('bulk-orders.store', $artwork->id) }}" method="POST" class="bo-form" id="bo-form">
                @csrf

                <div class="form-group">
                    <label for="quantity">
                        Quantity <span class="required">*</span>
                    </label>
                    <div class="hint">
                        Minimum {{ $artwork->bulk_sell_min_qty ?? 1 }} pieces for bulk discount
                    </div>
                    <input type="number"
                           id="quantity"
                           name="quantity"
                           min="{{ $artwork->bulk_sell_min_qty ?? 1 }}"
                           value="{{ old('quantity', $artwork->bulk_sell_min_qty ?? 1) }}"
                           placeholder="Enter quantity"
                           oninput="updatePreview()"
                           required>
                    @error('quantity')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="last_ship_date">
                        Last Ship Date <span class="required">*</span>
                    </label>
                    <div class="hint">The latest date you need the order to be shipped by</div>
                    <input type="date"
                           id="last_ship_date"
                           name="last_ship_date"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           value="{{ old('last_ship_date') }}"
                           required>
                    @error('last_ship_date')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Additional Notes</label>
                    <div class="hint">Any special requirements, customisations, or notes for the seller</div>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              maxlength="1000"
                              placeholder="e.g. Preferred colour, packaging, delivery instructions...">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Live price preview --}}
                <div class="bo-price-preview">
                    <div class="bo-price-preview-title">Order Summary</div>
                    <div class="bo-price-row">
                        <span class="bo-price-row-key">Unit Price</span>
                        <span class="bo-price-row-val">RM {{ number_format($artwork->product_price, 2) }}</span>
                    </div>
                    <div class="bo-price-row" id="preview-discount-row" style="display:none;">
                        <span class="bo-price-row-key">Bulk Discount ({{ $artwork->bulk_sell_discount }}%)</span>
                        <span class="bo-price-row-val discount" id="preview-discount-val">− RM 0.00</span>
                    </div>
                    <div class="bo-price-row">
                        <span class="bo-price-row-key">Price per piece</span>
                        <span class="bo-price-row-val" id="preview-unit-price">RM {{ number_format($artwork->product_price, 2) }}</span>
                    </div>
                    <div class="bo-price-row">
                        <span class="bo-price-row-key">Quantity</span>
                        <span class="bo-price-row-val" id="preview-qty">{{ $artwork->bulk_sell_min_qty ?? 1 }}</span>
                    </div>
                    <div class="bo-price-divider"></div>
                    <div class="bo-total">
                        <span class="bo-total-label">Estimated Total</span>
                        <span class="bo-total-val" id="preview-total">
                            RM {{ number_format($artwork->product_price * ($artwork->bulk_sell_min_qty ?? 1), 2) }}
                        </span>
                    </div>
                </div>

                <div class="bo-submit-row">
                    <a href="{{ route('product.show', $artwork->id) }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        Submit Bulk Order Request
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    const UNIT_PRICE  = {{ $artwork->product_price }};
    const MIN_QTY     = {{ $artwork->bulk_sell_min_qty ?? 1 }};
    const DISCOUNT    = {{ $artwork->bulk_sell_discount ?? 0 }};

    function fmt(n) {
        return 'RM ' + n.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updatePreview() {
        const qty = parseInt(document.getElementById('quantity').value) || 0;
        const discountRow = document.getElementById('preview-discount-row');
        let unitPrice = UNIT_PRICE;

        if (DISCOUNT > 0 && qty >= MIN_QTY) {
            const saving = UNIT_PRICE * (DISCOUNT / 100);
            unitPrice    = UNIT_PRICE - saving;
            document.getElementById('preview-discount-val').textContent = '− ' + fmt(saving);
            discountRow.style.display = 'flex';
        } else {
            discountRow.style.display = 'none';
        }

        document.getElementById('preview-unit-price').textContent = fmt(unitPrice);
        document.getElementById('preview-qty').textContent         = qty;
        document.getElementById('preview-total').textContent       = fmt(unitPrice * qty);
    }

    // Init on load
    updatePreview();
</script>

@endsection