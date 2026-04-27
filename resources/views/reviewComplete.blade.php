@extends('layouts.app')

@section('title', 'Review Submitted!')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; background: #f7fafc; }

    .complete-container {
        max-width: 520px;
        margin: 60px auto;
        padding: 0 15px;
        text-align: center;
    }

    .complete-card {
        background: white;
        border-radius: 16px;
        padding: 40px 32px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    /* ── Star burst icon ── */
    .complete-icon {
        width: 88px;
        height: 88px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 2.2rem;
        color: white;
        box-shadow: 0 4px 20px rgba(102,126,234,0.4);
        animation: popIn 0.5s cubic-bezier(0.34,1.56,0.64,1);
    }
    @keyframes popIn {
        from { transform: scale(0.5); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }

    .complete-card h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
    }
    .complete-card > p {
        color: #718096;
        font-size: 0.95rem;
        margin-bottom: 28px;
        line-height: 1.6;
    }

    /* ── Stars display ── */
    .rating-display {
        display: flex;
        justify-content: center;
        gap: 4px;
        margin-bottom: 24px;
    }
    .rating-display i {
        font-size: 1.6rem;
        color: #f59e0b;
        animation: starPop 0.3s cubic-bezier(0.34,1.56,0.64,1) both;
    }
    .rating-display i:nth-child(1) { animation-delay: 0.05s; }
    .rating-display i:nth-child(2) { animation-delay: 0.10s; }
    .rating-display i:nth-child(3) { animation-delay: 0.15s; }
    .rating-display i:nth-child(4) { animation-delay: 0.20s; }
    .rating-display i:nth-child(5) { animation-delay: 0.25s; }
    @keyframes starPop {
        from { transform: scale(0); opacity: 0; }
        to   { transform: scale(1); opacity: 1; }
    }

    /* ── Order summary box ── */
    .order-summary-box {
        background: #f7fafc;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 28px;
        text-align: left;
    }

    .osb-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.87rem;
        padding: 7px 0;
        border-bottom: 1px solid #edf2f7;
        gap: 12px;
    }
    .osb-row:last-child { border-bottom: none; }
    .osb-label { color: #718096; font-weight: 500; flex-shrink: 0; }
    .osb-value { color: #1a202c; font-weight: 600; text-align: right; }
    .osb-value.purple { color: #667eea; }

    /* ── Artwork thumb in box ── */
    .osb-thumb-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #edf2f7;
    }
    .osb-thumb {
        width: 52px;
        height: 52px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
        background: #ede9fe;
    }
    .osb-thumb-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 8px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .osb-thumb-info strong {
        display: block;
        font-size: 0.9rem;
        color: #1a202c;
        margin-bottom: 2px;
    }
    .osb-thumb-info span {
        font-size: 0.78rem;
        color: #718096;
    }

    /* ── Review snippet ── */
    .review-snippet {
        background: #f0ebff;
        border-left: 3px solid #667eea;
        border-radius: 0 8px 8px 0;
        padding: 12px 16px;
        margin-bottom: 28px;
        text-align: left;
        font-size: 0.88rem;
        color: #4a5568;
        font-style: italic;
        line-height: 1.5;
    }
    .review-snippet.empty {
        color: #a0aec0;
        font-style: normal;
    }

    /* ── Buttons ── */
    .btn-group {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.92rem;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }
    .btn-primary:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 16px rgba(102,126,234,0.4); }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: white;
        color: #4a5568;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.92rem;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-secondary:hover { border-color: #667eea; color: #667eea; }

    /* ── Divider ── */
    .divider {
        border: none;
        border-top: 1px solid #edf2f7;
        margin: 24px 0;
    }

    .thank-note {
        font-size: 0.8rem;
        color: #a0aec0;
        margin-top: 20px;
    }
    .thank-note i { color: #667eea; }
</style>
@endsection

@section('content')
<div class="complete-container">
    <div class="complete-card">

        {{-- Icon --}}
        <div class="complete-icon">
            <i class="fas fa-star"></i>
        </div>

        <h1>Review Submitted!</h1>
        <p>Thank you for sharing your experience. Your feedback helps the Craftistry community discover great artists.</p>

        {{-- Stars --}}
        <div class="rating-display">
            @for($i = 1; $i <= 5; $i++)
                <i class="fas fa-star" style="{{ $i <= $review->rating ? 'color:#f59e0b;' : 'color:#e2e8f0;' }}"></i>
            @endfor
        </div>

        {{-- Order + artwork info --}}
        <div class="order-summary-box">

            {{-- Artwork row --}}
            <div class="osb-thumb-row">
                @if($order->artwork?->image_path)
                    <img src="{{ asset('storage/' . $order->artwork->image_path) }}"
                         class="osb-thumb" alt="Artwork">
                @else
                    <div class="osb-thumb-placeholder"><i class="fas fa-palette"></i></div>
                @endif
                <div class="osb-thumb-info">
                    <strong>{{ $order->artwork?->product_name ?? $order->title ?? 'Artwork Order' }}</strong>
                    <span>by {{ $order->artist->user->fullname ?? 'Artist' }}</span>
                </div>
            </div>

            <div class="osb-row">
                <span class="osb-label">Order ID</span>
                <span class="osb-value">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="osb-row">
                <span class="osb-label">Order Date</span>
                <span class="osb-value">{{ $order->created_at->format('d M Y') }}</span>
            </div>
            <div class="osb-row">
                <span class="osb-label">Amount Paid</span>
                <span class="osb-value purple">RM {{ number_format($order->total ?? 0, 2) }}</span>
            </div>
            <div class="osb-row">
                <span class="osb-label">Your Rating</span>
                <span class="osb-value">
                    @for($i = 1; $i <= $review->rating; $i++)⭐@endfor
                    ({{ $review->rating }}/5)
                </span>
            </div>
        </div>

        {{-- Review text snippet --}}
        @if($review->description)
            <div class="review-snippet">
                "{{ Str::limit($review->description, 120) }}"
            </div>
        @else
            <div class="review-snippet empty">
                No written review submitted.
            </div>
        @endif

        <hr class="divider">

        {{-- Actions --}}
        <div class="btn-group">
            <a href="{{ route('orders.index') }}" class="btn-primary">
                <i class="fas fa-box"></i> My Orders
            </a>
            <a href="{{ route('artist.browse') }}" class="btn-secondary">
                <i class="fas fa-palette"></i> Browse Artworks
            </a>
        </div>

        <p class="thank-note">
            <i class="fas fa-heart"></i>
            Your review supports Malaysian artists on Craftistry.
        </p>

    </div>
</div>
@endsection