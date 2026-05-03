@extends('layouts.app')

@section('title', 'My Orders & Requests')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/userRequestList.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">My Orders & Requests</span>
    </div>
</div>

<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="javascript:history.back()" class="back-btn">← Back</a>
</div>

<div class="co-page">

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info"><i class="fas fa-info-circle"></i> {{ session('info') }}</div>
    @endif

    {{-- Page header --}}
    @php
        $totalPending = $requests->getCollection()->filter(fn($r) =>
            ($r->isRefused() && $r->hasCounterPrice() && is_null($r->buyer_response)) ||
            ($r->isAccepted() && !$r->order_id)
        )->count()
        + $bulkOrders->getCollection()->filter(fn($b) =>
            $b->status === 'accepted' && !$b->order_id
        )->count();
    @endphp

    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">Request List</div>
            <div class="page-subtitle">Manage and track your custom and bulk order requests</div>
        </div>
        @if($totalPending > 0)
        <div class="pending-pill">
            <i class="fas fa-bell"></i> {{ $totalPending }} pending
        </div>
        @endif
    </div>

    {{-- Tab bar --}}
    @php
        $activeTab = request('tab', 'custom');
        $customPending = $requests->getCollection()->filter(fn($r) =>
            ($r->isRefused() && $r->hasCounterPrice() && is_null($r->buyer_response)) ||
            ($r->isAccepted() && !$r->order_id)
        )->count();
        $bulkPending = $bulkOrders->getCollection()->filter(fn($b) =>
            $b->status === 'accepted' && !$b->order_id
        )->count();
    @endphp

    <div class="tab-bar">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'custom']) }}"
           class="tab-item {{ $activeTab === 'custom' ? 'active' : '' }}">
            <i class="fas fa-paint-brush"></i>
            Custom Orders
            @if($customPending > 0)
                <span class="tab-badge">{{ $customPending }}</span>
            @endif
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'bulk']) }}"
           class="tab-item {{ $activeTab === 'bulk' ? 'active' : '' }}">
            <i class="fas fa-boxes"></i>
            Bulk Orders
            @if($bulkPending > 0)
                <span class="tab-badge">{{ $bulkPending }}</span>
            @endif
        </a>
    </div>

    {{-- ══════════════════════════════════════
         CUSTOM ORDERS TAB
    ══════════════════════════════════════ --}}
    @if($activeTab === 'custom')
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Custom Order Requests
            </div>
            @if($requests->total() > 0)
                <span class="section-count">{{ $requests->total() }} total</span>
            @endif
        </div>
        <div class="sp-card-body">

            @if($requests->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-paint-brush"></i></div>
                    <h3>No custom orders yet</h3>
                    <p>Browse artists and send a custom order request to commission your own unique artwork.</p>
                    <a href="{{ route('artist.browse') }}" class="btn btn-primary">
                        <i class="fas fa-compass"></i> Browse Artists
                    </a>
                </div>
            @else
                <div class="request-list">
                    @foreach($requests as $req)
                    @php $order = $req->order; @endphp
                    <a href="{{ route('custom-orders.show', $req->id) }}" class="request-row">

                        {{-- Thumbnail --}}
                        <div class="request-thumb">
                            @if($req->reference_image)
                                <img src="{{ asset('storage/' . $req->reference_image) }}" alt="">
                            @else
                                <i class="fas fa-paint-brush"></i>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="request-body">
                            <div class="request-title">{{ $req->title }}</div>
                            <div class="request-meta">
                                <span>
                                    <i class="fas fa-user"></i>
                                    {{ $req->seller->fullname ?? $req->seller->name }}
                                </span>
                                <span>
                                    <i class="fas fa-tag"></i>
                                    {{ ucfirst($req->product_type) }}
                                </span>
                                <span>
                                    <i class="fas fa-clock"></i>
                                    {{ $req->created_at->diffForHumans() }}
                                </span>
                                @if($req->isRefused() && $req->hasCounterPrice() && is_null($req->buyer_response))
                                <span class="meta-urgent">
                                    <i class="fas fa-exclamation-circle"></i> Action needed
                                </span>
                                @endif
                                @if($req->isAccepted() && !$req->order_id)
                                <span class="meta-payment">
                                    <i class="fas fa-credit-card"></i> Awaiting payment
                                </span>
                                @endif
                                @if($req->order_id && $order)
                                <span class="order-status-chip order-status-{{ $order->status }}">
                                    <i class="fas fa-box"></i> {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                                @endif
                            </div>
                            @if($req->description)
                            <div class="request-desc">{{ Str::limit($req->description, 90) }}</div>
                            @endif
                        </div>

                        {{-- Right --}}
                        <div class="request-right">
                            <div class="request-price">RM {{ number_format($req->finalPrice(), 2) }}</div>
                            @if($req->order_id && $order)
                                <span class="order-status-chip order-status-{{ $order->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                                <a href="{{ route('orders.show', $order->id) }}"
                                   class="view-btn"
                                   onclick="event.stopPropagation()">
                                    <i class="fas fa-eye"></i> View Order
                                </a>
                            @else
                                <span class="status-badge {{ $req->statusColor() }}">
                                    {{ $req->statusLabel() }}
                                </span>
                                @if($req->isAccepted())
                                <a href="{{ route('custom-orders.pay', $req->id) }}"
                                   class="pay-btn"
                                   onclick="event.stopPropagation()">
                                    <i class="fas fa-credit-card"></i> Pay Now
                                </a>
                                @endif
                            @endif
                        </div>

                    </a>
                    @endforeach
                </div>
                @if($requests->hasPages())
                <div class="pagination-wrapper">{{ $requests->links() }}</div>
                @endif
            @endif

        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════
         BULK ORDERS TAB
    ══════════════════════════════════════ --}}
    @if($activeTab === 'bulk')
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Bulk Order Requests
            </div>
            @if($bulkOrders->total() > 0)
                <span class="section-count">{{ $bulkOrders->total() }} total</span>
            @endif
        </div>
        <div class="sp-card-body">

            @if($bulkOrders->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-boxes"></i></div>
                    <h3>No bulk orders yet</h3>
                    <p>Browse artworks and place a bulk order to buy large quantities at a discounted rate.</p>
                    <a href="{{ route('artist.browse') }}" class="btn btn-primary">
                        <i class="fas fa-compass"></i> Browse Artists
                    </a>
                </div>
            @else
                <div class="request-list">
                    @foreach($bulkOrders as $bulk)
                    @php
                        $artwork = $bulk->artworkSell;
                        $order   = $bulk->order;
                        $price   = $bulk->discounted_price ?? $bulk->unit_price;
                        $total   = $price * $bulk->quantity;
                    @endphp
                    <a href="{{ route('bulk-orders.show', $bulk->id) }}" class="request-row">

                        {{-- Thumbnail --}}
                        <div class="request-thumb">
                            @if($artwork && $artwork->image_path)
                                <img src="{{ asset('storage/' . $artwork->image_path) }}" alt="">
                            @else
                                <i class="fas fa-boxes"></i>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="request-body">
                            <div class="request-title">
                                {{ $artwork->product_name ?? 'Artwork #' . $bulk->artwork_sell_id }}
                            </div>
                            <div class="request-meta">
                                @if($artwork && $artwork->artist && $artwork->artist->user)
                                <span>
                                    <i class="fas fa-user"></i>
                                    {{ $artwork->artist->user->fullname ?? $artwork->artist->user->name }}
                                </span>
                                @endif
                                <span>
                                    <i class="fas fa-users"></i>
                                    {{ $bulk->quantity }} {{ Str::plural('pcs', $bulk->quantity) }}
                                </span>
                                @if($bulk->last_ship_date)
                                <span>
                                    <i class="fas fa-calendar-alt"></i>
                                    Ship by {{ \Carbon\Carbon::parse($bulk->last_ship_date)->format('d M Y') }}
                                </span>
                                @endif
                                <span>
                                    <i class="fas fa-clock"></i>
                                    {{ $bulk->created_at->diffForHumans() }}
                                </span>
                                @if($bulk->status === 'accepted' && !$bulk->order_id)
                                <span class="meta-payment">
                                    <i class="fas fa-credit-card"></i> Awaiting payment
                                </span>
                                @endif
                                @if($bulk->order_id && $order)
                                <span class="order-status-chip order-status-{{ $order->status }}">
                                    <i class="fas fa-box"></i> {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                                @endif
                            </div>
                            @if($bulk->description)
                            <div class="request-desc">{{ Str::limit($bulk->description, 90) }}</div>
                            @endif
                        </div>

                        {{-- Right --}}
                        <div class="request-right">
                            <div class="request-price">RM {{ number_format($total, 2) }}</div>
                            <div class="bulk-qty-note">RM {{ number_format($price, 2) }} × {{ $bulk->quantity }}</div>
                            @if($bulk->order_id && $order)
                                <span class="order-status-chip order-status-{{ $order->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                                <a href="{{ route('orders.show', $order->id) }}"
                                   class="view-btn"
                                   onclick="event.stopPropagation()">
                                    <i class="fas fa-eye"></i> View Order
                                </a>
                            @else
                                @php
                                    $bulkColor = match($bulk->status) {
                                        'pending'  => 'orange',
                                        'accepted' => 'green',
                                        'refused'  => 'red',
                                        default    => 'gray',
                                    };
                                    $bulkLabel = match($bulk->status) {
                                        'pending'  => 'Pending',
                                        'accepted' => 'Accepted',
                                        'refused'  => 'Refused',
                                        default    => ucfirst($bulk->status),
                                    };
                                @endphp
                                <span class="status-badge {{ $bulkColor }}">{{ $bulkLabel }}</span>
                                @if($bulk->status === 'accepted' && !$bulk->order_id)
                                <a href="{{ route('bulk-orders.pay', $bulk->id) }}"
                                   class="pay-btn"
                                   onclick="event.stopPropagation()">
                                    <i class="fas fa-credit-card"></i> Pay Now
                                </a>
                                @endif
                            @endif
                        </div>

                    </a>
                    @endforeach
                </div>
                @if($bulkOrders->hasPages())
                <div class="pagination-wrapper">{{ $bulkOrders->links() }}</div>
                @endif
            @endif

        </div>
    </div>
    @endif

</div>
@endsection