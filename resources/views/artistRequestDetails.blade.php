@extends('layouts.app')

@section('title', 'Custom Request — ' . $customOrder->title)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/requestDetails.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.custom-orders.index') }}">Custom Requests</a>
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

    {{-- ══ PAGE HEADER ══ --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">{{ $customOrder->title }}</div>
            <div class="page-subtitle">
                From <strong>{{ $customOrder->buyer->fullname ?? $customOrder->buyer->name }}</strong>
                &nbsp;·&nbsp; {{ $customOrder->created_at->format('d M Y') }}
            </div>
        </div>
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
                    <span class="detail-key"><i class="fas fa-tag"></i> Buyer's Offered Price</span>
                    <span class="detail-val price">RM {{ number_format($customOrder->buyer_price, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-{{ $customOrder->isDigital() ? 'file-image' : 'box' }}"></i> Product Type</span>
                    <span class="detail-val">
                        {{ $customOrder->isDigital() ? '🖥️ Digital' : '📦 Physical' }}
                        @if($customOrder->isDigital())
                            <span style="font-size:var(--fs-sm);color:var(--muted);font-weight:400;"> — no shipping required</span>
                        @else
                            <span style="font-size:var(--fs-sm);color:var(--muted);font-weight:400;"> — ship via courier</span>
                        @endif
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-user"></i> Buyer</span>
                    <span class="detail-val">{{ $customOrder->buyer->fullname ?? $customOrder->buyer->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key"><i class="fas fa-calendar-alt"></i> Submitted</span>
                    <span class="detail-val">{{ $customOrder->created_at->format('d M Y, g:i A') }}</span>
                </div>
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label class="form-label">Buyer's Description</label>
                <div class="description-box">{{ $customOrder->description }}</div>
            </div>

        </div>
    </div>

    {{-- ══ PENDING — accept or refuse ══ --}}
    @if($customOrder->isPending())
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Your Response</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-lg);">

            {{-- Accept --}}
            <div>
                <p style="font-size:var(--fs-sm);color:var(--muted);margin-bottom:var(--sp-sm);">
                    Accepting will notify the buyer to complete payment at
                    <strong style="color:var(--ink);">RM {{ number_format($customOrder->buyer_price, 2) }}</strong>.
                </p>
                <form action="{{ route('artist.custom-orders.accept', $customOrder->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" style="justify-content:center;width:100%;"
                            onclick="return confirm('Accept this custom order at RM {{ number_format($customOrder->buyer_price, 2) }}?')">
                        <i class="fas fa-check"></i> Accept Order
                    </button>
                </form>
            </div>

            <div style="height:1px;background:var(--divider);"></div>

            {{-- Refuse toggle button --}}
            <div id="refuse-toggle-row">
                <button type="button" class="btn btn-outline-danger" style="justify-content:center;width:100%;" onclick="showRefuse()">
                    <i class="fas fa-times"></i> Refuse This Request
                </button>
            </div>

            {{-- Refuse panel (hidden by default) --}}
            <div class="refuse-panel" id="refuse-panel" style="display:none;">
                <div class="refuse-panel-title">
                    <i class="fas fa-times-circle"></i> Refuse This Request
                </div>

                <form action="{{ route('artist.custom-orders.refuse', $customOrder->id) }}"
                      method="POST"
                      class="form-grid">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Reason <span class="req">*</span></label>
                        <textarea name="seller_reason"
                                  class="form-textarea"
                                  style="min-height:90px;"
                                  placeholder="Explain why you cannot accept this request..."
                                  maxlength="1000">{{ old('seller_reason') }}</textarea>
                        @error('seller_reason')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="counter-toggle">
                            <input type="checkbox" id="counter-chk" name="has_counter">
                            Suggest a different price instead
                        </label>
                    </div>

                    <div id="counter-wrap" class="form-group" style="display:none;">
                        <label class="form-label">Your Counter Price (RM)</label>
                        <div class="price-wrap">
                            <span class="price-prefix">RM</span>
                            <input type="number"
                                   name="counter_price"
                                   class="form-input"
                                   placeholder="0.00"
                                   min="1"
                                   step="0.01"
                                   value="{{ old('counter_price') }}">
                        </div>
                        <span class="form-hint">The buyer will be notified and can accept or decline.</span>
                        @error('counter_price')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="action-row">
                        <button type="submit" class="btn btn-danger" style="justify-content:center;flex:1;">
                            <i class="fas fa-times"></i> Send Refusal
                        </button>
                        <button type="button" class="btn btn-secondary" style="justify-content:center;flex:1;" onclick="hideRefuse()">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    @endif

    {{-- ══ ACCEPTED — waiting for buyer payment OR buyer already paid ══ --}}
    @if($customOrder->isAccepted())
        @if($customOrder->order_id && $customOrder->order)
        {{-- Buyer paid — show order status ── --}}
        <div class="sp-card">
            <div class="sp-card-header">
                <div class="sp-card-header-left"><div class="hline"></div> Order Status</div>
            </div>
            <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    The buyer has paid. The order is now in your order list.
                </div>
                <div>
                    <div class="detail-row">
                        <span class="detail-key"><i class="fas fa-box"></i> Order Status</span>
                        <span class="detail-val">{{ ucfirst(str_replace('_', ' ', $customOrder->order->status)) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-key"><i class="fas fa-calendar-alt"></i> Order Date</span>
                        <span class="detail-val">{{ $customOrder->order->created_at->format('d M Y, g:i A') }}</span>
                    </div>
                </div>
                <a href="{{ route('artist.orders') }}" class="btn btn-primary" style="justify-content:center;">
                    <i class="fas fa-clipboard-list"></i> View in Order List
                </a>
            </div>
        </div>
        @else
        {{-- Waiting for buyer to pay ── --}}
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            You accepted this request. Waiting for the buyer to complete payment.
        </div>
        @endif
    @endif

    {{-- ══ REFUSED — your response sent ══ --}}
    @if($customOrder->isRefused())
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Your Response Sent</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">
            <div class="reason-box">
                <div class="reason-box-title">
                    <i class="fas fa-times-circle"></i> Your Reason
                </div>
                {{ $customOrder->seller_reason }}
            </div>
            @if($customOrder->hasCounterPrice())
            <div class="counter-box">
                <div>
                    <div class="counter-box-label">Counter Price Offered</div>
                    <div class="counter-box-desc">Waiting for buyer's response.</div>
                </div>
                <div class="counter-box-price">RM {{ number_format($customOrder->counter_price, 2) }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ══ COMPLETED ══ --}}
    @if($customOrder->isCompleted())
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        Payment received. This order has been created successfully.
    </div>
    @endif

    {{-- ══ CANCELLED ══ --}}
    @if($customOrder->isCancelled())
    <div class="alert alert-danger">
        <i class="fas fa-times-circle"></i>
        The buyer declined your counter offer. This request has been cancelled.
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

    const counterChk  = document.getElementById('counter-chk');
    const counterWrap = document.getElementById('counter-wrap');
    if (counterChk) {
        counterChk.addEventListener('change', () => {
            counterWrap.style.display = counterChk.checked ? 'flex' : 'none';
        });
    }
</script>
@endsection