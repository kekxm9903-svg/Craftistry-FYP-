@extends('layouts.app')

@section('title', 'Request List')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistRequestList.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">Request List</span>
    </div>
</div>

<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="javascript:history.back()" class="back-btn">← Back</a>
</div>

<div class="co-page">

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- Page header --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">Request List</div>
            <div class="page-subtitle">Manage custom and bulk order requests from buyers</div>
        </div>
        @php
            $pendingCustom = $requests->getCollection()->where('status', 'pending')->count();
            $pendingBulk   = $bulkOrders->where('status', 'pending')->count();
            $totalPending  = $pendingCustom + $pendingBulk;
        @endphp
        @if($totalPending > 0)
            <span class="status-badge orange" style="font-size:var(--fs-base);padding:6px 14px;">
                <i class="fas fa-inbox"></i> {{ $totalPending }} pending
            </span>
        @endif
    </div>

    {{-- ── TABS ── --}}
    @php $activeTab = request('tab', 'custom'); @endphp
    <div class="tab-bar">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'custom']) }}"
           class="tab-item {{ $activeTab === 'custom' ? 'active' : '' }}">
            <i class="fas fa-paint-brush"></i>
            Custom Orders
            @if($pendingCustom > 0)
                <span class="tab-badge">{{ $pendingCustom }}</span>
            @endif
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'bulk']) }}"
           class="tab-item {{ $activeTab === 'bulk' ? 'active' : '' }}">
            <i class="fas fa-boxes"></i>
            Bulk Orders
            @if($pendingBulk > 0)
                <span class="tab-badge">{{ $pendingBulk }}</span>
            @endif
        </a>
    </div>

    {{-- ══ CUSTOM ORDERS TAB ══ --}}
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
                    <h3>No custom order requests yet</h3>
                    <p>When buyers send you custom order requests from your profile, they will appear here.</p>
                </div>
            @else
                <div class="request-list">
                    @foreach($requests as $req)
                    @php $order = $req->order; @endphp
                    <a href="{{ route('artist.custom-orders.show', $req->id) }}" class="request-row">

                        <div class="request-thumb">
                            @if($req->reference_image)
                                <img src="{{ asset('storage/' . $req->reference_image) }}" alt="">
                            @else
                                <i class="fas fa-paint-brush"></i>
                            @endif
                        </div>

                        <div class="request-body">
                            <div class="request-title">{{ $req->title }}</div>
                            <div class="request-meta">
                                <span><i class="fas fa-user"></i> {{ $req->buyer->fullname ?? $req->buyer->name }}</span>
                                <span><i class="fas fa-clock"></i> {{ $req->created_at->diffForHumans() }}</span>
                                @if($req->isPending())
                                    <span style="color:#d97706;font-weight:700;">
                                        <i class="fas fa-exclamation-circle"></i> Needs your response
                                    </span>
                                @endif
                                @if($req->isRefused() && $req->hasCounterPrice())
                                    <span style="color:var(--primary);font-weight:700;">
                                        <i class="fas fa-exchange-alt"></i> Awaiting buyer
                                    </span>
                                @endif
                                @if($req->isAccepted() && !$req->order_id)
                                    <span style="color:#2563eb;font-weight:700;">
                                        <i class="fas fa-credit-card"></i> Awaiting payment
                                    </span>
                                @endif
                                @if($req->order_id && $order)
                                    <span class="order-status-chip order-status-{{ $order->status }}">
                                        <i class="fas fa-box"></i>
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="request-right">
                            <div class="request-price">RM {{ number_format($req->buyer_price, 2) }}</div>
                            @if($req->order_id && $order)
                                <span class="order-status-chip order-status-{{ $order->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            @else
                                <span class="status-badge {{ $req->statusColor() }}">
                                    {{ $req->statusLabel() }}
                                </span>
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

    {{-- ══ BULK ORDERS TAB ══ --}}
    @if($activeTab === 'bulk')
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Bulk Order Requests
            </div>
            @if($bulkOrders->count() > 0)
                <span class="section-count">{{ $bulkOrders->count() }} total</span>
            @endif
        </div>
        <div class="sp-card-body">

            @if($bulkOrders->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-boxes"></i></div>
                    <h3>No bulk order requests yet</h3>
                    <p>When buyers place bulk orders for your artworks, they will appear here.</p>
                </div>
            @else
                <div class="request-list">
                    @foreach($bulkOrders as $bulk)
                    <div class="request-row" style="cursor:default;">

                        {{-- Artwork thumbnail --}}
                        <div class="request-thumb">
                            @if($bulk->artworkSell->image_path)
                                <img src="{{ asset('storage/' . $bulk->artworkSell->image_path) }}" alt="">
                            @else
                                <i class="fas fa-boxes"></i>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="request-body">
                            <div class="request-title">{{ $bulk->artworkSell->product_name }}</div>
                            <div class="request-meta">
                                <span><i class="fas fa-user"></i> {{ $bulk->buyer->fullname ?? $bulk->buyer->name }}</span>
                                <span><i class="fas fa-boxes"></i> {{ number_format($bulk->quantity) }} pcs</span>
                                <span><i class="fas fa-calendar-alt"></i> Ship by {{ $bulk->last_ship_date->format('d M Y') }}</span>
                                <span><i class="fas fa-clock"></i> {{ $bulk->created_at->diffForHumans() }}</span>
                                @if($bulk->status === 'pending')
                                    <span style="color:#d97706;font-weight:700;">
                                        <i class="fas fa-exclamation-circle"></i> Needs your response
                                    </span>
                                @endif
                            </div>
                            @if($bulk->description)
                                <div style="font-size:var(--fs-sm);color:var(--muted);margin-top:4px;line-height:1.5;">
                                    {{ Str::limit($bulk->description, 100) }}
                                </div>
                            @endif
                        </div>

                        {{-- Right --}}
                        <div class="request-right">
                            <div class="request-price">RM {{ number_format($bulk->discounted_price * $bulk->quantity, 2) }}</div>
                            <span class="status-badge {{ $bulk->status === 'pending' ? 'orange' : ($bulk->status === 'accepted' ? 'green' : 'red') }}">
                                {{ ucfirst($bulk->status) }}
                            </span>

                            {{-- Accept / Refuse actions for pending --}}
                            @if($bulk->status === 'pending')
                            <div style="display:flex;gap:6px;margin-top:8px;" onclick="event.stopPropagation()">
                                <form action="{{ route('artist.bulk-orders.accept', $bulk->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="action-btn action-btn-accept"
                                            onclick="return confirm('Accept this bulk order?')">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                </form>
                                <form action="{{ route('artist.bulk-orders.refuse', $bulk->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="action-btn action-btn-refuse"
                                            onclick="return confirm('Refuse this bulk order?')">
                                        <i class="fas fa-times"></i> Refuse
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>

                    </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
    @endif

</div>
@endsection