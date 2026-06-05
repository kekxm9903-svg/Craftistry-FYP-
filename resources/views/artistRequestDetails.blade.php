@extends('layouts.app')

@section('title', 'Custom Request — ' . $customOrder->title)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/requestDetails.css') }}">
<style>
    /* ── Refuse Confirm Modal (matches logout modal style) ── */
    #refuseConfirmModal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        align-items: center;
        justify-content: center;
    }
    #refuseConfirmModal.open { display: flex; }
    #refuseConfirmBackdrop {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,.48);
        backdrop-filter: blur(3px);
    }
    #refuseConfirmBox {
        position: relative;
        background: #fff;
        border-radius: 16px;
        padding: 36px 32px 28px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 24px 64px rgba(102,126,234,.22), 0 4px 16px rgba(0,0,0,.08);
        text-align: center;
        z-index: 1;
        animation: refuseModalIn .22s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes refuseModalIn {
        from { opacity: 0; transform: scale(.88) translateY(16px); }
        to   { opacity: 1; transform: scale(1)  translateY(0); }
    }
    .refuse-modal-icon {
        width: 60px; height: 60px;
        background: linear-gradient(135deg, #fff5f5, #fed7d7);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 18px;
        border: 2px solid #fca5a5;
        box-shadow: 0 4px 12px rgba(239,68,68,.15);
    }
    .refuse-modal-icon i { color: #ef4444; font-size: 1.45rem; }
    .refuse-modal-title  { font-size: 1.15rem; font-weight: 800; color: #1a202c; margin-bottom: 8px; }
    .refuse-modal-msg    { font-size: 0.84rem; color: #718096; line-height: 1.65; margin-bottom: 28px; }
    .refuse-modal-btns   { display: flex; gap: 10px; }
    .refuse-btn-cancel {
        flex: 1; padding: 12px; border-radius: 8px;
        border: 1.5px solid #e2e8f0; background: #fff;
        color: #4a5568; font-size: 0.88rem; font-weight: 600;
        cursor: pointer; font-family: 'Inter', sans-serif; transition: all .15s;
    }
    .refuse-btn-cancel:hover { background: #f7fafc; border-color: #cbd5e0; }
    .refuse-btn-confirm {
        flex: 1; padding: 12px; border-radius: 8px; border: none;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff; font-size: 0.88rem; font-weight: 700;
        cursor: pointer; font-family: 'Inter', sans-serif; transition: all .15s;
        box-shadow: 0 4px 14px rgba(239,68,68,.35);
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .refuse-btn-confirm:hover { opacity: .88; transform: translateY(-1px); }
</style>
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

            @if($customOrder->reference_image)
            <div class="ref-image">
                <img src="{{ asset('storage/' . $customOrder->reference_image) }}" alt="Reference image">
            </div>
            @endif

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
                    <button type="submit" class="btn btn-success"
                            style="width:100%;justify-content:center;"
                            onclick="return confirm('Accept this custom order at RM {{ number_format($customOrder->buyer_price, 2) }}?')">
                        <i class="fas fa-check"></i> Accept Order
                    </button>
                </form>
            </div>

            <div style="height:1px;background:var(--divider);"></div>

            {{-- Refuse toggle --}}
            <div id="refuse-toggle-row">
                <p style="font-size:var(--fs-sm);color:var(--muted);margin-bottom:var(--sp-sm);">
                    Cannot fulfil this request? Refuse it and optionally suggest a different price.
                </p>
                <button type="button"
                        class="btn btn-outline-danger"
                        style="width:100%;justify-content:center;"
                        onclick="showRefuse()">
                    <i class="fas fa-times"></i> Refuse This Request
                </button>
            </div>

            {{-- Refuse panel --}}
            <div id="refuse-panel" style="display:none;">

                <div class="refuse-panel-title" style="margin-bottom:var(--sp-md);">
                    <i class="fas fa-times-circle"></i> Refuse This Request
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" style="margin-bottom:var(--sp-md);">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form id="refuseForm"
                      action="{{ route('artist.custom-orders.refuse', $customOrder->id) }}"
                      method="POST"
                      style="display:flex;flex-direction:column;gap:var(--sp-md);">
                    @csrf

                    {{-- Reason --}}
                    <div class="form-group">
                        <label class="form-label">
                            Reason for Refusal
                            <span style="font-size:0.78rem;font-weight:400;color:var(--muted);margin-left:4px;">
                                (Optional if you suggest a counter price)
                            </span>
                        </label>
                        <textarea name="seller_reason"
                                  id="refuse-reason"
                                  class="form-textarea"
                                  rows="4"
                                  placeholder="Explain why you cannot accept this request. The buyer will be notified."
                                  maxlength="1000">{{ old('seller_reason') }}</textarea>
                        @error('seller_reason')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Counter price --}}
                    <div class="form-group">
                        <label class="form-label">
                            Counter Price (RM)
                            <span style="font-size:0.78rem;font-weight:400;color:var(--muted);margin-left:4px;">
                                (Optional if you provide a reason)
                            </span>
                        </label>
                        <div class="price-wrap">
                            <span class="price-prefix">RM</span>
                            <input type="number"
                                   name="counter_price"
                                   id="counter-price-input"
                                   class="form-input"
                                   placeholder="0.00"
                                   min="1"
                                   step="0.01"
                                   value="{{ old('counter_price') }}">
                        </div>
                        <span class="form-hint">
                            Suggest a different price — the buyer can accept or decline your counter offer.
                        </span>
                        @error('counter_price')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Buttons --}}
                    <div style="display:flex;gap:12px;margin-top:4px;">
                        <button type="button"
                                class="btn btn-secondary"
                                style="flex:1;justify-content:center;"
                                onclick="hideRefuse()">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </button>
                        <button type="button"
                                class="btn btn-danger"
                                style="flex:1;justify-content:center;"
                                onclick="openRefuseConfirm()">
                            <i class="fas fa-paper-plane"></i> Send Refusal
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
    @endif

    {{-- ══ ACCEPTED ══ --}}
    @if($customOrder->isAccepted())
        @if($customOrder->order_id && $customOrder->order)
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
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            You accepted this request. Waiting for the buyer to complete payment.
        </div>
        @endif
    @endif

    {{-- ══ REFUSED ══ --}}
    @if($customOrder->isRefused())
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left"><div class="hline"></div> Your Response Sent</div>
        </div>
        <div class="sp-card-body" style="display:flex;flex-direction:column;gap:var(--sp-md);">
            @if($customOrder->seller_reason)
            <div class="reason-box">
                <div class="reason-box-title">
                    <i class="fas fa-times-circle"></i> Your Reason
                </div>
                {{ $customOrder->seller_reason }}
            </div>
            @endif
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

{{-- ══ REFUSE CONFIRM MODAL ══ --}}
<div id="refuseConfirmModal" role="dialog" aria-modal="true" aria-labelledby="refuseModalTitle">
    <div id="refuseConfirmBackdrop" onclick="closeRefuseConfirm()"></div>
    <div id="refuseConfirmBox">
        <div class="refuse-modal-icon">
            <i class="fas fa-times"></i>
        </div>
        <div class="refuse-modal-title" id="refuseModalTitle">Send Refusal?</div>
        <div class="refuse-modal-msg" id="refuseModalMsg">
            This will notify the buyer of your decision.
        </div>
        <div class="refuse-modal-btns">
            <button class="refuse-btn-cancel" onclick="closeRefuseConfirm()">
                Go Back
            </button>
            <button class="refuse-btn-confirm" onclick="submitRefuse()">
                <i class="fas fa-paper-plane"></i> Send
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ── Refuse panel show/hide ──
function showRefuse() {
    document.getElementById('refuse-panel').style.display      = 'block';
    document.getElementById('refuse-toggle-row').style.display = 'none';
}

function hideRefuse() {
    document.getElementById('refuse-panel').style.display      = 'none';
    document.getElementById('refuse-toggle-row').style.display = 'block';
    document.getElementById('refuse-reason').value             = '';
    document.getElementById('counter-price-input').value       = '';
}

// ── Refuse confirm modal ──
function openRefuseConfirm() {
    const reason = document.getElementById('refuse-reason').value.trim();
    const price  = document.getElementById('counter-price-input').value.trim();

    // Validate at least one filled
    if (!reason && !price) {
        alert('Please provide a refusal reason or a counter price — at least one is required.');
        return;
    }

    // Validate price if provided
    if (price && parseFloat(price) < 1) {
        alert('Counter price must be at least RM 1.00.');
        return;
    }

    // Build confirm message
    let msg;
    if (reason && price) {
        msg = `Your refusal reason and a counter offer of <strong>RM ${parseFloat(price).toFixed(2)}</strong> will be sent to the buyer.`;
    } else if (price) {
        msg = `A counter offer of <strong>RM ${parseFloat(price).toFixed(2)}</strong> will be sent to the buyer.`;
    } else {
        msg = `Your refusal reason will be sent to the buyer. This action cannot be undone.`;
    }

    document.getElementById('refuseModalMsg').innerHTML = msg;
    document.getElementById('refuseConfirmModal').classList.add('open');
}

function closeRefuseConfirm() {
    document.getElementById('refuseConfirmModal').classList.remove('open');
}

function submitRefuse() {
    closeRefuseConfirm();
    document.getElementById('refuseForm').submit();
}

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRefuseConfirm();
});

// Auto-open refuse panel if server validation failed
@if($errors->any())
    showRefuse();
@endif
</script>
@endsection