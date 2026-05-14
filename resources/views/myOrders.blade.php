@extends('layouts.app')

@section('title', 'My Orders')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/myOrders.css') }}">























<style>
/* ── Success / Error Popups — exact artistProfile.css styles ── */
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

    {{-- Back button --}}
    <a href="{{ route('dashboard') }}" class="back-btn">← Back</a>

    {{-- ── Page Header Card ── --}}
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

    {{-- ── Quick Tabs Card ── --}}
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

    {{-- ── Status Pills Card ── --}}
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

    {{-- Flash messages handled by popup toast (see bottom of page) --}}

    {{-- ── Empty State ── --}}
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

        {{-- ── Orders List ── --}}
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
            @endphp

            <div class="order-card">

                {{-- Card Header --}}
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
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="oc-body">
                    <div class="oc-item-row">

                    {{-- Thumbnail --}}
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

                        {{-- Info --}}
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

                        {{-- Price --}}
                        <div class="oc-item-price">
                            RM {{ number_format($order->items->first()?->price ?? $order->price ?? 0, 2) }}
                        </div>

                    </div>

                    {{-- Extra items --}}
                    @if($extraCount > 0)
                        <div class="oc-more-items">
                            + {{ $extraCount }} more item{{ $extraCount > 1 ? 's' : '' }}
                            — <a href="{{ route('orders.show', $order->id) }}">View all</a>
                        </div>
                    @endif
                </div>

                {{-- Tracking bar --}}
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

                {{-- Card Footer --}}
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

                        {{-- View Details --}}
                        <a href="{{ route('orders.show', $order->id) }}" class="btn-craft btn-craft-outline">
                            <i class="fas fa-eye"></i> View Details
                        </a>

                        {{-- Download Receipt --}}
                        @if($canDownloadReceipt)
                            <a href="{{ route('orders.receipt', $order->id) }}"
                               class="btn-craft btn-craft-receipt"
                               title="Download PDF Receipt">
                                <i class="fas fa-file-download"></i> Receipt
                            </a>
                        @endif

                        {{-- Rate / Rated / Track --}}
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

                        {{-- Order Received --}}
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

                        {{-- Pay Now --}}
                        @if($order->status === 'pending_payment')
                            <a href="{{ route('order.checkout.repay', $order->id) }}"
                               class="btn-craft btn-craft-primary">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </a>
                        @endif

                        {{-- Cancel Order — only when paid but seller hasn't accepted yet --}}
                        @if($order->status === 'processing')
                            <button type="button"
                                class="btn-craft btn-craft-cancel"
                                onclick="openCancelConfirm({{ $order->id }}, '{{ addslashes($order->items->first()?->name ?? 'this order') }}')">
                                <i class="fas fa-times-circle"></i> Cancel
                            </button>
                        @endif

                    </div>
                </div>

            </div>{{-- /order-card --}}
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrapper">
            {{ $orders->appends(request()->query())->links() }}
        </div>

    @endif

</div>
</main>

{{-- SUCCESS POPUP — exact artistProfile.blade.php markup --}}
<div class="success-popup" id="successPopup">
    <div class="success-content">
        <div class="success-icon"><i class="fas fa-check-circle"></i></div>
        <div><p id="successMessage">Success!</p></div>
    </div>
</div>

{{-- ERROR POPUP — uses delete-popup class (red, same as artistProfile) --}}
<div class="delete-popup" id="errorPopup">
    <div class="delete-content">
        <div class="delete-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div><p id="errorMessage">Something went wrong.</p></div>
    </div>
</div>


{{-- ── Cancel Order Confirm Modal ── --}}
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
        <h3 style="font-size:1.18rem; font-weight:800; color:#1a202c; margin-bottom:8px;">
            Cancel Order?
        </h3>
        <p style="font-size:0.84rem; color:#718096; line-height:1.7; margin-bottom:6px;">
            You are about to cancel
            <strong id="cancelOrderName" style="color:#4a5568;"></strong>.
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
                       cursor:pointer; font-family:inherit; transition:all .15s;"
                onmouseover="this.style.background='#f7fafc'; this.style.borderColor='#cbd5e0';"
                onmouseout="this.style.background='#fff'; this.style.borderColor='#e2e8f0';">
                Keep Order
            </button>
            <button onclick="executeCancelOrder()"
                style="flex:1; padding:12px; border-radius:8px; border:none;
                       background:linear-gradient(135deg,#ef4444,#dc2626);
                       color:#fff; font-size:0.88rem; font-weight:700;
                       cursor:pointer; font-family:inherit; transition:all .15s;
                       box-shadow:0 4px 14px rgba(239,68,68,.35);"
                onmouseover="this.style.opacity='.88'; this.style.transform='translateY(-1px)';"
                onmouseout="this.style.opacity='1'; this.style.transform='translateY(0)';">
                <i class="fas fa-ban" style="margin-right:6px;"></i>Yes, Cancel Order
            </button>
        </div>
    </div>
</div>

{{-- Hidden cancel form — submitted programmatically --}}
<form id="cancelOrderForm" method="POST" style="display:none;">
    @csrf
</form>

<style>
@keyframes cancelModalIn {
    from { opacity:0; transform:scale(.88) translateY(16px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
</style>

<script>
// ── Cancel Order Modal ──
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

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') window.closeCancelConfirm();
});
</script>

<script>
(function () {
    function showSuccessPopup(message) {
        const popup     = document.getElementById('successPopup');
        const messageEl = document.getElementById('successMessage');
        if (popup && messageEl) {
            messageEl.textContent = message;
            popup.classList.add('show');
            setTimeout(() => {
                popup.classList.add('hide');
                setTimeout(() => popup.classList.remove('show', 'hide'), 300);
            }, 3000);
        }
    }

    function showErrorPopup(message) {
        const popup     = document.getElementById('errorPopup');
        const messageEl = document.getElementById('errorMessage');
        if (popup && messageEl) {
            messageEl.textContent = message;
            popup.classList.add('show');
            setTimeout(() => {
                popup.classList.add('hide');
                setTimeout(() => popup.classList.remove('show', 'hide'), 300);
            }, 3000);
        }
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