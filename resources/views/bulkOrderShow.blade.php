@extends('layouts.app')

@section('title', 'Bulk Order — ' . $bulkOrder->artworkSell->product_name)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/bulkOrderDetail.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('bulk-orders.index') }}">My Bulk Orders</a>
        <span class="sep">/</span>
        <span class="cur">{{ Str::limit($bulkOrder->artworkSell->product_name, 30) }}</span>
    </div>
</div>

<div style="max-width:700px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="javascript:history.back()" class="back-btn">← Back</a>
</div>

<div class="co-page-narrow">

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- ══ PAGE HEADER ══ --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">Bulk Order Request</div>
            <div class="page-subtitle">
                Submitted {{ $bulkOrder->created_at->format('d M Y') }}
            </div>
        </div>
        <span class="status-badge {{ $bulkOrder->statusColor() }}">
            @if($bulkOrder->isPending())      <i class="fas fa-clock"></i>
            @elseif($bulkOrder->isAccepted()) <i class="fas fa-check-circle"></i>
            @elseif($bulkOrder->isPaid())     <i class="fas fa-check-double"></i>
            @else                             <i class="fas fa-times-circle"></i>
            @endif
            {{ $bulkOrder->statusLabel() }}
        </span>
    </div>

    {{-- ══ ARTWORK ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Artwork</div>
        </div>
        <div class="sp-card-body">
            <div class="artwork-row">
                <div class="artwork-thumb">
                    @if($bulkOrder->artworkSell->image_path)
                        <img src="{{ asset('storage/' . $bulkOrder->artworkSell->image_path) }}" alt="">
                    @else
                        <i class="fas fa-image"></i>
                    @endif
                </div>
                <div class="artwork-info">
                    <div class="artwork-name">{{ $bulkOrder->artworkSell->product_name }}</div>
                    <div class="artwork-unit-price">
                        <i class="fas fa-tag"></i> Unit Price: <strong>RM {{ number_format($bulkOrder->artworkSell->product_price, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ ORDER DETAILS ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Order Details</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">
            <div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-boxes"></i> Quantity</span>
                    <span class="detail-val">{{ number_format($bulkOrder->quantity) }} pieces</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-calendar-alt"></i> Ship By</span>
                    <span class="detail-val">{{ $bulkOrder->last_ship_date->format('d M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-clock"></i> Submitted</span>
                    <span class="detail-val">{{ $bulkOrder->created_at->format('d M Y, g:i A') }}</span>
                </div>
            </div>

            {{-- Pricing breakdown --}}
            <div class="pricing-box">
                <div class="pricing-row">
                    <span>Unit Price</span>
                    <span>RM {{ number_format($bulkOrder->unit_price, 2) }}</span>
                </div>
                @if($bulkOrder->is_discounted)
                <div class="pricing-row discount">
                    <span><i class="fas fa-percentage"></i> Bulk Discount</span>
                    <span class="discount-tag">- RM {{ number_format(($bulkOrder->unit_price - $bulkOrder->discounted_price) * $bulkOrder->quantity, 2) }}</span>
                </div>
                <div class="pricing-row">
                    <span>Discounted Unit Price</span>
                    <span>RM {{ number_format($bulkOrder->discounted_price, 2) }}</span>
                </div>
                @endif
                <div class="pricing-row">
                    <span>Quantity</span>
                    <span>× {{ number_format($bulkOrder->quantity) }}</span>
                </div>
                <div class="pricing-row total">
                    <span>Total</span>
                    <span class="total-price">RM {{ number_format($bulkOrder->total_price, 2) }}</span>
                </div>
            </div>

            @if($bulkOrder->description)
            <div class="form-group">
                <label class="form-label">Your Notes</label>
                <div class="description-box">{{ $bulkOrder->description }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- ══ PENDING — waiting for seller ══ --}}
    @if($bulkOrder->isPending())
    <div class="alert" style="background:rgba(245,158,11,.1);color:#92400e;border:1px solid rgba(245,158,11,.25);">
        <i class="fas fa-hourglass-half"></i>
        Waiting for the seller to review your request.
    </div>
    @endif

    {{-- ══ ACCEPTED — pay now ══ --}}
    @if($bulkOrder->isAccepted())
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Complete Your Order</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                The seller accepted your request! Please complete payment to confirm your order.
            </div>
            <div class="pricing-box">
                <div class="pricing-row total">
                    <span>Amount to Pay</span>
                    <span class="total-price">RM {{ number_format($bulkOrder->total_price, 2) }}</span>
                </div>
            </div>
            <a href="{{ route('bulk-orders.pay', $bulkOrder->id) }}" class="btn btn-primary">
                <i class="fas fa-credit-card"></i> Pay Now
            </a>
        </div>
    </div>
    @endif

    {{-- ══ PAID — order created ══ --}}
    @if($bulkOrder->isPaid() && $bulkOrder->order_id)
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Order Status</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Payment complete! The seller is preparing your order.
            </div>
            <div class="detail-row">
                <span class="detail-key"><i class="fas fa-box"></i> Order Status</span>
                <span class="detail-val">{{ ucfirst(str_replace('_', ' ', $bulkOrder->order->status)) }}</span>
            </div>
            <a href="{{ route('orders.show', $bulkOrder->order_id) }}" class="btn btn-primary">
                <i class="fas fa-clipboard-list"></i> View Order
            </a>
        </div>
    </div>
    @endif

    {{-- ══ REFUSED ══ --}}
    @if($bulkOrder->isRefused())
    <div class="alert alert-danger">
        <i class="fas fa-times-circle"></i>
        The seller could not fulfil this bulk order.
        @if($bulkOrder->seller_reason)
            <span style="display:block;margin-top:4px;font-weight:400;">Reason: {{ $bulkOrder->seller_reason }}</span>
        @endif
    </div>
    @endif

</div>
@endsection