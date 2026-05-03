@extends('layouts.app')

@section('title', 'Bulk Order — ' . $bulkOrder->artworkSell->product_name)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/bulkOrderDetail.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
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
            @if($bulkOrder->isPending())      <i class="bi bi-clock"></i>
            @elseif($bulkOrder->isAccepted()) <i class="bi bi-check-circle-fill"></i>
            @elseif($bulkOrder->isPaid())     <i class="bi bi-check2-all"></i>
            @else                             <i class="bi bi-x-circle-fill"></i>
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
                        <i class="bi bi-image"></i>
                    @endif
                </div>
                <div class="artwork-info">
                    <div class="artwork-name">{{ $bulkOrder->artworkSell->product_name }}</div>
                    <div class="artwork-unit-price">
                        <i class="bi bi-tag-fill"></i> Unit Price: <strong>RM {{ number_format($bulkOrder->artworkSell->product_price, 2) }}</strong>
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
                    <span class="detail-key"><i class="bi bi-boxes"></i> Quantity</span>
                    <span class="detail-val">{{ number_format($bulkOrder->quantity) }} pieces</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="bi bi-calendar3"></i> Ship By</span>
                    <span class="detail-val">{{ $bulkOrder->last_ship_date->format('d M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="bi bi-clock"></i> Submitted</span>
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
                    <span><i class="bi bi-percent"></i> Bulk Discount</span>
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
        <i class="bi bi-hourglass-split"></i>
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
                <i class="bi bi-check-circle-fill"></i>
                The seller accepted your request! Please complete payment to confirm your order.
            </div>
            <div class="pricing-box">
                <div class="pricing-row total">
                    <span>Amount to Pay</span>
                    <span class="total-price">RM {{ number_format($bulkOrder->total_price, 2) }}</span>
                </div>
            </div>
            <a href="{{ route('bulk-orders.pay', $bulkOrder->id) }}"
               style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;box-shadow:0 4px 15px rgba(102,126,234,.35);transition:opacity .15s;"
               onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                <i class="bi bi-credit-card-fill"></i> Pay Now
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
                <i class="bi bi-check-circle-fill"></i>
                Payment complete! The seller is preparing your order.
            </div>
            <div class="detail-row">
                <span class="detail-key"><i class="bi bi-box-seam"></i> Order Status</span>
                <span class="detail-val">{{ ucfirst(str_replace('_', ' ', $bulkOrder->order->status)) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key"><i class="bi bi-calendar3"></i> Order Date</span>
                <span class="detail-val">{{ $bulkOrder->order->created_at->format('d M Y, g:i A') }}</span>
            </div>
            <a href="{{ route('orders.show', $bulkOrder->order_id) }}"
               style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;box-shadow:0 4px 15px rgba(102,126,234,.35);transition:opacity .15s;"
               onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                <i class="bi bi-eye-fill"></i> View Full Order
            </a>
        </div>
    </div>
    @endif

    {{-- ══ REFUSED ══ --}}
    @if($bulkOrder->isRefused())
    <div class="alert alert-danger">
        <i class="bi bi-x-circle-fill"></i>
        The seller could not fulfil this bulk order.
        @if($bulkOrder->seller_reason)
            <span style="display:block;margin-top:4px;font-weight:400;">Reason: {{ $bulkOrder->seller_reason }}</span>
        @endif
    </div>
    @endif

</div>
@endsection