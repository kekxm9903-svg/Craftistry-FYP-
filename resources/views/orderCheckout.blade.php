@extends('layouts.app')

@section('title', 'Checkout - Craftistry')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary: #667eea;
        --primary-dark: #764ba2;
        --text-dark: #1a202c;
        --text-gray: #718096;
        --bg-light: #f7fafc;
        --border-light: #e2e8f0;
        --green: #48bb78;
    }

    body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--text-dark); }

    .checkout-container {
        max-width: 960px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* Breadcrumb steps */
    .steps {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 36px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .step {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-gray);
    }

    .step.active { color: var(--primary); }
    .step.done   { color: var(--green); }

    .step-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        background: var(--border-light);
        color: var(--text-gray);
    }

    .step.active .step-num { background: var(--primary); color: white; }
    .step.done   .step-num { background: var(--green);   color: white; }

    .step-divider {
        width: 40px;
        height: 2px;
        background: var(--border-light);
        margin: 0 8px;
    }

    .step-divider.done { background: var(--green); }

    /* Page title */
    .page-title {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 6px;
    }

    .page-subtitle {
        color: var(--text-gray);
        font-size: 0.95rem;
        margin-bottom: 32px;
    }

    /* Grid */
    .checkout-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 24px;
        align-items: start;
    }

    /* Cards */
    .card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        margin-bottom: 20px;
    }

    .card:last-child { margin-bottom: 0; }

    .card-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 2px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-title i { color: var(--primary); }

    /* Order item rows */
    .order-item {
        display: flex;
        gap: 16px;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid var(--border-light);
    }

    .order-item:last-child { border-bottom: none; padding-bottom: 0; }

    .item-img {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid var(--border-light);
        flex-shrink: 0;
    }

    .item-img-placeholder {
        width: 64px;
        height: 64px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .item-details { flex: 1; }

    .item-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-bottom: 3px;
    }

    .item-meta {
        font-size: 0.8rem;
        color: var(--text-gray);
    }

    .item-subtotal {
        font-weight: 700;
        font-size: 1rem;
        color: var(--primary);
        white-space: nowrap;
    }

    /* Summary rows */
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-gray);
    }

    .summary-row:last-of-type { border-bottom: none; }
    .summary-row .val { font-weight: 600; color: var(--text-dark); }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.15rem;
        font-weight: 800;
        padding: 18px 0 0;
        margin-top: 6px;
        border-top: 2px solid var(--text-dark);
    }

    .summary-total .val { color: var(--primary); font-size: 1.4rem; }

    /* Pay button */
    .btn-pay {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 700;
        cursor: pointer;
        margin-top: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
    }

    .btn-pay:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102,126,234,0.4);
    }

    .secure-note {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.78rem;
        color: var(--text-gray);
        margin-top: 10px;
    }

    .secure-note i { color: var(--green); }

    /* Back link */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--text-gray);
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 600;
        margin-top: 14px;
        width: 100%;
        justify-content: center;
        transition: color 0.2s;
    }

    .back-link:hover { color: var(--primary); }

    /* Payment method badges */
    .payment-methods {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .pay-badge {
        background: var(--bg-light);
        border: 1px solid var(--border-light);
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-gray);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    @media (max-width: 768px) {
        .checkout-grid { grid-template-columns: 1fr; }
        .steps { font-size: 0.78rem; }
        .step-divider { width: 24px; }
    }
</style>
@endsection

@section('content')
<div class="checkout-container">

    {{-- Step indicator --}}
    <div class="steps">
        <div class="step done">
            <div class="step-num"><i class="fas fa-check" style="font-size:0.7rem;"></i></div>
            <span>Cart</span>
        </div>
        <div class="step-divider done"></div>
        <div class="step active">
            <div class="step-num">2</div>
            <span>Review Order</span>
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <div class="step-num">3</div>
            <span>Payment</span>
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <div class="step-num">4</div>
            <span>Confirmation</span>
        </div>
    </div>

    <div class="page-title">
        <i class="fas fa-receipt" style="color:var(--primary);"></i> Review Your Order
    </div>
    <p class="page-subtitle">Please confirm your items before proceeding to payment.</p>

    <div class="checkout-grid">

        {{-- Left: Order items --}}
        <div>
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-shopping-bag"></i>
                    Order Items ({{ count($cartItems) }})
                </div>

                @foreach($cartItems as $item)
                <div class="order-item">
                    @if($item['artwork']->image_path)
                        <img src="{{ asset('storage/' . $item['artwork']->image_path) }}"
                             alt="{{ $item['artwork']->product_name }}"
                             class="item-img">
                    @else
                        <div class="item-img-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    @endif

                    <div class="item-details">
                        <div class="item-name">{{ $item['artwork']->product_name ?? 'Artwork' }}</div>
                        <div class="item-meta">
                            Qty: {{ $item['quantity'] }}
                            &nbsp;·&nbsp;
                            RM {{ number_format($item['artwork']->product_price ?? $item['artwork']->price ?? 0, 2) }} each
                        </div>
                    </div>

                    <div class="item-subtotal">
                        RM {{ number_format($item['subtotal'], 2) }}
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Shipping info --}}
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-truck"></i>
                    Delivery
                </div>
                <p style="color:var(--text-gray); font-size:0.9rem; margin:0;">
                    The seller will contact you regarding delivery arrangements after your payment is confirmed.
                </p>
            </div>
        </div>

        {{-- Right: Payment summary --}}
        <div>
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-credit-card"></i>
                    Payment Summary
                </div>

                @foreach($cartItems as $item)
                <div class="summary-row">
                    <span>{{ $item['artwork']->product_name ?? 'Artwork' }} × {{ $item['quantity'] }}</span>
                    <span class="val">RM {{ number_format($item['subtotal'], 2) }}</span>
                </div>
                @endforeach

                <div class="summary-total">
                    <span>Total</span>
                    <span class="val">RM {{ number_format($total, 2) }}</span>
                </div>

                {{-- This form POSTs to Stripe via OrderCheckoutController@process --}}
                <form action="{{ route('order.checkout.process') }}" method="POST" id="pay-form">
                    @csrf
                    <button type="submit" class="btn-pay" id="pay-btn">
                        <i class="fab fa-stripe-s" id="pay-icon"></i>
                        <span id="pay-label">Pay RM {{ number_format($total, 2) }} with Stripe</span>
                    </button>
                </form>

                <div class="secure-note">
                    <i class="fas fa-shield-alt"></i>
                    Secured by Stripe &nbsp;·&nbsp; SSL Encrypted
                </div>

                <div class="payment-methods">
                    <span style="font-size:0.75rem; color:var(--text-gray); font-weight:600;">Accepts:</span>
                    <span class="pay-badge"><i class="fas fa-credit-card"></i> Card</span>
                    <span class="pay-badge">FPX</span>
                    <span class="pay-badge">GrabPay</span>
                </div>

                <a href="{{ route('cart.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Cart
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Show loading state on pay button submit — no disabled to avoid blocking form
document.getElementById('pay-form').addEventListener('submit', function () {
    const icon  = document.getElementById('pay-icon');
    const label = document.getElementById('pay-label');
    icon.className    = 'fas fa-spinner fa-spin';
    label.textContent = 'Redirecting to Stripe…';
});
</script>
@endsection