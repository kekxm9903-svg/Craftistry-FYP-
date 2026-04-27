@extends('layouts.app')

@section('title', 'My Custom Orders')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/userRequestList.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">My Custom Orders</span>
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
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">My Custom Orders</div>
            <div class="page-subtitle">
                @if($requests->total() > 0)
                    {{ $requests->total() }} {{ Str::plural('request', $requests->total()) }}
                @else
                    You haven't made any custom order requests yet
                @endif
            </div>
        </div>
        <a href="{{ route('artist.browse') }}" class="btn btn-primary">
            <i class="fas fa-compass"></i> Browse Artists
        </a>
    </div>

    {{-- List card --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                All Requests
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
                                    <i class="fas fa-clock"></i>
                                    {{ $req->created_at->diffForHumans() }}
                                </span>
                                {{-- Action needed: seller countered --}}
                                @if($req->isRefused() && $req->hasCounterPrice() && is_null($req->buyer_response))
                                <span style="color:#d97706;font-weight:700;">
                                    <i class="fas fa-exclamation-circle"></i> Action needed
                                </span>
                                @endif
                                {{-- Payment pending — only if not yet paid --}}
                                @if($req->isAccepted() && !$req->order_id)
                                <span style="color:#2563eb;font-weight:700;">
                                    <i class="fas fa-credit-card"></i> Payment pending
                                </span>
                                @endif
                                {{-- Order status once paid --}}
                                @if($req->order_id && $order)
                                <span class="order-status-chip order-status-{{ $order->status }}">
                                    <i class="fas fa-box"></i>
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                                @endif
                            </div>
                        </div>

                        {{-- Right --}}
                        <div class="request-right">
                            <div class="request-price">RM {{ number_format($req->finalPrice(), 2) }}</div>
                            {{-- Show order status if paid, else request status --}}
                            @if($req->order_id && $order)
                                <span class="order-status-chip order-status-{{ $order->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                                <a href="{{ route('orders.show', $order->id) }}"
                                   class="pay-btn" style="background:linear-gradient(135deg,var(--primary),var(--primary-2));"
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

</div>
@endsection