@extends('layouts.app')

@section('title', 'Order List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistOrders.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <span class="cur">Order List</span>
    </div>
</div>

{{-- Status tab bar --}}
<div class="order-tab-bar">
    <div class="order-tab-inner">
        @php
            $tabs = [
                ''                => 'All',
                'processing'      => 'New Orders',
                'preparing'       => 'Preparing',
                'shipped'         => 'Shipped',
                'completed'       => 'Completed',
                'pending_payment' => 'Unpaid',
                'cancelled'       => 'Cancelled',
            ];
            $newCount = $orders->whereIn('status', ['processing'])->count();
        @endphp
        @foreach($tabs as $val => $label)
            <a href="{{ route('artist.orders', $val ? ['status' => $val] : []) }}"
               class="tab {{ request('status', '') === $val ? 'active' : '' }}">
                {{ $label }}
                @if($val === 'processing' && $newCount > 0)
                    <span class="tab-dot"></span>
                @endif
            </a>
        @endforeach
    </div>
</div>

<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="javascript:history.back()" class="back-btn">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<main class="artist-orders-main">

    {{-- Page header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-title">Order List</div>
            <div class="page-sub">Manage and track your buyer orders</div>
        </div>
        <div style="display:flex;align-items:center;gap:var(--sp-sm);">
            @if($newCount > 0)
                <span class="stat-pill new">
                    <i class="bi bi-bell-fill"></i> {{ $newCount }} new
                </span>
            @endif
            <span class="header-badge">{{ $orders->count() }} total</span>
        </div>
    </div>

    {{-- Results card --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                @php
                    $activeTab = request('status', '');
                    $tabTitles = [
                        ''                => 'All Orders',
                        'processing'      => 'New Orders',
                        'preparing'       => 'Preparing',
                        'shipped'         => 'Shipped',
                        'completed'       => 'Completed',
                        'pending_payment' => 'Unpaid',
                        'cancelled'       => 'Cancelled',
                    ];
                @endphp
                {{ $tabTitles[$activeTab] ?? 'Orders' }}
            </div>
            @if($filteredOrders->isNotEmpty())
                <span class="section-count">{{ $filteredOrders->total() }} order{{ $filteredOrders->total() !== 1 ? 's' : '' }}</span>
            @endif
        </div>

        <div class="sp-card-body">

            @if($filteredOrders->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-clipboard-check"></i></div>
                    <h3>No orders found</h3>
                    <p>{{ request('status') ? 'No ' . request('status') . ' orders at the moment.' : 'You have no orders yet.' }}</p>
                </div>

            @else
                <div class="orders-list">
                    @foreach($filteredOrders as $order)
                    @php
                        $sellerLabel = match($order->status) {
                            'pending_payment' => 'Awaiting Payment',
                            'processing'      => 'New Order',
                            'preparing'       => 'Preparing',
                            'shipped'         => 'Shipped',
                            'completed'       => 'Completed',
                            'cancelled'       => 'Cancelled',
                            default           => ucfirst($order->status),
                        };
                        $sellerClass = match($order->status) {
                            'pending_payment' => 'yellow',
                            'processing'      => 'blue',
                            'preparing'       => 'orange',
                            'shipped'         => 'purple',
                            'completed'       => 'green',
                            'cancelled'       => 'red',
                            default           => 'gray',
                        };

                        // Check if ALL items are digital — skip courier modal if so
                        $isAllDigital = $order->items && $order->items->count() > 0
                            && $order->items->every(
                                fn($i) => $i->artwork?->artwork_type === 'digital'
                            );
                    @endphp

                    <div class="order-card {{ $order->status === 'processing' ? 'order-card--new' : '' }}">

                        {{-- ── Card Header ── --}}
                        <div class="order-header">
                            <div class="order-meta">
                                @if($order->status === 'processing')
                                    <span class="new-badge"><i class="bi bi-lightning-fill"></i> New</span>
                                @endif
                                <span class="order-id">
                                    <i class="bi bi-hash"></i>{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="order-date">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $order->created_at->format('d M Y, h:i A') }}
                                </span>
                            </div>
                            <span class="status-badge status-{{ $sellerClass }}">
                                @switch($order->status)
                                    @case('pending_payment') <i class="bi bi-clock"></i>             @break
                                    @case('processing')      <i class="bi bi-bell-fill"></i>         @break
                                    @case('preparing')       <i class="bi bi-box-seam"></i>          @break
                                    @case('shipped')         <i class="bi bi-truck"></i>             @break
                                    @case('completed')       <i class="bi bi-check-circle-fill"></i> @break
                                    @case('cancelled')       <i class="bi bi-x-circle-fill"></i>     @break
                                    @default                 <i class="bi bi-circle"></i>
                                @endswitch
                                {{ $sellerLabel }}
                            </span>
                        </div>

                        {{-- ── Buyer Row ── --}}
                        <div class="buyer-section">
                            <div class="buyer-row">
                                <div class="buyer-avatar">
                                    @if($order->user->profile_image)
                                        <img src="{{ asset('storage/' . $order->user->profile_image) }}"
                                             alt="{{ $order->user->fullname }}">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ strtoupper(substr($order->user->fullname ?? '?', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="buyer-info">
                                    <span class="buyer-name">
                                        <i class="bi bi-person-fill"></i>
                                        {{ $order->user->fullname }}
                                    </span>
                                    <span class="buyer-email">
                                        <i class="bi bi-envelope"></i>
                                        {{ $order->user->email }}
                                    </span>
                                </div>
                                <div class="order-price-col">
                                    <span class="price-label">Order Total</span>
                                    <span class="order-price">RM {{ number_format($order->total ?? $order->price ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ── Products to Prepare (Shopee-style) ── --}}
                        <div class="products-section">
                            <div class="products-section-label">
                                <i class="bi bi-bag-check-fill"></i> Items to Prepare
                                <span class="items-count">{{ $order->items && $order->items->count() > 0 ? $order->items->count() : 1 }} item{{ ($order->items && $order->items->count() > 1) ? 's' : '' }}</span>
                            </div>

                            @if($order->items && $order->items->count() > 0)
                                @foreach($order->items as $item)
                                @php
                                    $artwork  = $item->artwork;
                                    $imgPath  = $artwork?->image_path ?? $item->image_path ?? null;
                                    $isCustom = is_null($item->artwork_sell_id);
                                    $isDigitalItem = $artwork?->artwork_type === 'digital';
                                @endphp
                                <div class="product-row">
                                    {{-- Thumbnail --}}
                                    <div class="product-thumb">
                                        @if($imgPath)
                                            <img src="{{ asset('storage/' . $imgPath) }}" alt="{{ $item->name }}">
                                        @elseif($isCustom)
                                            <i class="bi bi-brush"></i>
                                        @else
                                            <i class="bi bi-image"></i>
                                        @endif
                                    </div>

                                    {{-- Info --}}
                                    <div class="product-info">
                                        <div class="product-name">{{ $item->name ?? 'Artwork' }}</div>
                                        @if($isCustom)
                                        <div class="product-tag custom-tag">
                                            <i class="bi bi-brush"></i> Custom Order
                                        </div>
                                        @elseif($artwork)
                                        <div class="product-tag">
                                            @if($isDigitalItem)
                                                <span class="digital-tag"><i class="bi bi-cloud-download"></i> Digital</span>
                                            @elseif($artwork->artwork_type)
                                                <i class="bi bi-tag"></i> {{ ucfirst($artwork->artwork_type) }}
                                            @endif
                                            @if($artwork->material)
                                                &middot; {{ $artwork->material }}
                                            @endif
                                        </div>
                                        @endif
                                        @if($order->notes && $isCustom)
                                        <div class="product-notes">
                                            <i class="bi bi-chat-left-text"></i> {{ Str::limit($order->notes, 80) }}
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Qty & Price --}}
                                    <div class="product-price-col">
                                        <div class="product-qty">
                                            <i class="bi bi-layers"></i> × {{ $item->quantity ?? 1 }}
                                        </div>
                                        <div class="product-unit-price">
                                            RM {{ number_format($item->price, 2) }}
                                            @if(($item->quantity ?? 1) > 1)
                                            <span class="per-unit">/ unit</span>
                                            @endif
                                        </div>
                                        @if(($item->quantity ?? 1) > 1)
                                        <div class="product-subtotal">
                                            = RM {{ number_format($item->price * $item->quantity, 2) }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            @else
                                {{-- Fallback for orders without items --}}
                                <div class="product-row">
                                    <div class="product-thumb"><i class="bi bi-image"></i></div>
                                    <div class="product-info">
                                        <div class="product-name">{{ $order->title ?? 'Artwork Order' }}</div>
                                    </div>
                                    <div class="product-price-col">
                                        <div class="product-qty"><i class="bi bi-layers"></i> × 1</div>
                                        <div class="product-unit-price">RM {{ number_format($order->total ?? $order->price ?? 0, 2) }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- ── Order Info Summary ── --}}
                        <div class="order-info-strip">
                            <div class="info-chip">
                                <i class="bi bi-receipt"></i>
                                Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="info-chip">
                                <i class="bi bi-credit-card"></i>
                                {{ $order->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                            </div>
                            @if($isAllDigital)
                            <div class="info-chip digital-chip">
                                <i class="bi bi-cloud-download"></i> Digital Delivery
                            </div>
                            @endif
                            @if($order->tracking_number)
                            <div class="info-chip tracking-chip">
                                <i class="bi bi-truck"></i>
                                <strong>{{ strtoupper($order->courier ?? 'Courier') }}</strong>
                                — {{ $order->tracking_number }}
                            </div>
                            @endif
                        </div>

                        {{-- ── Card Footer (Actions) ── --}}
                        <div class="order-footer">

                            @if($order->status === 'pending_payment')
                                <span class="status-note">
                                    <i class="bi bi-clock"></i> Waiting for buyer to complete payment
                                </span>

                            @elseif($order->status === 'processing')
                                <form action="{{ route('artist.orders.accept', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-accept">
                                        <i class="bi bi-check-lg"></i> Accept Order
                                    </button>
                                </form>

                            @elseif($order->status === 'preparing')
                                @if($isAllDigital)
                                    {{-- Digital: direct submit, no courier/tracking needed --}}
                                    <form action="{{ route('artist.orders.ship', $order->id) }}" method="POST"
                                          onsubmit="return confirm('Mark this digital order as delivered to buyer?')">
                                        @csrf
                                        <input type="hidden" name="courier" value="digital">
                                        <input type="hidden" name="tracking_number" value="DIGITAL-DELIVERY">
                                        <button type="submit" class="btn-ship">
                                            <i class="bi bi-cloud-check-fill"></i> Mark as Delivered
                                        </button>
                                    </form>
                                @else
                                    {{-- Physical: open courier/tracking modal --}}
                                    <button class="btn-ship" onclick="openShipModal({{ $order->id }})">
                                        <i class="bi bi-truck"></i> Mark as Shipped
                                    </button>
                                @endif

                            @elseif($order->status === 'shipped')
                                @if($order->getTrackingUrl())
                                    <a href="{{ $order->getTrackingUrl() }}" target="_blank" class="btn-track">
                                        <i class="bi bi-box-arrow-up-right"></i> Track Parcel
                                    </a>
                                @endif
                                <span class="status-note">
                                    <i class="bi bi-info-circle"></i> Waiting for buyer to confirm receipt
                                </span>

                            @elseif($order->status === 'completed')
                                <span class="status-note status-note--green">
                                    <i class="bi bi-check-circle-fill"></i> Order completed by buyer
                                </span>

                            @endif

                        </div>

                    </div>
                    @endforeach
                </div>

                @if($filteredOrders->hasPages())
                <div class="pagination-wrapper">
                    {{ $filteredOrders->links() }}
                </div>
                @endif

            @endif

        </div>
    </div>

</main>

{{-- Ship Modal (physical orders only) --}}
<div class="modal-backdrop" id="shipBackdrop" onclick="closeShipModal()"></div>
<div class="ship-modal" id="shipModal">
    <div class="ship-modal-header">
        <h3><i class="bi bi-truck"></i> Shipping Details</h3>
        <button onclick="closeShipModal()" class="modal-close-btn">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form id="shipForm" method="POST">
        @csrf
        <div class="ship-modal-body">
            <div class="form-group" id="courierGroup">
                <label>Courier <span class="req">*</span></label>
                <select name="courier" required>
                    <option value="">— Select Courier —</option>
                    <option value="poslaju">Pos Laju</option>
                    <option value="jnt">J&T Express</option>
                    <option value="dhl">DHL</option>
                    <option value="ninjavan">Ninja Van</option>
                    <option value="citylink">City-Link</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group" id="trackingGroup">
                <label>Tracking Number <span class="req">*</span></label>
                <input type="text" name="tracking_number"
                       placeholder="e.g. EF123456789MY" required maxlength="100">
            </div>
        </div>
        <div class="ship-modal-footer">
            <button type="button" class="btn-cancel" onclick="closeShipModal()">Cancel</button>
            <button type="submit" class="btn-ship">
                <i class="bi bi-truck"></i> Confirm & Ship
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function openShipModal(orderId) {
    document.getElementById('shipForm').action = `/artist/orders/${orderId}/ship`;

    // Always show courier + tracking fields (physical orders only reach here)
    document.getElementById('courierGroup').style.display  = '';
    document.getElementById('trackingGroup').style.display = '';
    document.querySelector('[name="courier"]').required    = true;
    document.querySelector('[name="tracking_number"]').required = true;
    document.querySelector('[name="courier"]').value       = '';
    document.querySelector('[name="tracking_number"]').value = '';

    document.getElementById('shipBackdrop').classList.add('show');
    document.getElementById('shipModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeShipModal() {
    document.getElementById('shipBackdrop').classList.remove('show');
    document.getElementById('shipModal').classList.remove('open');
    document.body.style.overflow = '';
}
</script>
@endsection