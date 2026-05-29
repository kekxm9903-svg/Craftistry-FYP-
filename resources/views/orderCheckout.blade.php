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
        --red: #ef4444;
        --orange: #f59e0b;
    }

    body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--text-dark); }

    .checkout-container { max-width: 960px; margin: 40px auto; padding: 0 20px; }

    .steps { display: flex; align-items: center; gap: 0; margin-bottom: 36px; font-size: 0.85rem; font-weight: 600; }
    .step  { display: flex; align-items: center; gap: 8px; color: var(--text-gray); }
    .step.active { color: var(--primary); }
    .step.done   { color: var(--green); }
    .step-num {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.8rem; font-weight: 700;
        background: var(--border-light); color: var(--text-gray);
    }
    .step.active .step-num { background: var(--primary); color: white; }
    .step.done   .step-num { background: var(--green);   color: white; }
    .step-divider { width: 40px; height: 2px; background: var(--border-light); margin: 0 8px; }
    .step-divider.done { background: var(--green); }

    .page-title    { font-size: 1.8rem; font-weight: 800; color: var(--text-dark); margin-bottom: 6px; }
    .page-subtitle { color: var(--text-gray); font-size: 0.95rem; margin-bottom: 32px; }

    .checkout-grid {
        display: grid; grid-template-columns: 1fr 360px;
        gap: 24px; align-items: start;
    }

    .card {
        background: white; border-radius: 16px; padding: 28px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px;
    }
    .card:last-child { margin-bottom: 0; }
    .card-title {
        font-size: 1rem; font-weight: 700; color: var(--text-dark);
        margin-bottom: 20px; padding-bottom: 14px;
        border-bottom: 2px solid var(--border-light);
        display: flex; align-items: center; justify-content: space-between;
    }
    .card-title-left { display: flex; align-items: center; gap: 8px; }
    .card-title i { color: var(--primary); }

    .order-item {
        display: flex; gap: 16px; align-items: center;
        padding: 14px 0; border-bottom: 1px solid var(--border-light);
    }
    .order-item:last-child { border-bottom: none; padding-bottom: 0; }
    .item-img {
        width: 64px; height: 64px; border-radius: 10px;
        object-fit: cover; border: 1px solid var(--border-light); flex-shrink: 0;
    }
    .item-img-placeholder {
        width: 64px; height: 64px; border-radius: 10px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 1.4rem; flex-shrink: 0;
    }
    .item-details { flex: 1; }
    .item-name  { font-weight: 700; font-size: 0.95rem; color: var(--text-dark); margin-bottom: 3px; }
    .item-meta  { font-size: 0.8rem; color: var(--text-gray); }
    .item-subtotal { font-weight: 700; font-size: 1rem; color: var(--primary); white-space: nowrap; }

    .address-block {
        background: #f8fafc; border: 1px solid var(--border-light);
        border-radius: 10px; padding: 14px 16px;
        font-size: 0.88rem; color: var(--text-dark); line-height: 1.7;
    }
    .address-block .addr-name  { font-weight: 700; font-size: 0.93rem; margin-bottom: 2px; }
    .address-block .addr-line  { color: var(--text-gray); }

    .address-warning {
        display: flex; align-items: flex-start; gap: 12px;
        background: #fffbeb; border: 1.5px solid #fde68a;
        border-radius: 10px; padding: 14px 16px;
        font-size: 0.88rem; color: #92400e;
    }
    .address-warning i { font-size: 1.1rem; color: var(--orange); flex-shrink: 0; margin-top: 1px; }
    .address-warning a { color: var(--primary); font-weight: 700; text-decoration: none; }
    .address-warning a:hover { text-decoration: underline; }

    .summary-row {
        display: flex; justify-content: space-between; align-items: center;
        font-size: 0.9rem; padding: 10px 0;
        border-bottom: 1px solid var(--border-light); color: var(--text-gray);
    }
    .summary-row:last-of-type { border-bottom: none; }
    .summary-row .val { font-weight: 600; color: var(--text-dark); }
    .summary-row.shipping-row .val { color: #718096; }
    .summary-row.free-ship .val { color: var(--green); font-weight: 700; }
    .summary-divider { border: none; border-top: 1px solid var(--border-light); margin: 4px 0; }
    .summary-subtotal {
        display: flex; justify-content: space-between; align-items: center;
        font-size: 0.9rem; padding: 10px 0 6px;
        color: var(--text-gray);
    }
    .summary-subtotal .val { font-weight: 600; color: var(--text-dark); }
    .summary-total {
        display: flex; justify-content: space-between; align-items: center;
        font-size: 1.15rem; font-weight: 800;
        padding: 14px 0 0; margin-top: 4px;
        border-top: 2px solid var(--text-dark);
    }
    .summary-total .val { color: var(--primary); font-size: 1.4rem; }

    .btn-pay {
        width: 100%; padding: 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white; border: none; border-radius: 12px;
        font-size: 1.05rem; font-weight: 700; cursor: pointer;
        margin-top: 20px; display: flex; align-items: center;
        justify-content: center; gap: 10px; transition: all 0.2s;
    }
    .btn-pay:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }
    .btn-pay:disabled { background: #d1d5db; cursor: not-allowed; transform: none; box-shadow: none; }

    .secure-note {
        display: flex; align-items: center; justify-content: center;
        gap: 6px; font-size: 0.78rem; color: var(--text-gray); margin-top: 10px;
    }
    .secure-note i { color: var(--green); }

    .back-link {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--text-gray); text-decoration: none;
        font-size: 0.88rem; font-weight: 600; margin-top: 14px;
        width: 100%; justify-content: center; transition: color 0.2s;
    }
    .back-link:hover { color: var(--primary); }

    .payment-methods {
        display: flex; align-items: center; gap: 8px;
        flex-wrap: wrap; margin-top: 14px;
    }
    .pay-badge {
        background: var(--bg-light); border: 1px solid var(--border-light);
        border-radius: 6px; padding: 4px 10px; font-size: 0.75rem;
        font-weight: 600; color: var(--text-gray);
        display: flex; align-items: center; gap: 4px;
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

        {{-- ── LEFT: Order items + Shipping ── --}}
        <div>

            {{-- Order items --}}
            <div class="card">
                <div class="card-title">
                    <div class="card-title-left">
                        <i class="fas fa-shopping-bag"></i>
                        Order Items ({{ count($cartItems) }})
                    </div>
                </div>

                @foreach($cartItems as $item)
                <div class="order-item">
                    @if($item['artwork']->image_path)
                        <img src="{{ asset('storage/' . $item['artwork']->image_path) }}"
                             alt="{{ $item['artwork']->product_name }}" class="item-img">
                    @else
                        <div class="item-img-placeholder"><i class="fas fa-image"></i></div>
                    @endif

                    <div class="item-details">
                        <div class="item-name">{{ $item['artwork']->product_name ?? 'Artwork' }}</div>
                        <div class="item-meta">
                            Qty: {{ $item['quantity'] }}
                            &nbsp;·&nbsp;
                            RM {{ number_format($item['price'], 2) }} each
                            @if(in_array($item['artwork']->artwork_type ?? '', ['physical', 'both']))
                                &nbsp;·&nbsp;
                                <span style="color:#667eea;font-weight:600;">
                                    <i class="fas fa-box"></i> Physical
                                </span>
                            @else
                                &nbsp;·&nbsp;
                                <span style="color:#48bb78;font-weight:600;">
                                    <i class="fas fa-download"></i> Digital
                                </span>
                            @endif
                            @if(($item['shipping_fee'] ?? 0) > 0)
                                &nbsp;·&nbsp;
                                <span style="color:var(--text-gray);">
                                    <i class="fas fa-truck"></i> +RM {{ number_format($item['shipping_fee'], 2) }} shipping
                                </span>
                            @else
                                &nbsp;·&nbsp;
                                <span style="color:#48bb78;font-weight:600;">
                                    <i class="fas fa-truck"></i> Free Shipping
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="item-subtotal">RM {{ number_format($item['subtotal'], 2) }}</div>
                </div>
                @endforeach
            </div>

            {{-- Shipping address card --}}
            @if($needsAddress)
            <div class="card">
                <div class="card-title">
                    <div class="card-title-left">
                        <i class="fas fa-map-marker-alt"></i>
                        Shipping Address
                    </div>
                    @if($hasAddress)
                        <a href="{{ route('user.profile.edit') }}"
                           style="font-size:0.8rem;font-weight:600;color:var(--primary);text-decoration:none;">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                    @endif
                </div>

                @if($hasAddress)
                    <div class="address-block">
                        <div class="addr-name">{{ $user->fullname }}</div>
                        <div class="addr-line">{{ $user->address }}</div>
                        <div class="addr-line">
                            {{ $user->city }}, {{ $user->state }} {{ $user->postcode }}
                        </div>
                        @if($user->phone)
                            <div class="addr-line" style="margin-top:4px;">
                                <i class="fas fa-phone" style="font-size:0.75rem;"></i>
                                {{ $user->phone }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="address-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Shipping address required.</strong><br>
                            Your order contains physical items. Please
                            <a href="{{ route('user.profile.edit') }}">set your shipping address</a>
                            before placing the order.
                        </div>
                    </div>
                @endif
            </div>
            @endif

            {{-- Delivery note --}}
            @if(!$needsAddress || $hasAddress)
            <div class="card">
                <div class="card-title">
                    <div class="card-title-left">
                        <i class="fas fa-truck"></i> Delivery
                    </div>
                </div>
                <p style="color:var(--text-gray); font-size:0.9rem; margin:0;">
                    @if($needsAddress)
                        The seller will arrange delivery to your address after payment is confirmed.
                    @else
                        Your digital items will be available after payment is confirmed.
                    @endif
                </p>
            </div>
            @endif

        </div>

        {{-- ── RIGHT: Payment summary ── --}}
        <div>
            <div class="card">
                <div class="card-title">
                    <div class="card-title-left">
                        <i class="fas fa-credit-card"></i>
                        Payment Summary
                    </div>
                </div>

                @php
                    $subtotalOnly = collect($cartItems)->sum(fn($i) => $i['subtotal']);
                    $shippingOnly = collect($cartItems)->sum(fn($i) => $i['shipping_fee'] ?? 0);
                @endphp

                {{-- Item rows --}}
                @foreach($cartItems as $item)
                <div class="summary-row">
                    <span>{{ Str::limit($item['artwork']->product_name ?? 'Artwork', 24) }} × {{ $item['quantity'] }}</span>
                    <span class="val">RM {{ number_format($item['subtotal'], 2) }}</span>
                </div>
                @endforeach

                {{-- Shipping row --}}
                @if($shippingOnly > 0)
                <div class="summary-row shipping-row">
                    <span><i class="fas fa-truck" style="font-size:0.75rem;margin-right:4px;"></i>Shipping Fee</span>
                    <span class="val">RM {{ number_format($shippingOnly, 2) }}</span>
                </div>
                @else
                <div class="summary-row free-ship">
                    <span><i class="fas fa-truck" style="font-size:0.75rem;margin-right:4px;"></i>Shipping Fee</span>
                    <span class="val">Free</span>
                </div>
                @endif

                {{-- Total --}}
                <div class="summary-total">
                    <span>Total</span>
                    <span class="val">RM {{ number_format($total, 2) }}</span>
                </div>

                <form action="{{ route('order.checkout.process') }}" method="POST" id="pay-form">
                    @csrf
                    <button type="submit" class="btn-pay" id="pay-btn"
                        {{ ($needsAddress && !$hasAddress) ? 'disabled' : '' }}>
                        <i class="fab fa-stripe-s" id="pay-icon"></i>
                        <span id="pay-label">
                            @if($needsAddress && !$hasAddress)
                                Set Address to Continue
                            @else
                                Pay RM {{ number_format($total, 2) }} with Stripe
                            @endif
                        </span>
                    </button>
                </form>

                @if($needsAddress && !$hasAddress)
                    <p style="text-align:center;font-size:0.78rem;color:var(--orange);margin-top:10px;font-weight:600;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Please set your shipping address first.
                    </p>
                @else
                    <div class="secure-note">
                        <i class="fas fa-shield-alt"></i>
                        Secured by Stripe &nbsp;·&nbsp; SSL Encrypted
                    </div>
                @endif

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
document.getElementById('pay-form').addEventListener('submit', function (e) {
    const btn = document.getElementById('pay-btn');
    if (btn.disabled) { e.preventDefault(); return; }
    const icon  = document.getElementById('pay-icon');
    const label = document.getElementById('pay-label');
    icon.className    = 'fas fa-spinner fa-spin';
    label.textContent = 'Redirecting to Stripe…';
});
</script>
@endsection