@extends('layouts.app')

@section('title', 'My Cart - Craftistry')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/cart.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">My Cart</span>
    </div>
</div>

<div class="cart-page">

    {{-- Error banner --}}
    @if(session('error'))
        <div class="error-banner">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- ══ PAGE HEADER CARD ══ --}}
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-shopping-cart"></i>
            My Cart
            @if(count($cartItems) > 0)
                <span class="item-count-tag">{{ count($cartItems) }} item{{ count($cartItems) > 1 ? 's' : '' }}</span>
            @endif
        </h1>
        <a href="{{ route('artist.browse') }}" class="continue-btn">
            <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
    </div>

    @if(count($cartItems) > 0)

        <div class="cart-layout">

            {{-- ══ CART ITEMS CARD ══ --}}
            <div class="sp-card">
                <div class="sp-card-header">
                    <div class="sp-card-header-left">
                        <div class="hline"></div>
                        Cart Items
                    </div>
                </div>

                <div class="cart-items-header">
                    <span>Artwork</span>
                    <span style="text-align:center">Qty</span>
                    <span>Price</span>
                    <span></span>
                </div>

                @foreach($cartItems as $item)
                @php $itemShipping = (float)($item['shipping_fee'] ?? 0); @endphp

                <div class="cart-item" id="cart-item-{{ $item['id'] }}">

                    <div class="item-info">
                        @if(!empty($item['image_path']))
                            <img src="{{ asset('storage/' . $item['image_path']) }}"
                                 alt="{{ $item['name'] }}" class="item-thumb">
                        @else
                            <div class="item-thumb-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                        <div>
                            <div class="item-name">{{ $item['name'] }}</div>
                            @if(!empty($item['artist_name']))
                                <div class="item-artist">by {{ $item['artist_name'] }}</div>
                            @endif
                            @if(!empty($item['artwork_type']))
                                <div class="item-type-badge">{{ ucfirst($item['artwork_type']) }}</div>
                            @endif
                            <div>
                                @if($itemShipping > 0)
                                    <span class="item-shipping-pill paid">
                                        <i class="fas fa-truck"></i>
                                        Ship: RM {{ number_format($itemShipping, 2) }}
                                    </span>
                                @else
                                    <span class="item-shipping-pill free">
                                        <i class="fas fa-truck"></i>
                                        Free Shipping
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="item-qty">
                        <button class="qty-btn" onclick="updateQty({{ $item['id'] }}, -1)">−</button>
                        <span class="qty-value" id="qty-{{ $item['id'] }}">{{ $item['quantity'] }}</span>
                        <button class="qty-btn" onclick="updateQty({{ $item['id'] }}, 1)">+</button>
                    </div>

                    <div class="item-price-col">
                        <div class="item-price" id="price-{{ $item['id'] }}">
                            RM {{ number_format($item['price'] * $item['quantity'], 2) }}
                        </div>
                        @if($itemShipping > 0)
                            <div class="item-shipping-note">+ RM {{ number_format($itemShipping, 2) }} shipping</div>
                        @else
                            <div class="item-shipping-note free">Free shipping</div>
                        @endif
                    </div>

                    <button class="remove-btn" onclick="removeItem({{ $item['id'] }})" title="Remove">
                        <i class="fas fa-trash-alt"></i>
                    </button>

                </div>
                @endforeach
            </div>

            {{-- ══ ORDER SUMMARY PANEL ══ --}}
            <div class="summary-panel">
                <div class="sp-card-header">
                    <div class="sp-card-header-left">
                        <div class="hline"></div>
                        Order Summary
                    </div>
                </div>
                <div class="summary-body">

                    <div class="summary-line">
                        <span class="label">Subtotal ({{ count($cartItems) }} items)</span>
                        <span class="value" id="summary-subtotal">RM {{ number_format($subtotal, 2) }}</span>
                    </div>

                    <div class="summary-line">
                        <span class="label">
                            <i class="fas fa-truck" style="color:var(--primary);margin-right:4px;"></i>Shipping
                        </span>
                        <span class="value {{ $shipping == 0 ? 'free' : '' }}" id="summary-shipping">
                            {{ $shipping > 0 ? 'RM ' . number_format($shipping, 2) : 'Free' }}
                        </span>
                        <div class="shipping-breakdown">
                            @foreach($cartItems as $item)
                                <div class="shipping-breakdown-row">
                                    <span class="seller">{{ $item['artist_name'] ?? 'Unknown Seller' }}</span>
                                    @if(($item['shipping_fee'] ?? 0) > 0)
                                        <span class="fee">RM {{ number_format($item['shipping_fee'], 2) }}</span>
                                    @else
                                        <span class="fee free">Free</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="summary-line">
                        <span class="label">Tax (SST)</span>
                        <span class="value">—</span>
                    </div>

                    <div class="summary-total">
                        <span class="label">Total</span>
                        <span class="value" id="summary-total">RM {{ number_format($total, 2) }}</span>
                    </div>

                    <a href="{{ route('order.checkout.show') }}" class="checkout-btn">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </a>

                    <button class="clear-cart-btn" onclick="clearCart()">
                        <i class="fas fa-trash"></i> Clear Cart
                    </button>

                    <div class="summary-note">
                        <i class="fas fa-shield-alt"></i>
                        Secure checkout powered by Stripe
                    </div>

                </div>
            </div>

        </div>

    @else

        {{-- ══ EMPTY CART ══ --}}
        <div class="sp-card">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart empty-cart-icon"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any artworks yet. Browse our collection and find something you love!</p>
                <a href="{{ route('artist.browse') }}" class="browse-btn">
                    <i class="fas fa-search"></i> Browse Artworks
                </a>
            </div>
        </div>

    @endif

