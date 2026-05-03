{{-- resources/views/orders/index.blade.php --}}

@extends('layouts.app')

@section('title', 'My Orders')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/myOrders.css') }}">
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

@endsection