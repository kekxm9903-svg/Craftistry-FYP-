{{--
    Seller Refund Panel — include on seller order detail pages

    Normal order:
        @include('_refundPanel', [
            'refundable'   => $order,
            'approveRoute' => route('refund.approve.order', $order->id),
            'rejectRoute'  => route('refund.reject.order', $order->id),
        ])

    Bulk order:
        @include('_refundPanel', [
            'refundable'   => $bulk,
            'approveRoute' => route('refund.approve.bulk', $bulk->id),
            'rejectRoute'  => route('refund.reject.bulk', $bulk->id),
        ])

    Custom order:
        @include('_refundPanel', [
            'refundable'   => $customOrder,
            'approveRoute' => route('refund.approve.custom', $customOrder->id),
            'rejectRoute'  => route('refund.reject.custom', $customOrder->id),
        ])
--}}

@php $rs = $refundable->refund_status ?? 'none'; @endphp

@if($rs === 'requested')
<div class="refund-panel">
    <div class="refund-panel__header">
        <span class="refund-badge requested"><i class="bi bi-arrow-return-left"></i> Refund Requested</span>
        <span class="refund-panel__date">{{ \Carbon\Carbon::parse($refundable->refund_requested_at)->format('d M Y, h:i A') }}</span>
    </div>
    <div class="refund-panel__reason">
        <p class="refund-panel__reason-label">Buyer's reason:</p>
        <p class="refund-panel__reason-text">"{{ $refundable->refund_reason }}"</p>
    </div>
    <div class="refund-panel__actions">
        <form method="POST" action="{{ $approveRoute }}" onsubmit="return confirm('Approve this refund? Stripe will process it immediately.')">
            @csrf
            <button type="submit" class="refund-btn approve"><i class="bi bi-check-circle"></i> Approve Refund</button>
        </form>
        <button type="button" class="refund-btn reject" onclick="toggleRejectForm()"><i class="bi bi-x-circle"></i> Reject</button>
    </div>
    <div id="rejectForm" style="display:none; margin-top:14px;">
        <form method="POST" action="{{ $rejectRoute }}">
            @csrf
            <textarea name="reject_reason" class="refund-reject-textarea" rows="3"
                placeholder="Explain why you are rejecting this request..."
                required minlength="5" maxlength="500"></textarea>
            <div style="display:flex; gap:10px; margin-top:10px; justify-content:flex-end;">
                <button type="button" onclick="toggleRejectForm()" class="refund-btn cancel-small">Cancel</button>
                <button type="submit" class="refund-btn reject-confirm">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

@elseif($rs === 'refunded')
<div class="refund-panel refunded">
    <span class="refund-badge refunded"><i class="bi bi-check-circle-fill"></i> Refunded</span>
    <span class="refund-panel__meta">
        RM {{ number_format($refundable->refund_amount, 2) }} •
        {{ \Carbon\Carbon::parse($refundable->refunded_at)->format('d M Y') }}
    </span>
</div>

@elseif($rs === 'rejected')
<div class="refund-panel rejected">
    <span class="refund-badge rejected"><i class="bi bi-x-circle-fill"></i> Refund Rejected</span>
    @if($refundable->refund_reject_reason)
        <p class="refund-panel__reason-text" style="margin-top:8px;">{{ $refundable->refund_reject_reason }}</p>
    @endif
</div>
@endif

<style>
.refund-panel {
    background: #fff8f0; border: 1.5px solid #fed7aa;
    border-radius: 12px; padding: 18px 20px; margin: 18px 0;
}
.refund-panel.refunded { background: #f0fdf4; border-color: #bbf7d0; }
.refund-panel.rejected  { background: #fef2f2; border-color: #fecaca; }
.refund-panel__header   { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px; }
.refund-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
.refund-badge.requested { background: #fff3cd; color: #92400e; }
.refund-badge.refunded  { background: #dcfce7; color: #166534; }
.refund-badge.rejected  { background: #fee2e2; color: #991b1b; }
.refund-panel__date     { font-size: 12px; color: #9ca3af; }
.refund-panel__reason-label { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
.refund-panel__reason-text  { font-size: 14px; color: #374151; font-style: italic; line-height: 1.5; }
.refund-panel__meta     { font-size: 13px; color: #6b7280; margin-left: 10px; }
.refund-panel__actions  { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-top: 12px; }
.refund-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: opacity .15s; font-family: inherit; }
.refund-btn:hover        { opacity: .85; }
.refund-btn.approve      { background: #22c55e; color: #fff; }
.refund-btn.reject       { background: #ef4444; color: #fff; }
.refund-btn.cancel-small { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
.refund-btn.reject-confirm { background: #ef4444; color: #fff; }
.refund-reject-textarea  { width: 100%; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 10px 12px; font-size: 13px; font-family: inherit; resize: vertical; outline: none; }
.refund-reject-textarea:focus { border-color: #ef4444; }
</style>

<script>
function toggleRejectForm() {
    var f = document.getElementById('rejectForm');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
</script>