@extends('layouts.app')

@section('title', 'Bulk Order — ' . $bulk->artworkSell->product_name)

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
        <a href="{{ route('artist.custom-orders.index', ['tab' => 'bulk']) }}">Request List</a>
        <span class="sep">/</span>
        <span class="cur">Bulk Order #{{ $bulk->id }}</span>
    </div>
</div>

<div style="max-width:700px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="javascript:history.back()" class="back-btn">← Back</a>
</div>

<div class="co-page-narrow">

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- PAGE HEADER --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">Bulk Order Request</div>
            <div class="page-subtitle">
                From <strong>{{ $bulk->buyer->fullname ?? $bulk->buyer->name }}</strong>
                &nbsp;·&nbsp; {{ $bulk->created_at->format('d M Y') }}
            </div>
        </div>
        @if($bulk->order_id && $bulk->order)
            <span class="order-status-chip order-status-{{ $bulk->order->status }}">
                <i class="fas fa-box"></i>
                {{ ucfirst(str_replace('_', ' ', $bulk->order->status)) }}
            </span>
        @else
            <span class="status-badge {{ $bulk->statusColor() }}">
                {{ $bulk->statusLabel() }}
            </span>
        @endif
    </div>

    {{-- ARTWORK --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Artwork</div>
        </div>
        <div class="sp-card-body">
            <div class="artwork-row">
                <div class="artwork-thumb">
                    @if($bulk->artworkSell->image_path)
                        <img src="{{ asset('storage/' . $bulk->artworkSell->image_path) }}" alt="">
                    @else
                        <i class="fas fa-image"></i>
                    @endif
                </div>
                <div class="artwork-info">
                    <div class="artwork-name">{{ $bulk->artworkSell->product_name }}</div>
                    <div class="artwork-desc">{{ Str::limit($bulk->artworkSell->product_description ?? '', 120) }}</div>
                    <div class="artwork-unit-price">
                        <i class="fas fa-tag"></i> Unit Price: <strong>RM {{ number_format($bulk->artworkSell->product_price, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ORDER DETAILS --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Order Details</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">
            <div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-user"></i> Buyer</span>
                    <span class="detail-val">{{ $bulk->buyer->fullname ?? $bulk->buyer->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-boxes"></i> Quantity</span>
                    <span class="detail-val">{{ number_format($bulk->quantity) }} pieces</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-calendar-alt"></i> Ship By</span>
                    <span class="detail-val">{{ $bulk->last_ship_date->format('d M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-clock"></i> Submitted</span>
                    <span class="detail-val">{{ $bulk->created_at->format('d M Y, g:i A') }}</span>
                </div>
            </div>

            <div class="pricing-box">
                <div class="pricing-row">
                    <span>Unit Price</span>
                    <span>RM {{ number_format($bulk->unit_price, 2) }}</span>
                </div>
                @if($bulk->is_discounted)
                <div class="pricing-row discount">
                    <span><i class="fas fa-percentage"></i> Bulk Discount Applied</span>
                    <span class="discount-tag">- RM {{ number_format(($bulk->unit_price - $bulk->discounted_price) * $bulk->quantity, 2) }}</span>
                </div>
                <div class="pricing-row">
                    <span>Discounted Unit Price</span>
                    <span>RM {{ number_format($bulk->discounted_price, 2) }}</span>
                </div>
                @endif
                <div class="pricing-row">
                    <span>Quantity</span>
                    <span>x {{ number_format($bulk->quantity) }}</span>
                </div>
                <div class="pricing-row total">
                    <span>Total Order Value</span>
                    <span class="total-price">RM {{ number_format($bulk->total_price, 2) }}</span>
                </div>
            </div>

            @if($bulk->description)
            <div class="form-group">
                <label class="form-label">Buyer's Notes</label>
                <div class="description-box">{{ $bulk->description }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- PENDING --}}
    @if($bulk->isPending())
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Your Response</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-lg);">
            <div>
                <p style="font-size:var(--fs-sm);color:var(--muted);margin-bottom:var(--sp-sm);">
                    Accepting notifies the buyer to complete payment for
                    <strong style="color:var(--ink);">{{ number_format($bulk->quantity) }} pcs</strong>
                    totalling <strong style="color:var(--ink);">RM {{ number_format($bulk->total_price, 2) }}</strong>.
                </p>
                <form action="{{ route('artist.bulk-orders.accept', $bulk->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Accept this bulk order?')">
                        <i class="fas fa-check"></i> Accept Bulk Order
                    </button>
                </form>
            </div>
            <div style="height:1px;background:var(--divider);"></div>
            <div id="refuse-toggle-row">
                <button type="button" class="btn btn-outline-danger" onclick="showRefuse()">
                    <i class="fas fa-times"></i> Refuse This Request
                </button>
            </div>
            <div class="refuse-panel" id="refuse-panel" style="display:none;">
                <div class="refuse-panel-title"><i class="fas fa-times-circle"></i> Refuse This Request</div>
                <form action="{{ route('artist.bulk-orders.refuse', $bulk->id) }}" method="POST" class="form-grid">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Reason <span class="req">*</span></label>
                        <textarea name="seller_reason" class="form-textarea" style="min-height:90px;"
                                  placeholder="Explain why you cannot fulfil this bulk order..."
                                  maxlength="1000">{{ old('seller_reason') }}</textarea>
                        @error('seller_reason')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="action-row">
                        <button type="submit" class="btn btn-danger" style="flex:1;"><i class="fas fa-times"></i> Send Refusal</button>
                        <button type="button" class="btn btn-secondary" style="flex:1;" onclick="hideRefuse()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ACCEPTED — awaiting payment --}}
    @if($bulk->isAccepted() && !$bulk->order_id)
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        You accepted this bulk order. Waiting for the buyer to complete payment.
    </div>
    @endif

    {{-- PAID — order now in order list --}}
    @if($bulk->isPaid() && $bulk->order_id)
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Order Status</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                The buyer has paid. This order is now in your order list.
            </div>
            <div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-box"></i> Order Status</span>
                    <span class="detail-val">{{ ucfirst(str_replace('_', ' ', $bulk->order->status)) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-calendar-alt"></i> Order Date</span>
                    <span class="detail-val">{{ $bulk->order->created_at->format('d M Y, g:i A') }}</span>
                </div>
            </div>
            <a href="{{ route('artist.orders') }}" class="btn btn-primary" style="justify-content:center;">
                <i class="fas fa-clipboard-list"></i> View in Order List
            </a>
        </div>
    </div>
    @endif

    {{-- REFUSED --}}
    @if($bulk->isRefused())
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Your Response Sent</div>
        </div>
        <div class="sp-card-body">
            <div class="reason-box">
                <div class="reason-box-title"><i class="fas fa-times-circle"></i> Your Reason</div>
                {{ $bulk->seller_reason }}
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
    function showRefuse() {
        document.getElementById('refuse-panel').style.display = 'flex';
        document.getElementById('refuse-toggle-row').style.display = 'none';
    }
    function hideRefuse() {
        document.getElementById('refuse-panel').style.display = 'none';
        document.getElementById('refuse-toggle-row').style.display = 'block';
    }
</script>
@endsection