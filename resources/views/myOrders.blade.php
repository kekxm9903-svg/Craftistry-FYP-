@extends('layouts.app')

@section('title', 'My Orders')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/myOrders.css') }}">

<style>
.success-popup,
.delete-popup {
    display: none;
    position: fixed;
    top: 80px;
    right: var(--sp-lg, 20px);
    z-index: 999;
}

.success-popup.show,
.delete-popup.show { display: block; animation: slideInRight .3s ease-out; }

.success-popup.hide,
.delete-popup.hide { animation: slideOutRight .3s ease-out forwards; }

.success-content {
    background: #d1fae5;
    border: 1px solid #6ee7b7;
    border-radius: 10px;
    padding: 16px 20px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, .15);
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 280px;
}

.delete-content {
    background: #fee2e2;
    border: 1px solid #fca5a5;
    border-radius: 10px;
    padding: 16px 20px;
    box-shadow: 0 4px 12px rgba(239, 68, 68, .15);
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 280px;
}

.success-icon { font-size: 15px; color: #065f46; flex-shrink: 0; }
.delete-icon  { font-size: 15px; color: #991b1b; flex-shrink: 0; }
.success-content p { font-size: 13px; font-weight: 600; color: #065f46; margin: 0; }
.delete-content  p { font-size: 13px; font-weight: 600; color: #991b1b; margin: 0; }

@keyframes slideInRight {
    from { opacity: 0; transform: translateX(360px); }
    to   { opacity: 1; transform: translateX(0); }
}

@keyframes slideOutRight {
    from { opacity: 1; transform: translateX(0); }
    to   { opacity: 0; transform: translateX(360px); }
}

@media (max-width: 768px) {
    .success-popup, .delete-popup { top: 10px; right: 10px; left: 10px; }
    .success-content, .delete-content { min-width: auto; }
}

/* ── ADDITION 1: Refund button style ── */
.btn-craft-refund {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    background: #fff8f0;
    color: #92400e;
    border: 1.5px solid #fcd34d;
    cursor: pointer;
    font-family: inherit;
    transition: background .15s;
}
.btn-craft-refund:hover { background: #fef3c7; }

/* ── ADDITION 2: Refund status badge ── */
.refund-inline-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.refund-inline-badge.pending  { background: #fff3cd; color: #92400e; }
.refund-inline-badge.refunded { background: #dcfce7; color: #166534; }
.refund-inline-badge.rejected { background: #fee2e2; color: #991b1b; }

/* ── ADDITION 3: Chips inside modal ── */
.refund-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
.refund-chip {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    background: #ede9fe;
    color: #5b21b6;
    border: 1px solid #c4b5fd;
    cursor: pointer;
    font-family: inherit;
    transition: background .15s;
}
.refund-chip:hover { background: #667eea; color: #fff; border-color: #667eea; }
</style>

@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">My Orders</span>
    </div>
</div>

<main class="orders-main">
<div class="orders-inner">

    <a href="{{ route('dashboard') }}" class="back-btn">← Back</a>

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <div class="page-title">My Orders</div>
            <div class="page-subtitle">
                {{ $orders->total() }} order{{ $orders->total() !== 1 ? 's' : '' }} in total
            </div>
        </div>
        <a href="{{ route('artist.browse') }}" class="browse-link">
            <i class="fas fa-search"></i> Browse Artworks
        </a>
    </div>

    {{-- Quick Tabs --}}
    @php
        $currentStatus  = request('status', '');
        $currentCat     = request('cat', '');

        $payCount       = $totalCounts['pending_payment']  ?? 0;
        $shipCount      = ($totalCounts['processing'] ?? 0) + ($totalCounts['preparing'] ?? 0);
        $receiveCount   = $totalCounts['shipped']          ?? 0;
        $completedCount = ($totalCounts['completed'] ?? 0)  + ($totalCounts['cancelled'] ?? 0);
        $allCount       = $totalCounts['all']              ?? $orders->total();

        $quickTabs = [
            ['cat' => '',           'status' => '',                'label' => 'All Orders',  'icon' => 'fa-shopping-bag', 'count' => $allCount],
            ['cat' => 'to-pay',     'status' => 'pending_payment', 'label' => 'To Pay',      'icon' => 'fa-credit-card',  'count' => $payCount],
            ['cat' => 'to-ship',    'status' => 'preparing',       'label' => 'To Ship',     'icon' => 'fa-box',          'count' => $shipCount],
            ['cat' => 'to-receive', 'status' => 'shipped',         'label' => 'To Receive',  'icon' => 'fa-truck',        'count' => $receiveCount],
            ['cat' => 'completed',  'status' => 'completed',       'label' => 'Completed',   'icon' => 'fa-check-circle', 'count' => $completedCount],
        ];
    @endphp

    <div class="quick-tabs-card">
        <div class="quick-tabs">
            @foreach($quickTabs as $tab)
                @php
                    $params   = array_filter(['cat' => $tab['cat'] ?: null, 'status' => $tab['status'] ?: null]);
                    $href     = route('orders.index', $params);
                    $isActive = $currentCat === $tab['cat'];
                @endphp
                <a href="{{ $href }}" class="quick-tab {{ $isActive ? 'active' : '' }}">
                    <i class="fas {{ $tab['icon'] }} qt-icon"></i>
                    <span class="qt-label">{{ $tab['label'] }}</span>
                    @if($tab['count'] > 0)
                        <span class="qt-count">{{ $tab['count'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- Status Pills --}}
    @php
        $statusPills = [
            ''                => 'All',
            'pending_payment' => 'Pending Payment',
            'processing'      => 'Order Placed',
            'preparing'       => 'Preparing',
            'shipped'         => 'Shipped',
            'completed'       => 'Completed',
            'cancelled'       => 'Cancelled',
        ];
    @endphp

    <div class="status-pills-card">
        <div class="status-row">
            @foreach($statusPills as $val => $label)
                @php
                    $pillActive = $currentStatus === $val;
                    $params     = array_filter(['status' => $val ?: null, 'cat' => $currentCat ?: null]);
                    $pillHref   = route('orders.index', $params);
                @endphp
                <a href="{{ $pillHref }}" class="status-pill {{ $pillActive ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Empty State --}}
    @if($orders->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-shopping-bag"></i></div>
            <h3>No Orders Yet</h3>
            <p>
                @if($currentStatus || $currentCat)
                    You have no
                    <strong>{{ str_replace(['_', '-'], ' ', $currentStatus ?: $currentCat) }}</strong>
                    orders at the moment.
                @else
                    You haven't placed any orders yet.<br>
                    Browse artworks and find something you love!
                @endif
            </p>
            <a href="{{ route('artist.browse') }}" class="btn-craft btn-craft-primary">
                <i class="fas fa-palette"></i> Browse Artworks
            </a>
        </div>

    @else

        {{-- Orders List --}}
        <div class="orders-list">
            @foreach($orders as $order)
            @php
                $firstArtwork = $order->items->first()?->artwork;
                $artistUser   = $order->artist?->user ?? $firstArtwork?->artist?->user;
                $artistName   = $artistUser?->fullname
                             ?? $artistUser?->name
                             ?? $order->artist?->name
                             ?? $firstArtwork?->artist?->name
                             ?? 'Unknown Artist';
                $shopInitial  = strtoupper(substr($artistName, 0, 1));

                $statusLabels = [
                    'pending_payment' => 'Pending Payment',
                    'processing'      => 'Order Placed',
                    'preparing'       => 'Preparing',
                    'shipped'         => 'Shipped',
                    'completed'       => 'Completed',
                    'cancelled'       => 'Cancelled',
                ];
                $statusLabel = $statusLabels[$order->status]
                             ?? ucfirst(str_replace('_', ' ', $order->status));

                $firstItem  = $order->items?->first();
                $extraCount = max(0, ($order->items?->count() ?? 0) - 1);

                $trackingSteps = [
                    ['label' => 'Paid',    'doneWhen' => ['processing','preparing','shipped','completed']],
                    ['label' => 'Placed',  'doneWhen' => ['processing','preparing','shipped','completed']],
                    ['label' => 'Packing', 'doneWhen' => ['preparing','shipped','completed']],
                    ['label' => 'Shipped', 'doneWhen' => ['shipped','completed']],
                    ['label' => 'Done',    'doneWhen' => ['completed']],
                ];

                $orderReview   = $order->has_review
                    ? \App\Models\Review::where('order_id', $order->id)->first()
                    : null;
                $canEditReview = $orderReview
                    && $orderReview->created_at->diffInDays(now()) <= 30;
                $canDownloadReceipt = $order->status === 'completed';

                {{-- ADDITION: refund eligibility --}}
                $refundStatus = $order->refund_status ?? 'none';
                $canRefund    = $refundStatus === 'none'
                             && $order->payment_status === 'paid'
                             && in_array($order->status, ['completed', 'shipped', 'preparing', 'processing'])
                             && ($order->status !== 'completed' || now()->diffInDays($order->updated_at) <= 7);
            @endphp

            <div class="order-card">

                {{-- Header --}}
                <div class="oc-header">
                    <div class="oc-shop">
                        <div class="oc-shop-icon">{{ $shopInitial }}</div>
                        {{ $artistName }}
                    </div>
                    <div class="oc-header-right">
                        <span class="order-id-text">
                            Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                            &middot; {{ $order->created_at->format('d M Y') }}
                        </span>
                        <span class="oc-status-badge status-{{ $order->status }}">
                            <span class="dot"></span>
                            {{ $statusLabel }}
                        </span>
                        {{-- ADDITION: refund status badge --}}
                        @if($refundStatus === 'requested')
                            <span class="refund-inline-badge pending">
                                <i class="fas fa-clock"></i> Refund Pending
                            </span>
                        @elseif($refundStatus === 'refunded')
                            <span class="refund-inline-badge refunded">
                                <i class="fas fa-check-circle"></i> Refunded
                            </span>
                        @elseif($refundStatus === 'rejected')
                            <span class="refund-inline-badge rejected">
                                <i class="fas fa-times-circle"></i> Refund Rejected
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Body --}}
                <div class="oc-body">
                    <div class="oc-item-row">
                        <div class="oc-thumb">
                            @php
                                $firstItem = $order->items->first();
                                $thumb     = $firstItem?->artwork;
                                $isCustom  = $firstItem?->artwork_sell_id === null;
                                $imgSrc    = $thumb?->image_path
                                            ? asset('storage/' . $thumb->image_path)
                                            : ($isCustom && $firstItem?->image_path
                                                ? asset('storage/' . $firstItem->image_path)
                                                : null);
                            @endphp
                            @if($imgSrc)
                                <img src="{{ $imgSrc }}" alt="{{ $firstItem->name ?? 'Artwork' }}">
                            @elseif($isCustom)
                                <i class="fas fa-paint-brush" style="font-size:22px;color:#b0a8e0;"></i>
                            @else
                                <i class="fas fa-palette" style="font-size:22px;color:#b0a8e0;"></i>
                            @endif
                        </div>

                        <div class="oc-item-info">
                            <p class="oc-item-title">
                                {{ $order->items->first()?->name ?? $order->notes ?? 'Artwork Order' }}
                            </p>
                            <p class="oc-item-artist">
                                <i class="fas fa-palette" style="font-size:10px;"></i>
                                by {{ $artistName }}
                            </p>
                            @if($firstItem)
                                <p class="oc-item-meta">
                                    Qty: {{ $firstItem->quantity ?? 1 }}
                                    @if($firstItem->variant ?? null)
                                        &middot; {{ $firstItem->variant }}
                                    @endif
                                </p>
                            @endif
                        </div>

                        <div class="oc-item-price">
                            RM {{ number_format($order->items->first()?->price ?? $order->price ?? 0, 2) }}
                        </div>
                    </div>

                    @if($extraCount > 0)
                        <div class="oc-more-items">
                            + {{ $extraCount }} more item{{ $extraCount > 1 ? 's' : '' }}
                            — <a href="{{ route('orders.show', $order->id) }}">View all</a>
                        </div>
                    @endif
                </div>

                {{-- Tracking --}}
                @if(in_array($order->status, ['shipped', 'completed']) && $order->tracking_number)
                <div class="tracking-section">
                    <div class="tracking-courier">
                        <i class="fas fa-truck"></i>
                        <strong>{{ strtoupper($order->courier ?? 'Courier') }}</strong>
                        &mdash; <code>{{ $order->tracking_number }}</code>
                    </div>
                    <div class="track-steps">
                        @foreach($trackingSteps as $i => $step)
                            @php
                                $isDone    = in_array($order->status, $step['doneWhen']);
                                $isCurrent = $isDone && (
                                    $i === count($trackingSteps) - 1
                                    || !in_array($order->status, $trackingSteps[$i + 1]['doneWhen'] ?? [])
                                );
                            @endphp
                            <div class="track-step {{ $isDone ? 'step-done' : '' }} {{ $isCurrent ? 'step-current' : '' }}">
                                <div class="track-dot">
                                    @if($isDone && !$isCurrent)
                                        <i class="fas fa-check" style="font-size:8px;"></i>
                                    @endif
                                </div>
                                <span class="track-label">{{ $step['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Footer --}}
                <div class="oc-footer">
                    <div class="oc-total">
                        Order Total:
                        <span class="total-amount">
                            RM {{ number_format($order->total ?? $order->price ?? 0, 2) }}
                        </span>
                        @if(($order->shipping_fee ?? 0) > 0)
                            <span class="total-note">(incl. shipping)</span>
                        @endif
                    </div>

                    <div class="oc-actions">

                        <a href="{{ route('orders.show', $order->id) }}" class="btn-craft btn-craft-outline">
                            <i class="fas fa-eye"></i> View Details
                        </a>

                        @if($canDownloadReceipt)
                            <a href="{{ route('orders.receipt', $order->id) }}"
                               class="btn-craft btn-craft-receipt">
                                <i class="fas fa-file-download"></i> Receipt
                            </a>
                        @endif

                        @if($order->status === 'completed')
                            @if($order->has_review)
                                <button class="btn-craft btn-craft-rated" disabled>
                                    <i class="fas fa-star"></i> Rated
                                </button>
                                @if($canEditReview)
                                    <a href="{{ route('reviews.edit', $orderReview->id) }}"
                                       class="btn-craft btn-craft-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('reviews.create', $order->id) }}"
                                   class="btn-craft btn-craft-primary">
                                    <i class="fas fa-star"></i> Rate
                                </a>
                            @endif
                        @elseif($order->tracking_number && $order->getTrackingUrl())
                            <a href="{{ $order->getTrackingUrl() }}"
                               target="_blank" rel="noopener"
                               class="btn-craft btn-craft-track">
                                <i class="fas fa-truck"></i> Track Parcel
                            </a>
                        @endif

                        @if($order->status === 'shipped')
                            <form action="{{ route('orders.complete', $order->id) }}"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Confirm that you have received this order?')">
                                @csrf
                                <button type="submit" class="btn-craft btn-craft-success">
                                    <i class="fas fa-check-circle"></i> Order Received
                                </button>
                            </form>
                        @endif

                        @if($order->status === 'pending_payment')
                            <a href="{{ route('order.checkout.repay', $order->id) }}"
                               class="btn-craft btn-craft-primary">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </a>
                        @endif

                        @if($order->status === 'processing')
                            <button type="button"
                                class="btn-craft btn-craft-cancel"
                                onclick="openCancelConfirm({{ $order->id }}, '{{ addslashes($order->items->first()?->name ?? 'this order') }}')">
                                <i class="fas fa-times-circle"></i> Cancel
                            </button>
                        @endif

                        {{-- ADDITION: Refund button --}}
                        @if($canRefund)
                            <button type="button"
                                class="btn-craft btn-craft-refund"
                                onclick="openRefundModal(
                                    {{ $order->id }},
                                    '{{ addslashes($order->items->first()?->name ?? 'this order') }}',
                                    'RM {{ number_format($order->total ?? 0, 2) }}'
                                )">
                                <i class="fas fa-undo-alt"></i> Refund
                            </button>
                        @endif

                    </div>
                </div>

            </div>
            @endforeach
        </div>

        {{-- Pagination — single clean block --}}
        @if($orders->lastPage() > 1)
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} results
            </div>
            {{ $orders->appends(request()->query())->links('pagination.craftistry') }}
        </div>
        @endif

    @endif

</div>
</main>

{{-- Success Popup --}}
<div class="success-popup" id="successPopup">
    <div class="success-content">
        <div class="success-icon"><i class="fas fa-check-circle"></i></div>
        <div><p id="successMessage">Success!</p></div>
    </div>
</div>

{{-- Error Popup --}}
<div class="delete-popup" id="errorPopup">
    <div class="delete-content">
        <div class="delete-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div><p id="errorMessage">Something went wrong.</p></div>
    </div>
</div>

{{-- Cancel Order Modal --}}
<div id="cancelConfirmModal"
     style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
    <div style="position:absolute; inset:0; background:rgba(0,0,0,.48); backdrop-filter:blur(3px);"
         onclick="closeCancelConfirm()"></div>
    <div id="cancelConfirmInner"
         style="position:relative; background:#fff; border-radius:16px; padding:36px 32px 28px;
                max-width:440px; width:90%;
                box-shadow:0 24px 64px rgba(102,126,234,.22), 0 4px 16px rgba(0,0,0,.08);
                text-align:center; z-index:1;">
        <div style="width:64px; height:64px; background:linear-gradient(135deg,#fff5f5,#fed7d7);
                    border-radius:50%; display:flex; align-items:center; justify-content:center;
                    margin:0 auto 18px; border:2px solid #fca5a5;
                    box-shadow:0 4px 12px rgba(239,68,68,.15);">
            <i class="fas fa-ban" style="color:#ef4444; font-size:1.55rem;"></i>
        </div>
        <h3 style="font-size:1.18rem; font-weight:800; color:#1a202c; margin-bottom:8px;">Cancel Order?</h3>
        <p style="font-size:0.84rem; color:#718096; line-height:1.7; margin-bottom:6px;">
            You are about to cancel <strong id="cancelOrderName" style="color:#4a5568;"></strong>.
        </p>
        <p style="font-size:0.82rem; color:#718096; line-height:1.65; margin-bottom:28px;">
            A <strong style="color:#48bb78;">full refund</strong> will be initiated to your original
            payment method and may take <strong>5–10 business days</strong> to appear.
            This action cannot be undone.
        </p>
        <div style="display:flex; gap:10px;">
            <button onclick="closeCancelConfirm()"
                style="flex:1; padding:12px; border-radius:8px; border:1.5px solid #e2e8f0;
                       background:#fff; color:#4a5568; font-size:0.88rem; font-weight:600;
                       cursor:pointer; font-family:inherit;"
                onmouseover="this.style.background='#f7fafc';"
                onmouseout="this.style.background='#fff';">
                Keep Order
            </button>
            <button onclick="executeCancelOrder()"
                style="flex:1; padding:12px; border-radius:8px; border:none;
                       background:linear-gradient(135deg,#ef4444,#dc2626);
                       color:#fff; font-size:0.88rem; font-weight:700;
                       cursor:pointer; font-family:inherit;
                       box-shadow:0 4px 14px rgba(239,68,68,.35);"
                onmouseover="this.style.opacity='.88';"
                onmouseout="this.style.opacity='1';">
                <i class="fas fa-ban" style="margin-right:6px;"></i>Yes, Cancel Order
            </button>
        </div>
    </div>
</div>

<form id="cancelOrderForm" method="POST" style="display:none;">@csrf</form>

{{-- ADDITION: Refund Request Modal --}}
<div id="refundModal"
     style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center;">
    <div style="position:absolute; inset:0; background:rgba(0,0,0,.48); backdrop-filter:blur(3px);"
         onclick="closeRefundModal()"></div>
    <div id="refundModalInner"
         style="position:relative; background:#fff; border-radius:16px; padding:32px 28px 26px;
                max-width:480px; width:92%;
                box-shadow:0 24px 64px rgba(102,126,234,.22), 0 4px 16px rgba(0,0,0,.08);
                z-index:1;">

        <div style="text-align:center; margin-bottom:20px;">
            <div style="width:56px; height:56px; background:linear-gradient(135deg,#fff8f0,#fef3c7);
                        border-radius:50%; display:flex; align-items:center; justify-content:center;
                        margin:0 auto 14px; border:2px solid #fcd34d;
                        box-shadow:0 4px 12px rgba(252,211,77,.25);">
                <i class="fas fa-undo-alt" style="color:#d97706; font-size:1.3rem;"></i>
            </div>
            <h3 style="font-size:1.08rem; font-weight:800; color:#1a202c; margin:0 0 4px;">Request Refund</h3>
            <p id="refundModalSubtitle" style="font-size:0.8rem; color:#718096; margin:0;"></p>
        </div>

        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:9px;
                    padding:11px 14px; margin-bottom:16px;
                    display:flex; align-items:flex-start; gap:9px;">
            <i class="fas fa-info-circle" style="color:#2563eb; font-size:14px; margin-top:1px; flex-shrink:0;"></i>
            <span style="font-size:12px; color:#1d4ed8; line-height:1.55;">
                The seller will review your request. If approved, the refund returns to your original
                payment method within <strong>3–5 business days</strong>.
            </span>
        </div>

        <div class="refund-chips">
            <button type="button" class="refund-chip" onclick="pickReason('Item not as described')">Item not as described</button>
            <button type="button" class="refund-chip" onclick="pickReason('Item damaged or defective')">Item damaged or defective</button>
            <button type="button" class="refund-chip" onclick="pickReason('Wrong item received')">Wrong item received</button>
            <button type="button" class="refund-chip" onclick="pickReason('Item not received')">Item not received</button>
            <button type="button" class="refund-chip" onclick="pickReason('Changed my mind')">Changed my mind</button>
        </div>

        <form id="refundModalForm" method="POST">
            @csrf
            <label style="font-size:13px; font-weight:600; color:#1a1a2e; display:block; margin-bottom:7px;">
                Reason <span style="color:#ef4444;">*</span>
            </label>
            <textarea
                id="refundReasonInput"
                name="reason"
                rows="4"
                placeholder="Describe why you are requesting a refund..."
                minlength="10"
                maxlength="1000"
                required
                style="width:100%; border:1.5px solid #e5e7eb; border-radius:10px;
                       padding:11px 13px; font-size:13px; font-family:inherit;
                       color:#1a1a2e; resize:vertical; outline:none;
                       transition:border-color .2s; box-sizing:border-box;"
                onfocus="this.style.borderColor='#667eea'"
                onblur="this.style.borderColor='#e5e7eb'"
            ></textarea>
            <p style="font-size:11px; color:#9ca3af; margin:5px 0 18px;">Minimum 10 characters.</p>

            <div style="display:flex; gap:10px;">
                <button type="button" onclick="closeRefundModal()"
                    style="flex:1; padding:11px; border-radius:8px; border:1.5px solid #e2e8f0;
                           background:#fff; color:#6b7280; font-size:0.88rem; font-weight:600;
                           cursor:pointer; font-family:inherit;"
                    onmouseover="this.style.background='#f9fafb';"
                    onmouseout="this.style.background='#fff';">
                    Cancel
                </button>
                <button type="submit"
                    style="flex:2; padding:11px; border-radius:8px; border:none;
                           background:linear-gradient(135deg,#667eea,#764ba2);
                           color:#fff; font-size:0.88rem; font-weight:700;
                           cursor:pointer; font-family:inherit;
                           display:flex; align-items:center; justify-content:center; gap:7px;
                           box-shadow:0 4px 14px rgba(102,126,234,.35);"
                    onmouseover="this.style.opacity='.88';"
                    onmouseout="this.style.opacity='1';">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </div>
        </form>

    </div>
</div>

<style>
@keyframes cancelModalIn {
    from { opacity:0; transform:scale(.88) translateY(16px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
</style>

<script>
window.openCancelConfirm = function (orderId, orderName) {
    var modal = document.getElementById('cancelConfirmModal');
    var form  = document.getElementById('cancelOrderForm');
    var inner = document.getElementById('cancelConfirmInner');
    form.action = '/my-orders/' + orderId + '/cancel';
    document.getElementById('cancelOrderName').textContent = '\u201c' + orderName + '\u201d';
    modal.style.display = 'flex';
    if (inner) {
        inner.style.animation = 'none';
        void inner.offsetWidth;
        inner.style.animation = 'cancelModalIn .22s cubic-bezier(.34,1.56,.64,1)';
    }
};
window.closeCancelConfirm = function () {
    document.getElementById('cancelConfirmModal').style.display = 'none';
};
window.executeCancelOrder = function () {
    document.getElementById('cancelOrderForm').submit();
};

{{-- ADDITION: refund modal JS --}}
window.openRefundModal = function (orderId, orderName, orderAmount) {
    var modal    = document.getElementById('refundModal');
    var inner    = document.getElementById('refundModalInner');
    var form     = document.getElementById('refundModalForm');
    var subtitle = document.getElementById('refundModalSubtitle');
    var textarea = document.getElementById('refundReasonInput');

    form.action       = '/refund/order/' + orderId;
    subtitle.textContent = '\u201c' + orderName + '\u201d \u2022 ' + orderAmount;
    textarea.value    = '';

    modal.style.display = 'flex';
    inner.style.animation = 'none';
    void inner.offsetWidth;
    inner.style.animation = 'cancelModalIn .22s cubic-bezier(.34,1.56,.64,1)';
};
window.closeRefundModal = function () {
    document.getElementById('refundModal').style.display = 'none';
};
window.pickReason = function (text) {
    document.getElementById('refundReasonInput').value = text;
};

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        window.closeCancelConfirm();
        window.closeRefundModal();
    }
});
</script>

<script>
(function () {
    function showSuccessPopup(msg) {
        var p = document.getElementById('successPopup');
        var m = document.getElementById('successMessage');
        if (!p || !m) return;
        m.textContent = msg;
        p.classList.add('show');
        setTimeout(function () {
            p.classList.add('hide');
            setTimeout(function () { p.classList.remove('show','hide'); }, 300);
        }, 3000);
    }
    function showErrorPopup(msg) {
        var p = document.getElementById('errorPopup');
        var m = document.getElementById('errorMessage');
        if (!p || !m) return;
        m.textContent = msg;
        p.classList.add('show');
        setTimeout(function () {
            p.classList.add('hide');
            setTimeout(function () { p.classList.remove('show','hide'); }, 300);
        }, 3000);
    }
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showSuccessPopup(@json(session('success')));
        @endif
        @if(session('error'))
            showErrorPopup(@json(session('error')));
        @endif
    });
})();
</script>

@endsection