</div>

{{-- Toast --}}
<div class="toast" id="toast">
    <i class="fas fa-check-circle" id="toast-icon"></i>
    <span id="toast-msg">Done.</span>
</div>

@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function updateQty(artworkId, delta) {
    fetch('{{ route("cart.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ artwork_id: artworkId, delta: delta })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.removed) {
                document.getElementById('cart-item-' + artworkId).remove();
                showToast('Item removed from cart.', 'danger');
                if (data.cart_count === 0) location.reload();
            } else {
                document.getElementById('qty-'   + artworkId).textContent = data.new_qty;
                document.getElementById('price-' + artworkId).textContent = 'RM ' + data.item_total;
            }
            document.getElementById('summary-subtotal').textContent = 'RM ' + data.subtotal;
            updateShippingDisplay(data.shipping);
            document.getElementById('summary-total').textContent = 'RM ' + data.cart_total;
        }
    });
}

function removeItem(artworkId) {
    fetch('{{ route("cart.remove") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ artwork_id: artworkId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cart-item-' + artworkId).remove();
            document.getElementById('summary-subtotal').textContent = 'RM ' + data.subtotal;
            updateShippingDisplay(data.shipping);
            document.getElementById('summary-total').textContent = 'RM ' + data.cart_total;
            showToast('Item removed from cart.', 'danger');
            if (data.cart_count === 0) setTimeout(() => location.reload(), 1000);
        }
    });
}

function updateShippingDisplay(shippingValue) {
    const el  = document.getElementById('summary-shipping');
    const val = parseFloat(shippingValue) || 0;
    if (val === 0) {
        el.textContent = 'Free';
        el.classList.add('free');
    } else {
        el.textContent = 'RM ' + parseFloat(shippingValue).toFixed(2);
        el.classList.remove('free');
    }
}

function clearCart() {
    if (!confirm('Clear all items from your cart?')) return;
    fetch('{{ route("cart.clear") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
}

function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    const icon  = document.getElementById('toast-icon');
    document.getElementById('toast-msg').textContent = msg;
    icon.className = type === 'danger' ? 'fas fa-trash-alt' : 'fas fa-check-circle';
    toast.className = 'toast toast-' + type + ' show';
    setTimeout(() => toast.classList.remove('show'), 3000);
}
</script>
@endsection