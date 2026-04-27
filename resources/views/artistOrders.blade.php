@extends('layouts.app')

@section('title', 'Order List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistOrders.css') }}">
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

{{-- Status tab bar (Shopee-style sticky) --}}
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
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back
    </a>
</div>

<main class="artist-orders-main">

    {{-- Page header card --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-title">Order List</div>
            <div class="page-sub">Manage and track your buyer orders</div>
        </div>
        <div style="display:flex;align-items:center;gap:var(--sp-sm);">
            @if($newCount > 0)
                <span class="stat-pill new">
                    <i class="fas fa-bell"></i> {{ $newCount }} new
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
                    <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
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
                    @endphp

                    <div class="order-card {{ $order->status === 'processing' ? 'order-card--new' : '' }}">

                        {{-- Card Header --}}
                        <div class="order-header">
                            <div class="order-meta">
                                @if($order->status === 'processing')
                                    <span class="new-badge"><i class="fas fa-bolt"></i> New</span>
                                @endif
                                <span class="order-id">Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <span class="order-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $order->created_at->format('d M Y, h:i A') }}
                                </span>
                            </div>
                            <span class="status-badge status-{{ $sellerClass }}">
                                @switch($order->status)
                                    @case('pending_payment') <i class="fas fa-clock"></i>        @break
                                    @case('processing')      <i class="fas fa-bell"></i>         @break
                                    @case('preparing')       <i class="fas fa-box"></i>          @break
                                    @case('shipped')         <i class="fas fa-truck"></i>        @break
                                    @case('completed')       <i class="fas fa-check-circle"></i> @break
                                    @case('cancelled')       <i class="fas fa-times-circle"></i> @break
                                    @default                 <i class="fas fa-circle"></i>
                                @endswitch
                                {{ $sellerLabel }}
                            </span>
                        </div>

                        {{-- Card Body --}}
                        <div class="order-body">

                            {{-- Buyer info --}}
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
                                    <span class="buyer-name">{{ $order->user->fullname }}</span>
                                    <span class="buyer-email">{{ $order->user->email }}</span>
                                </div>
                                <div class="order-price-col">
                                    <span class="price-label">Total</span>
                                    <span class="order-price">RM {{ number_format($order->total ?? $order->price ?? 0, 2) }}</span>
                                </div>
                            </div>

                            {{-- Order items --}}
                            @if($order->items && $order->items->count() > 0)
                            <div class="order-items">
                                @foreach($order->items as $item)
                                <div class="order-item">
                                    <i class="fas fa-palette item-icon"></i>
                                    <span class="item-name">{{ $item->name ?? 'Item' }}</span>
                                    <span class="item-qty">×{{ $item->quantity ?? 1 }}</span>
                                    <span class="item-price">RM {{ number_format($item->price, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="order-items">
                                <div class="order-item">
                                    <i class="fas fa-palette item-icon"></i>
                                    <span class="item-name">{{ $order->title ?? 'Artwork Order' }}</span>
                                    <span class="item-qty">×1</span>
                                    <span class="item-price">RM {{ number_format($order->total ?? $order->price ?? 0, 2) }}</span>
                                </div>
                            </div>
                            @endif

                            {{-- Tracking pill --}}
                            @if($order->tracking_number)
                            <div class="tracking-pill">
                                <i class="fas fa-map-marker-alt"></i>
                                <strong>{{ strtoupper($order->courier ?? 'Courier') }}</strong>
                                &mdash; {{ $order->tracking_number }}
                            </div>
                            @endif

                        </div>

                        {{-- Card Footer --}}
                        <div class="order-footer">

                            @if($order->status === 'pending_payment')
                                <span class="status-note">
                                    <i class="fas fa-clock"></i> Waiting for buyer to complete payment
                                </span>

                            @elseif($order->status === 'processing')
                                <form action="{{ route('artist.orders.accept', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-accept">
                                        <i class="fas fa-check"></i> Accept Order
                                    </button>
                                </form>

                            @elseif($order->status === 'preparing')
                                <button class="btn-ship" onclick="openShipModal({{ $order->id }})">
                                    <i class="fas fa-truck"></i> Mark as Shipped
                                </button>

                            @elseif($order->status === 'shipped')
                                @if($order->getTrackingUrl())
                                    <a href="{{ $order->getTrackingUrl() }}" target="_blank" class="btn-track">
                                        <i class="fas fa-external-link-alt"></i> Track Parcel
                                    </a>
                                @endif
                                <span class="status-note">
                                    <i class="fas fa-info-circle"></i> Waiting for buyer to confirm receipt
                                </span>

                            @elseif($order->status === 'completed')
                                <span class="status-note status-note--green">
                                    <i class="fas fa-check-circle"></i> Order completed by buyer
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

{{-- Ship Modal --}}
<div class="modal-backdrop" id="shipBackdrop" onclick="closeShipModal()"></div>
<div class="ship-modal" id="shipModal">
    <div class="ship-modal-header">
        <h3><i class="fas fa-truck"></i> Shipping Details</h3>
        <button onclick="closeShipModal()" class="modal-close-btn">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <form id="shipForm" method="POST">
        @csrf
        <div class="ship-modal-body">
            <div class="form-group">
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
            <div class="form-group">
                <label>Tracking Number <span class="req">*</span></label>
                <input type="text" name="tracking_number"
                       placeholder="e.g. EF123456789MY" required maxlength="100">
            </div>
        </div>
        <div class="ship-modal-footer">
            <button type="button" class="btn-cancel" onclick="closeShipModal()">Cancel</button>
            <button type="submit" class="btn-ship">
                <i class="fas fa-truck"></i> Confirm & Ship
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function openShipModal(orderId) {
    document.getElementById('shipForm').action = `/artist/orders/${orderId}/ship`;
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