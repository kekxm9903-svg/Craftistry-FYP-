@extends('layouts.app')

@section('title', 'Custom Order — ' . $customOrder->title)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/requestDetails.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('custom-orders.index') }}">My Custom Orders</a>
        <span class="sep">/</span>
        <span class="cur">{{ Str::limit($customOrder->title, 40) }}</span>
    </div>
</div>

<div style="max-width:700px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="javascript:history.back()" class="back-btn">← Back</a>
</div>

<div class="co-page-narrow">

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info"><i class="fas fa-info-circle"></i> {{ session('info') }}</div>
    @endif

    {{-- ══ PAGE HEADER ══ --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">{{ $customOrder->title }}</div>
            <div class="page-subtitle">
                To <strong>{{ $customOrder->seller->fullname ?? $customOrder->seller->name }}</strong>
                &nbsp;·&nbsp; {{ $customOrder->created_at->format('d M Y') }}
            </div>
        </div>
        {{-- Show order status if paid, else request status --}}
        @if($customOrder->order_id && $customOrder->order)
            <span class="order-status-chip order-status-{{ $customOrder->order->status }}">
                {{ ucfirst(str_replace('_', ' ', $customOrder->order->status)) }}
            </span>
        @else
            <span class="status-badge {{ $customOrder->statusColor() }}">
                {{ $customOrder->statusLabel() }}
            </span>
        @endif
    </div>

    {{-- ══ REQUEST DETAILS ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Request Details
            </div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">

            {{-- Reference image --}}
            @if($customOrder->reference_image)
            <div class="ref-image">
                <img src="{{ asset('storage/' . $customOrder->reference_image) }}" alt="Reference image">
            </div>
            @endif

            {{-- Detail rows --}}
            <div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-tag"></i> Your Offered Price</span>
                    <span class="detail-val price">RM {{ number_format($customOrder->buyer_price, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-{{ $customOrder->isDigital() ? 'file-image' : 'box' }}"></i> Product Type</span>
                    <span class="detail-val">{{ $customOrder->isDigital() ? '🖥️ Digital' : '📦 Physical' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-user"></i> Seller</span>
                    <span class="detail-val">{{ $customOrder->seller->fullname ?? $customOrder->seller->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-calendar-alt"></i> Submitted</span>
                    <span class="detail-val">{{ $customOrder->created_at->format('d M Y, g:i A') }}</span>
                </div>
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label class="form-label">Your Description</label>
                <div class="description-box">{{ $customOrder->description }}</div>
            </div>

        </div>
    </div>

    {{-- ══ PENDING — waiting for seller ══ --}}
    @if($customOrder->isPending())
    <div class="alert alert-info">
        <i class="fas fa-clock"></i>
        Your request has been sent. Waiting for the seller to respond.
    </div>
    @endif

    {{-- ══ REFUSED — seller refused ══ --}}
    @if($customOrder->isRefused())
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Seller's Response</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">

            <div class="reason-box">
                <div class="reason-box-title">
                    <i class="fas fa-times-circle"></i> Reason for Refusal
                </div>
                {{ $customOrder->seller_reason }}
            </div>

            {{-- Counter-price offered, buyer hasn't responded yet --}}
            @if($customOrder->hasCounterPrice() && is_null($customOrder->buyer_response))
            <div class="counter-box">
                <div>
                    <div class="counter-box-label">
                        <i class="fas fa-exchange-alt"></i> Seller's Counter Offer
                    </div>
                    <div class="counter-box-desc">
                        The seller is willing to do this at a different price. Do you accept?
                    </div>
                </div>
                <div class="counter-box-price">RM {{ number_format($customOrder->counter_price, 2) }}</div>
            </div>

            <div class="action-row">
                <form action="{{ route('custom-orders.accept-counter', $customOrder->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" style="justify-content:center;width:100%;">
                        <i class="fas fa-check"></i> Accept Counter Price
                    </button>
                </form>
                <form action="{{ route('custom-orders.refuse-counter', $customOrder->id) }}" method="POST"
                      onsubmit="return confirm('Refusing will permanently cancel this request. Continue?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger" style="justify-content:center;width:100%;">
                        <i class="fas fa-times"></i> Refuse & Cancel
                    </button>
                </form>
            </div>

            @elseif(!$customOrder->hasCounterPrice())
            {{-- Flat refusal, no counter --}}
            <p style="font-size:var(--fs-sm);color:var(--muted);">
                The seller did not provide an alternative price. You may request from another artist.
            </p>
            <a href="{{ route('artist.browse') }}" class="btn btn-primary" style="justify-content:center;">
                <i class="fas fa-compass"></i> Browse Artists
            </a>
            @endif

        </div>
    </div>
    @endif

    {{-- ══ ACCEPTED — pending payment (only if not yet paid) ══ --}}
    @if($customOrder->isAccepted() && !$customOrder->order_id)
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Next Step</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">

            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                The seller accepted your request! Please complete payment to confirm your order.
            </div>

            <div class="counter-box" style="background:#f0f9ff;border-color:#bae6fd;">
                <div>
                    <div class="counter-box-label" style="color:#075985;">Amount to Pay</div>
                </div>
                <div class="counter-box-price" style="color:var(--primary);">
                    RM {{ number_format($customOrder->finalPrice(), 2) }}
                </div>
            </div>

            <a href="{{ route('custom-orders.pay', $customOrder->id) }}" class="btn btn-primary" style="justify-content:center;">
                <i class="fas fa-credit-card"></i> Proceed to Payment
            </a>

        </div>
    </div>
    @endif

    {{-- ══ PAID — order created, following normal order flow ══ --}}
    @if($customOrder->order_id)
    @php $order = $customOrder->order; @endphp
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Order Status</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Payment complete! Your custom order is confirmed and the seller is preparing it.
            </div>
            @if($order)
            <div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-box"></i> Order Status</span>
                    <span class="detail-val">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-calendar-alt"></i> Order Date</span>
                    <span class="detail-val">{{ $order->created_at->format('d M Y, g:i A') }}</span>
                </div>
            </div>
            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary" style="justify-content:center;">
                <i class="fas fa-eye"></i> View Full Order
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- ══ CANCELLED ══ --}}
    @if($customOrder->isCancelled())
    <div class="alert alert-danger">
        <i class="fas fa-times-circle"></i>
        This request has been cancelled.
    </div>
    @endif

</div>
@endsection