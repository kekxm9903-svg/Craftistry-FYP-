@extends('layouts.app')

@section('title', 'Order List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistOrders.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
.refund-banner {
    margin: 0 var(--sp-md) var(--sp-sm);
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1.5px solid #fed7aa;
}
.refund-banner-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px; padding: 10px var(--sp-md);
    background: linear-gradient(135deg, #fff8f0, #fef3c7);
}
.refund-banner.refunded { border-color: #bbf7d0; }
.refund-banner.refunded .refund-banner-header { background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
.refund-banner.rejected  { border-color: #fecaca; }
.refund-banner.rejected  .refund-banner-header { background: linear-gradient(135deg, #fef2f2, #fee2e2); }
.refund-banner-left { display: flex; align-items: center; gap: 10px; }
.refund-banner-icon { width: 36px; height: 36px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #d97706; flex-shrink: 0; border: 1.5px solid #fcd34d; box-shadow: 0 2px 6px rgba(252,211,77,.25); }
.refund-banner.refunded .refund-banner-icon { color: #166534; border-color: #bbf7d0; }
.refund-banner.rejected  .refund-banner-icon { color: #991b1b; border-color: #fecaca; }
.refund-banner-title { font-size: 13px; font-weight: 800; color: #92400e; }
.refund-banner.refunded .refund-banner-title { color: #166534; }
.refund-banner.rejected  .refund-banner-title { color: #991b1b; }
.refund-banner-sub   { font-size: 11px; color: #b45309; margin-top: 1px; }
.refund-banner.refunded .refund-banner-sub { color: #15803d; }
.refund-banner.rejected  .refund-banner-sub { color: #dc2626; }
.refund-banner-body { padding: var(--sp-sm) var(--sp-md); background: #fff; display: flex; align-items: flex-start; gap: var(--sp-md); flex-wrap: wrap; }
.refund-reason-box { flex: 1; min-width: 180px; background: #fff8f0; border: 1px solid #fed7aa; border-radius: var(--radius-sm); padding: 8px 12px; }
.refund-reason-label { font-size: 11px; color: #b45309; font-weight: 700; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
.refund-reason-text  { font-size: 13px; color: #374151; font-style: italic; line-height: 1.5; }
.refund-banner-actions { display: flex; flex-direction: column; gap: 7px; flex-shrink: 0; }
.refund-action-row { display: flex; gap: 7px; }
.rp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 8px 16px; border-radius: 7px; font-size: 12px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; transition: opacity .15s; white-space: nowrap; }
.rp-btn:hover { opacity: .85; }
.rp-btn.approve { background: #22c55e; color: #fff; box-shadow: 0 2px 8px rgba(34,197,94,.3); }
.rp-btn.reject  { background: #ef4444; color: #fff; box-shadow: 0 2px 8px rgba(239,68,68,.25); }
.rp-btn.cancel  { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
.rp-btn.confirm { background: #ef4444; color: #fff; }
.rp-reject-form { display: none; width: 100%; }
.rp-reject-textarea { width: 100%; border: 1.5px solid #fca5a5; border-radius: 7px; padding: 8px 10px; font-size: 12px; font-family: inherit; resize: vertical; outline: none; box-sizing: border-box; }
.rp-reject-textarea:focus { border-color: #ef4444; }
.rp-reject-actions { display: flex; gap: 7px; margin-top: 7px; justify-content: flex-end; }

.refund-header-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.refund-header-badge.requested { background: #fff3cd; color: #92400e; }
.refund-header-badge.refunded  { background: #dcfce7; color: #166534; }
.refund-header-badge.rejected  { background: #fee2e2; color: #991b1b; }

.btn-view-details { display: inline-flex; align-items: center; gap: var(--sp-xs); padding: 7px var(--sp-md); border-radius: var(--radius-sm); background: var(--lavender); color: var(--primary-2); font-weight: 700; font-size: var(--fs-base); text-decoration: none; border: 1.5px solid #ddd8f8; transition: all .15s; white-space: nowrap; }
.btn-view-details:hover { background: linear-gradient(135deg, var(--primary), var(--primary-2)); color: #fff; border-color: transparent; }

/* ── Digital Deliver Modal — matches logout modal style ── */
#deliverConfirmModal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 99999;
    align-items: center;
    justify-content: center;
}
#deliverConfirmModal.open { display: flex; }
#deliverConfirmBackdrop {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.48);
    backdrop-filter: blur(3px);
}
#deliverConfirmBox {
    position: relative;
    background: #fff;
    border-radius: 16px;
    padding: 36px 32px 28px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 24px 64px rgba(102,126,234,.22), 0 4px 16px rgba(0,0,0,.08);
    text-align: center;
    z-index: 1;
    animation: deliverModalIn .22s cubic-bezier(.34,1.56,.64,1);
}
@keyframes deliverModalIn {
    from { opacity: 0; transform: scale(.88) translateY(16px); }
    to   { opacity: 1; transform: scale(1)  translateY(0); }
}
.deliver-modal-icon {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
    border: 2px solid #c4b5fd;
    box-shadow: 0 4px 12px rgba(102,126,234,.15);
}
.deliver-modal-icon i { color: #667eea; font-size: 1.45rem; }
.deliver-modal-title { font-size: 1.15rem; font-weight: 800; color: #1a202c; margin-bottom: 8px; }
.deliver-modal-msg   { font-size: 0.84rem; color: #718096; line-height: 1.65; margin-bottom: 28px; }
.deliver-modal-btns  { display: flex; gap: 10px; }
.deliver-btn-cancel {
    flex: 1; padding: 12px; border-radius: 8px;
    border: 1.5px solid #e2e8f0; background: #fff;
    color: #4a5568; font-size: 0.88rem; font-weight: 600;
    cursor: pointer; font-family: 'Inter', sans-serif; transition: all .15s;
}
.deliver-btn-cancel:hover { background: #f7fafc; border-color: #cbd5e0; }
.deliver-btn-confirm {
    flex: 1; padding: 12px; border-radius: 8px; border: none;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff; font-size: 0.88rem; font-weight: 700;
    cursor: pointer; font-family: 'Inter', sans-serif; transition: all .15s;
    box-shadow: 0 4px 14px rgba(102,126,234,.35);
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.deliver-btn-confirm:hover { opacity: .88; transform: translateY(-1px); }
</style>
@endsection

@section('content')

<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <span class="cur">Order List</span>
    </div>
</div>

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
            $newCount    = $orders->whereIn('status', ['processing'])->count();
            $refundCount = $orders->whereIn('refund_status', ['requested'])->count();
        @endphp
        @foreach($tabs as $val => $label)
            <a href="{{ route('artist.orders', $val ? ['status' => $val] : []) }}"
               class="tab {{ request('status', '') === $val && request('refund') !== '1' ? 'active' : '' }}">
                {{ $label }}
                @if($val === 'processing' && $newCount > 0)
                    <span class="tab-dot"></span>
                @endif
            </a>
        @endforeach
        <a href="{{ route('artist.orders', ['refund' => '1']) }}"
           class="tab {{ request('refund') === '1' ? 'active' : '' }}">
            Refunds
            @if($refundCount > 0)
                <span class="tab-dot" style="background:#d97706;"></span>
            @endif
        </a>
    </div>
</div>

<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="javascript:history.back()" class="back-btn">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<main class="artist-orders-main">

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
            @if($refundCount > 0)
                <a href="{{ route('artist.orders', ['refund' => '1']) }}"
                   style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;
                          font-size:12px;font-weight:700;background:#fff3cd;color:#92400e;
                          text-decoration:none;border:1px solid #fcd34d;">
                    {{ $refundCount }} refund request{{ $refundCount > 1 ? 's' : '' }}
                </a>
            @endif
            <span class="header-badge">{{ $orders->count() }} total</span>
        </div>
    </div>

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
                {{ request('refund') === '1' ? 'Refund Requests' : ($tabTitles[$activeTab] ?? 'Orders') }}
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
                    <p>{{ request('refund') === '1' ? 'No refund requests at the moment.' : (request('status') ? 'No ' . request('status') . ' orders at the moment.' : 'You have no orders yet.') }}</p>
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
                        $isAllDigital = $order->items && $order->items->count() > 0
                            && $order->items->every(fn($i) => $i->artwork?->artwork_type === 'digital');
                        $refundStatus = $order->refund_status ?? 'none';
                    @endphp

                    <div class="order-card {{ $order->status === 'processing' ? 'order-card--new' : '' }}"
                         style="{{ $refundStatus === 'requested' ? 'border-color:#fed7aa;' : '' }}">

                        {{-- Card Header --}}
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
                            <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;">
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
                                @if($refundStatus === 'requested')
                                    <span class="refund-header-badge requested">
                                        <i class="bi bi-arrow-return-left"></i> Refund Requested
                                    </span>
                                @elseif($refundStatus === 'refunded')
                                    <span class="refund-header-badge refunded">
                                        <i class="bi bi-check-circle-fill"></i> Refunded
                                    </span>
                                @elseif($refundStatus === 'rejected')
                                    <span class="refund-header-badge rejected">
                                        <i class="bi bi-x-circle-fill"></i> Refund Rejected
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Buyer Row --}}
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

                        {{-- Products --}}
                        <div class="products-section">
                            <div class="products-section-label">
                                <i class="bi bi-bag-check-fill"></i> Items to Prepare
                                <span class="items-count">{{ $order->items && $order->items->count() > 0 ? $order->items->count() : 1 }} item{{ ($order->items && $order->items->count() > 1) ? 's' : '' }}</span>
                            </div>

                            @if($order->items && $order->items->count() > 0)
                                @foreach($order->items as $item)
                                @php
                                    $artwork       = $item->artwork;
                                    $isCustom      = is_null($item->artwork_sell_id);
                                    $imgPath       = $artwork?->image_path ?? $item->image_path ?? null;
                                    $isDigitalItem = $artwork?->artwork_type === 'digital';
                                    if ($isCustom && !$imgPath) {
                                        $imgPath = $order->customOrderRequest?->reference_image ?? null;
                                    }
                                @endphp
                                <div class="product-row">
                                    <div class="product-thumb">
                                        @if($imgPath)
                                            <img src="{{ asset('storage/' . $imgPath) }}" alt="{{ $item->name }}">
                                        @elseif($isCustom)
                                            <i class="bi bi-brush"></i>
                                        @else
                                            <i class="bi bi-image"></i>
                                        @endif
                                    </div>
                                    <div class="product-info">
                                        <div class="product-name">{{ $item->name ?? 'Artwork' }}</div>
                                        @if($isCustom)
                                        <div class="product-tag custom-tag"><i class="bi bi-brush"></i> Custom Order</div>
                                        @elseif($artwork)
                                        <div class="product-tag">
                                            @if($isDigitalItem)
                                                <span class="digital-tag"><i class="bi bi-cloud-download"></i> Digital</span>
                                            @elseif($artwork->artwork_type)
                                                <i class="bi bi-tag"></i> {{ ucfirst($artwork->artwork_type) }}
                                            @endif
                                            @if($artwork->material) &middot; {{ $artwork->material }} @endif
                                        </div>
                                        @endif
                                        @if($order->notes && $isCustom)
                                        <div class="product-notes">
                                            <i class="bi bi-chat-left-text"></i> {{ Str::limit($order->notes, 80) }}
                                        </div>
                                        @endif
                                    </div>
                                    <div class="product-price-col">
                                        <div class="product-qty"><i class="bi bi-layers"></i> × {{ $item->quantity ?? 1 }}</div>
                                        <div class="product-unit-price">
                                            RM {{ number_format($item->price, 2) }}
                                            @if(($item->quantity ?? 1) > 1)<span class="per-unit">/ unit</span>@endif
                                        </div>
                                        @if(($item->quantity ?? 1) > 1)
                                        <div class="product-subtotal">= RM {{ number_format($item->price * $item->quantity, 2) }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="product-row">
                                    <div class="product-thumb"><i class="bi bi-image"></i></div>
                                    <div class="product-info"><div class="product-name">{{ $order->title ?? 'Artwork Order' }}</div></div>
                                    <div class="product-price-col">
                                        <div class="product-qty"><i class="bi bi-layers"></i> × 1</div>
                                        <div class="product-unit-price">RM {{ number_format($order->total ?? $order->price ?? 0, 2) }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Order Info Strip --}}
                        <div class="order-info-strip">
                            <div class="info-chip"><i class="bi bi-receipt"></i> Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="info-chip"><i class="bi bi-credit-card"></i> {{ $order->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</div>
                            @if($isAllDigital)
                            <div class="info-chip digital-chip"><i class="bi bi-cloud-download"></i> Digital Delivery</div>
                            @endif
                            @if($order->tracking_number)
                            <div class="info-chip tracking-chip">
                                <i class="bi bi-truck"></i>
                                <strong>{{ strtoupper($order->courier ?? 'Courier') }}</strong>
                                — {{ $order->tracking_number }}
                            </div>
                            @endif
                        </div>

                        {{-- Refund Banner --}}
                        @if($refundStatus === 'requested')
                        <div class="refund-banner">
                            <div class="refund-banner-header">
                                <div class="refund-banner-left">
                                    <div class="refund-banner-icon"><i class="bi bi-arrow-return-left"></i></div>
                                    <div>
                                        <div class="refund-banner-title">Buyer Requested a Refund</div>
                                        <div class="refund-banner-sub">
                                            <i class="bi bi-clock"></i>
                                            {{ \Carbon\Carbon::parse($order->refund_requested_at)->format('d M Y, h:i A') }}
                                            &nbsp;·&nbsp; RM {{ number_format($order->total ?? 0, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="refund-banner-body">
                                <div class="refund-reason-box">
                                    <div class="refund-reason-label"><i class="bi bi-chat-quote"></i> Reason</div>
                                    <div class="refund-reason-text">"{{ $order->refund_reason }}"</div>
                                </div>
                                <div class="refund-banner-actions">
                                    <div class="refund-action-row">
                                        <form method="POST" action="{{ route('refund.approve.order', $order->id) }}"
                                              onsubmit="return confirm('Approve refund? Stripe will process immediately.')">
                                            @csrf
                                            <button type="submit" class="rp-btn approve">
                                                <i class="bi bi-check-circle-fill"></i> Approve Refund
                                            </button>
                                        </form>
                                        <button type="button" class="rp-btn reject"
                                                onclick="toggleRejectForm({{ $order->id }})">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </button>
                                    </div>
                                    <div class="rp-reject-form" id="rejectForm-{{ $order->id }}">
                                        <form method="POST" action="{{ route('refund.reject.order', $order->id) }}">
                                            @csrf
                                            <textarea name="reject_reason" class="rp-reject-textarea" rows="2"
                                                placeholder="Reason for rejection (required)..."
                                                required minlength="5" maxlength="500"></textarea>
                                            <div class="rp-reject-actions">
                                                <button type="button" class="rp-btn cancel"
                                                        onclick="toggleRejectForm({{ $order->id }})">Cancel</button>
                                                <button type="submit" class="rp-btn confirm">Confirm Rejection</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @elseif($refundStatus === 'refunded')
                        <div class="refund-banner refunded">
                            <div class="refund-banner-header">
                                <div class="refund-banner-left">
                                    <div class="refund-banner-icon"><i class="bi bi-check-circle-fill"></i></div>
                                    <div>
                                        <div class="refund-banner-title">Refund Processed</div>
                                        <div class="refund-banner-sub">
                                            RM {{ number_format($order->refund_amount, 2) }} returned to buyer
                                            · {{ \Carbon\Carbon::parse($order->refunded_at)->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @elseif($refundStatus === 'rejected')
                        <div class="refund-banner rejected">
                            <div class="refund-banner-header">
                                <div class="refund-banner-left">
                                    <div class="refund-banner-icon"><i class="bi bi-x-circle-fill"></i></div>
                                    <div>
                                        <div class="refund-banner-title">Refund Request Rejected</div>
                                        @if($order->refund_reject_reason)
                                        <div class="refund-banner-sub">{{ $order->refund_reject_reason }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Card Footer (Actions) --}}
                        <div class="order-footer">

                            <a href="{{ route('artist.orders.show', $order->id) }}" class="btn-view-details">
                                <i class="bi bi-eye"></i> View Details
                            </a>

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
                                    <button type="button" class="btn-ship"
                                            onclick="openDeliverConfirm({{ $order->id }})">
                                        <i class="bi bi-cloud-check-fill"></i> Mark as Delivered
                                    </button>
                                    <form id="deliverForm-{{ $order->id }}"
                                          action="{{ route('artist.orders.ship', $order->id) }}"
                                          method="POST" style="display:none;">
                                        @csrf
                                        <input type="hidden" name="courier" value="digital">
                                        <input type="hidden" name="tracking_number" value="DIGITAL-DELIVERY">
                                    </form>
                                @else
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

{{-- Ship Modal --}}
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

{{-- Digital Deliver Confirm Modal — matches logout modal style --}}
<div id="deliverConfirmModal" role="dialog" aria-modal="true">
    <div id="deliverConfirmBackdrop" onclick="closeDeliverConfirm()"></div>
    <div id="deliverConfirmBox">
        <div class="deliver-modal-icon">
            <i class="bi bi-cloud-check-fill"></i>
        </div>
        <div class="deliver-modal-title">Mark as Delivered?</div>
        <div class="deliver-modal-msg">
            This will mark the order as delivered and notify the buyer.<br>
            Make sure you have already sent the digital files before confirming.
        </div>
        <div class="deliver-modal-btns">
            <button class="deliver-btn-cancel" onclick="closeDeliverConfirm()">
                Cancel
            </button>
            <button class="deliver-btn-confirm" onclick="confirmDeliver()">
                <i class="bi bi-cloud-check-fill"></i> Yes, Mark as Delivered
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ── Ship Modal ──
function openShipModal(orderId) {
    document.getElementById('shipForm').action = `/artist/orders/${orderId}/ship`;
    document.getElementById('courierGroup').style.display   = '';
    document.getElementById('trackingGroup').style.display  = '';
    document.querySelector('[name="courier"]').required     = true;
    document.querySelector('[name="tracking_number"]').required = true;
    document.querySelector('[name="courier"]').value        = '';
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

// ── Digital Deliver Confirm Modal ──
var _deliverOrderId = null;

function openDeliverConfirm(orderId) {
    _deliverOrderId = orderId;
    document.getElementById('deliverConfirmModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDeliverConfirm() {
    document.getElementById('deliverConfirmModal').classList.remove('open');
    document.body.style.overflow = '';
    _deliverOrderId = null;
}
function confirmDeliver() {
    if (_deliverOrderId) {
        document.getElementById('deliverForm-' + _deliverOrderId).submit();
    }
}

// ── Reject Form Toggle ──
function toggleRejectForm(orderId) {
    var f = document.getElementById('rejectForm-' + orderId);
    f.style.display = f.style.display === 'none' || f.style.display === '' ? 'block' : 'none';
}

// ── Close on Escape ──
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeShipModal(); closeDeliverConfirm(); }
});
</script>
@endsection