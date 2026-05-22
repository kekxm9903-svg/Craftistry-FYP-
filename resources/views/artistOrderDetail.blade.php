@extends('layouts.app')

@section('title', 'Order #' . str_pad($order->id, 5, '0', STR_PAD_LEFT))

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ═══════════════════════════════════════════
   ARTIST ORDER DETAIL — matches artistOrders
═══════════════════════════════════════════ */
:root {
    --primary:   #667eea;
    --primary-2: #764ba2;
    --lavender:  #ede9fe;
    --ink:       #1a1a2e;
    --muted:     #6b6b8a;
    --border:    #e0e0ee;
    --divider:   #f0f0f5;
    --bg:        #f0f0f5;
    --white:     #ffffff;
    --fs-sm:     12px;
    --fs-base:   13px;
    --fs-md:     15px;
    --fs-lg:     18px;
    --sp-xs:     6px;
    --sp-sm:     10px;
    --sp-md:     16px;
    --sp-lg:     20px;
    --sp-xl:     24px;
    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 14px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    background: var(--bg);
    color: var(--ink);
    -webkit-font-smoothing: antialiased;
    line-height: 1.5;
}
.bi { font-size: inherit; line-height: 1; vertical-align: -0.125em; }

/* ── Breadcrumb ── */
.bc-bar { background: var(--white); border-bottom: 1px solid var(--border); padding: var(--sp-xs) 0; font-size: var(--fs-sm); }
.bc-inner { max-width: 1000px; margin: 0 auto; padding: 0 var(--sp-lg); display: flex; align-items: center; gap: var(--sp-xs); }
.bc-inner a { color: var(--muted); text-decoration: none; transition: color .15s; }
.bc-inner a:hover { color: var(--primary); }
.bc-inner .sep { color: #ccc; }
.bc-inner .cur { color: var(--ink); font-weight: 500; }

/* ── Main wrapper ── */
.od-main {
    max-width: 1000px;
    margin: 0 auto;
    padding: var(--sp-md) var(--sp-lg) 60px;
    display: flex;
    flex-direction: column;
    gap: var(--sp-md);
}

/* ── Back btn ── */
.back-btn {
    display: inline-flex; align-items: center; gap: var(--sp-xs);
    color: var(--muted); text-decoration: none;
    font-size: var(--fs-base); font-weight: 600; transition: color .15s;
    align-self: flex-start;
}
.back-btn:hover { color: var(--primary); }

/* ── Page title row ── */
.od-title-row {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: var(--sp-sm);
}
.od-title {
    display: flex; align-items: center; gap: var(--sp-sm);
}
.od-title h1 { font-size: var(--fs-lg); font-weight: 800; color: var(--ink); }
.od-title .order-num { font-size: var(--fs-md); color: var(--primary); font-weight: 700; }

/* ── Status badge (reuse from list) ── */
.status-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px var(--sp-sm); border-radius: 20px;
    font-size: var(--fs-sm); font-weight: 700; white-space: nowrap;
}
.status-yellow { background: #fff3e0; color: #e65100; }
.status-blue   { background: #e3f2fd; color: #1565c0; }
.status-orange { background: #fff7ed; color: #c05621; }
.status-purple { background: var(--lavender); color: #4527a0; }
.status-green  { background: #e8f5e9; color: #2e7d32; }
.status-red    { background: #ffebee; color: #c62828; }
.status-gray   { background: var(--divider); color: var(--muted); }

/* ── Cards ── */
.od-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.od-card-header {
    display: flex; align-items: center; gap: var(--sp-sm);
    padding: var(--sp-sm) var(--sp-md);
    background: var(--divider);
    border-bottom: 1px solid var(--border);
    font-size: var(--fs-sm); font-weight: 700; color: var(--primary-2);
}
.od-card-header .bi { font-size: 14px; }
.od-card-body { padding: var(--sp-md); }

/* ── Two-column grid ── */
.od-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--sp-md);
}
@media (max-width: 680px) { .od-grid { grid-template-columns: 1fr; } }

/* ── Info rows inside cards ── */
.info-row {
    display: flex; align-items: flex-start;
    gap: var(--sp-sm);
    padding: var(--sp-xs) 0;
    border-bottom: 1px solid var(--divider);
    font-size: var(--fs-base);
}
.info-row:last-child { border-bottom: none; }
.info-label {
    width: 130px; flex-shrink: 0;
    font-size: var(--fs-sm); font-weight: 600; color: var(--muted);
    text-transform: uppercase; letter-spacing: .04em;
    padding-top: 1px;
}
.info-val { flex: 1; color: var(--ink); font-weight: 500; word-break: break-word; }
.info-val.mono { font-family: 'Courier New', monospace; font-size: var(--fs-sm); background: var(--divider); padding: 2px 8px; border-radius: var(--radius-sm); display: inline-block; }

/* ── Buyer card ── */
.buyer-block {
    display: flex; align-items: center; gap: var(--sp-md);
    padding: var(--sp-md);
}
.buyer-avatar {
    width: 52px; height: 52px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
    background: var(--lavender); display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--border);
}
.buyer-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.avatar-placeholder { font-size: var(--fs-lg); font-weight: 800; color: var(--primary); }
.buyer-details { flex: 1; }
.buyer-name-lg { font-size: var(--fs-md); font-weight: 800; color: var(--ink); }
.buyer-meta { display: flex; flex-direction: column; gap: 3px; margin-top: 4px; }
.buyer-meta-item {
    display: flex; align-items: center; gap: 5px;
    font-size: var(--fs-sm); color: var(--muted);
}
.buyer-meta-item .bi { color: var(--primary); font-size: 11px; }

/* ── Address block ── */
.address-block {
    background: var(--lavender);
    border: 1px solid #ddd8f8;
    border-radius: var(--radius-md);
    padding: var(--sp-md);
    display: flex; gap: var(--sp-sm);
    align-items: flex-start;
}
.address-icon {
    width: 34px; height: 34px; border-radius: 50%;
    background: var(--white); display: flex; align-items: center; justify-content: center;
    color: var(--primary); font-size: 15px; flex-shrink: 0;
    border: 1.5px solid #ddd8f8;
}
.address-lines { flex: 1; }
.address-name { font-size: var(--fs-base); font-weight: 700; color: var(--ink); margin-bottom: 3px; }
.address-line { font-size: var(--fs-sm); color: var(--muted); line-height: 1.6; }

/* ── Products ── */
.product-row {
    display: flex; align-items: flex-start; gap: var(--sp-md);
    padding: var(--sp-md);
    border-bottom: 1px solid var(--divider);
}
.product-row:last-child { border-bottom: none; }
.product-row:hover { background: #fafafe; }
.product-thumb {
    width: 80px; height: 80px; border-radius: var(--radius-md); overflow: hidden; flex-shrink: 0;
    background: var(--lavender); display: flex; align-items: center; justify-content: center;
    color: var(--primary); font-size: 1.6rem; border: 1px solid var(--border);
}
.product-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.product-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 5px; }
.product-name { font-size: var(--fs-base); font-weight: 700; color: var(--ink); line-height: 1.3; }
.product-tags { display: flex; flex-wrap: wrap; gap: 5px; }
.ptag {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px;
}
.ptag-type    { background: var(--lavender); color: var(--primary-2); }
.ptag-digital { background: #eff6ff; color: #1d4ed8; }
.ptag-custom  { background: #fdf4ff; color: #7e22ce; }
.ptag-mat     { background: #f0fdf4; color: #166534; }
.ptag-size    { background: #fff7ed; color: #c2410c; }
.product-notes-box {
    background: var(--divider); border-radius: var(--radius-sm);
    padding: 6px var(--sp-sm); font-size: var(--fs-sm); color: var(--muted);
    display: flex; gap: 5px; align-items: flex-start; line-height: 1.5;
}
.product-notes-box .bi { flex-shrink: 0; margin-top: 1px; color: var(--primary); }
.product-price-col {
    flex-shrink: 0; display: flex; flex-direction: column;
    align-items: flex-end; gap: 4px; min-width: 90px;
}
.product-qty {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: var(--fs-sm); color: var(--muted); font-weight: 600;
    background: var(--divider); padding: 2px 8px; border-radius: 20px;
}
.product-unit-price { font-size: var(--fs-base); font-weight: 700; color: var(--ink); }
.per-unit { font-size: 11px; color: var(--muted); font-weight: 400; }
.product-subtotal {
    font-size: var(--fs-sm); font-weight: 800;
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}

/* ── Order summary totals ── */
.totals-block { display: flex; flex-direction: column; gap: 0; }
.total-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: var(--sp-xs) 0;
    font-size: var(--fs-base); color: var(--muted);
    border-bottom: 1px solid var(--divider);
}
.total-row:last-child { border-bottom: none; }
.total-row.grand {
    font-size: var(--fs-md); font-weight: 800; color: var(--ink);
    padding-top: var(--sp-sm); margin-top: 2px;
}
.grand-amount {
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    font-size: var(--fs-lg); font-weight: 800;
}

/* ── Tracking timeline ── */
.track-steps {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: var(--sp-md) var(--sp-lg);
    gap: 0;
}
.track-step {
    display: flex; flex-direction: column; align-items: center;
    gap: 6px; flex: 1; position: relative;
    font-size: var(--fs-sm); color: var(--muted); text-align: center;
}
.track-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 13px; left: 50%; right: -50%;
    height: 2px; background: var(--border); z-index: 0;
}
.track-step.step-done:not(:last-child)::after { background: linear-gradient(90deg, var(--primary), var(--primary-2)); }
.track-dot {
    width: 26px; height: 26px; border-radius: 50%;
    background: var(--divider); border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    z-index: 1; flex-shrink: 0; font-size: 10px; color: var(--muted);
    transition: all .2s;
}
.track-step.step-done .track-dot {
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    border-color: transparent; color: #fff;
}
.track-step.step-current .track-dot {
    box-shadow: 0 0 0 4px rgba(102,126,234,.2);
}
.track-label { font-size: 11px; font-weight: 600; color: var(--muted); }
.track-step.step-done .track-label { color: var(--primary); }
.track-step.step-current .track-label { color: var(--primary-2); font-weight: 800; }
.tracking-courier-row {
    display: flex; align-items: center; gap: var(--sp-sm);
    padding: var(--sp-sm) var(--sp-md);
    background: var(--lavender); border-bottom: 1px solid #ddd8f8;
    font-size: var(--fs-sm);
}
.tracking-courier-row .bi { color: var(--primary); }

/* ── Refund banner (reuse from list) ── */
.refund-banner { border-radius: var(--radius-md); overflow: hidden; border: 1.5px solid #fed7aa; }
.refund-banner-header {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
    gap: 8px; padding: 10px var(--sp-md);
    background: linear-gradient(135deg, #fff8f0, #fef3c7);
}
.refund-banner.refunded { border-color: #bbf7d0; }
.refund-banner.refunded .refund-banner-header { background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
.refund-banner.rejected  { border-color: #fecaca; }
.refund-banner.rejected  .refund-banner-header { background: linear-gradient(135deg, #fef2f2, #fee2e2); }
.refund-banner-left { display: flex; align-items: center; gap: 10px; }
.refund-banner-icon {
    width: 36px; height: 36px; border-radius: 50%;
    background: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 16px; color: #d97706; flex-shrink: 0;
    border: 1.5px solid #fcd34d; box-shadow: 0 2px 6px rgba(252,211,77,.25);
}
.refund-banner.refunded .refund-banner-icon { color: #166534; border-color: #bbf7d0; }
.refund-banner.rejected  .refund-banner-icon { color: #991b1b; border-color: #fecaca; }
.refund-banner-title { font-size: 13px; font-weight: 800; color: #92400e; }
.refund-banner.refunded .refund-banner-title { color: #166534; }
.refund-banner.rejected  .refund-banner-title { color: #991b1b; }
.refund-banner-sub { font-size: 11px; color: #b45309; margin-top: 1px; }
.refund-banner.refunded .refund-banner-sub { color: #15803d; }
.refund-banner.rejected  .refund-banner-sub { color: #dc2626; }
.refund-banner-body {
    padding: var(--sp-sm) var(--sp-md); background: #fff;
    display: flex; align-items: flex-start; gap: var(--sp-md); flex-wrap: wrap;
}
.refund-reason-box { flex: 1; min-width: 180px; background: #fff8f0; border: 1px solid #fed7aa; border-radius: var(--radius-sm); padding: 8px 12px; }
.refund-reason-label { font-size: 11px; color: #b45309; font-weight: 700; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
.refund-reason-text  { font-size: 13px; color: #374151; font-style: italic; line-height: 1.5; }
.refund-banner-actions { display: flex; flex-direction: column; gap: 7px; flex-shrink: 0; }
.refund-action-row { display: flex; gap: 7px; }
.rp-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 5px;
    padding: 8px 16px; border-radius: 7px; font-size: 12px; font-weight: 700;
    border: none; cursor: pointer; font-family: inherit; transition: opacity .15s; white-space: nowrap;
}
.rp-btn:hover { opacity: .85; }
.rp-btn.approve { background: #22c55e; color: #fff; box-shadow: 0 2px 8px rgba(34,197,94,.3); }
.rp-btn.reject  { background: #ef4444; color: #fff; box-shadow: 0 2px 8px rgba(239,68,68,.25); }
.rp-btn.cancel  { background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
.rp-btn.confirm { background: #ef4444; color: #fff; }
.rp-reject-form { display: none; width: 100%; }
.rp-reject-textarea {
    width: 100%; border: 1.5px solid #fca5a5; border-radius: 7px;
    padding: 8px 10px; font-size: 12px; font-family: inherit;
    resize: vertical; outline: none; box-sizing: border-box;
}
.rp-reject-textarea:focus { border-color: #ef4444; }
.rp-reject-actions { display: flex; gap: 7px; margin-top: 7px; justify-content: flex-end; }

/* ── Action footer ── */
.od-actions {
    display: flex; align-items: center; gap: var(--sp-sm);
    flex-wrap: wrap;
    padding: var(--sp-md);
    background: var(--divider);
    border-top: 1px solid var(--border);
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}
.btn-accept {
    display: inline-flex; align-items: center; gap: var(--sp-xs);
    padding: 9px var(--sp-lg); border-radius: var(--radius-sm);
    background: linear-gradient(135deg, #48bb78, #38a169);
    color: #fff; font-weight: 700; font-size: var(--fs-base);
    border: none; cursor: pointer;
    box-shadow: 0 3px 10px rgba(72,187,120,.3);
    transition: opacity .15s; font-family: 'Inter', sans-serif;
}
.btn-accept:hover { opacity: .88; }
.btn-ship {
    display: inline-flex; align-items: center; gap: var(--sp-xs);
    padding: 9px var(--sp-lg); border-radius: var(--radius-sm);
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    color: #fff; font-weight: 700; font-size: var(--fs-base);
    border: none; cursor: pointer;
    box-shadow: 0 3px 10px rgba(102,126,234,.28);
    transition: opacity .15s; font-family: 'Inter', sans-serif;
}
.btn-ship:hover { opacity: .88; }
.btn-track {
    display: inline-flex; align-items: center; gap: var(--sp-xs);
    padding: 9px var(--sp-lg); border-radius: var(--radius-sm);
    background: var(--lavender); color: var(--primary-2);
    font-weight: 700; font-size: var(--fs-base);
    text-decoration: none; border: 1.5px solid #ddd8f8; transition: all .15s;
}
.btn-track:hover { background: linear-gradient(135deg, var(--primary), var(--primary-2)); color: #fff; border-color: transparent; }
.status-note {
    font-size: var(--fs-sm); color: var(--muted);
    display: flex; align-items: center; gap: var(--sp-xs);
}
.status-note--green { color: #2e7d32; }

/* ── Ship Modal ── */
.modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(3px); z-index: 200; }
.modal-backdrop.show { display: block; }
.ship-modal {
    position: fixed; bottom: -100%; left: 50%; transform: translateX(-50%);
    width: 100%; max-width: 460px;
    background: var(--white); border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    z-index: 201; transition: bottom .3s cubic-bezier(.34,1.2,.64,1); overflow: hidden;
}
.ship-modal.open { bottom: 0; }
.ship-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: var(--sp-lg) var(--sp-xl) var(--sp-md);
    border-bottom: 1px solid var(--divider);
}
.ship-modal-header h3 { font-size: var(--fs-md); font-weight: 700; color: var(--ink); display: flex; align-items: center; gap: var(--sp-xs); }
.ship-modal-header h3 .bi { color: var(--primary); }
.modal-close-btn {
    background: var(--divider); border: none; width: 28px; height: 28px;
    border-radius: var(--radius-sm); font-size: var(--fs-sm); color: var(--muted);
    cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s;
}
.modal-close-btn:hover { background: #fee2e2; color: #dc2626; }
.ship-modal-body { padding: var(--sp-lg) var(--sp-xl); display: flex; flex-direction: column; gap: var(--sp-md); }
.form-group { display: flex; flex-direction: column; gap: var(--sp-xs); }
.form-group label { font-size: var(--fs-base); font-weight: 600; color: var(--ink); }
.req { color: #dc2626; }
.form-group select,
.form-group input[type="text"] {
    padding: 9px var(--sp-md); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); font-size: var(--fs-base);
    font-family: 'Inter', sans-serif; color: var(--ink); background: var(--white);
    transition: border-color .15s; outline: none;
}
.form-group select:focus,
.form-group input[type="text"]:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(102,126,234,.1); }
.ship-modal-footer { display: flex; gap: var(--sp-sm); padding: var(--sp-md) var(--sp-xl) var(--sp-xl); border-top: 1px solid var(--divider); }
.ship-modal-footer .btn-ship,
.ship-modal-footer .btn-cancel { flex: 1; justify-content: center; }
.btn-cancel {
    padding: 9px var(--sp-lg); border-radius: var(--radius-sm);
    border: 1.5px solid var(--border); background: var(--white);
    color: var(--muted); font-weight: 600; font-size: var(--fs-base);
    cursor: pointer; transition: all .15s; font-family: 'Inter', sans-serif;
}
.btn-cancel:hover { border-color: var(--primary); color: var(--primary); }

/* ── Responsive ── */
@media (max-width: 768px) {
    .od-main { padding: var(--sp-sm) var(--sp-sm) 80px; }
    .od-title-row { flex-direction: column; align-items: flex-start; }
    .buyer-block { flex-direction: column; align-items: flex-start; }
    .product-thumb { width: 60px; height: 60px; }
    .product-price-col { min-width: 70px; }
    .track-steps { padding: var(--sp-sm); }
    .ship-modal { max-width: 100%; }
    .od-actions { flex-direction: column; }
    .od-actions button, .od-actions a { width: 100%; justify-content: center; }
}
</style>
@endsection

@section('content')

@php
    $refundStatus = $order->refund_status ?? 'none';

    $sellerLabel = match($order->status) {
        'pending_payment' => 'Awaiting Payment',
        'processing'      => 'New Order',
        'preparing'       => 'Preparing',
        'shipped'         => 'Shipped',
        'completed'       => 'Completed',
        'cancelled'       => 'Cancelled',
        default           => ucfirst($order->status),
    };
    $sellerClass = match($order->status) {
        'pending_payment' => 'yellow',
        'processing'      => 'blue',
        'preparing'       => 'orange',
        'shipped'         => 'purple',
        'completed'       => 'green',
        'cancelled'       => 'red',
        default           => 'gray',
    };

    $isAllDigital = $order->items && $order->items->count() > 0
        && $order->items->every(fn($i) => $i->artwork?->artwork_type === 'digital');

    $trackingSteps = [
        ['label' => 'Paid',     'icon' => 'bi-credit-card-fill',  'doneWhen' => ['processing','preparing','shipped','completed']],
        ['label' => 'Accepted', 'icon' => 'bi-check-circle-fill', 'doneWhen' => ['preparing','shipped','completed']],
        ['label' => 'Packing',  'icon' => 'bi-box-seam-fill',     'doneWhen' => ['preparing','shipped','completed']],
        ['label' => 'Shipped',  'icon' => 'bi-truck',             'doneWhen' => ['shipped','completed']],
        ['label' => 'Done',     'icon' => 'bi-bag-check-fill',    'doneWhen' => ['completed']],
    ];

    // Shipping address fields (adjust field names to match your schema)
    $buyer           = $order->user;
    $shippingName    = $buyer->fullname ?? $buyer->name ?? null;
    $shippingPhone   = $buyer->phone   ?? null;
    $shippingAddress = $buyer->address ?? null;
    $shippingCity    = $buyer->city    ?? null;
    $shippingState   = $buyer->state   ?? null;
    $shippingPostcode= $buyer->postcode ?? $buyer->postal_code ?? null;
    $shippingCountry = $buyer->country  ?? 'Malaysia';

    $subtotal = $order->items->sum(fn($i) => ($i->price ?? 0) * ($i->quantity ?? 1));
@endphp

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.orders') }}">Order List</a>
        <span class="sep">/</span>
        <span class="cur">Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
    </div>
</div>

<main class="od-main">

    <a href="{{ route('artist.orders') }}" class="back-btn">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>

    {{-- Title row --}}
    <div class="od-title-row">
        <div class="od-title">
            <h1>Order Detail</h1>
            <span class="order-num">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span class="status-badge status-{{ $sellerClass }}">
                @switch($order->status)
                    @case('pending_payment') <i class="bi bi-clock"></i>             @break
                    @case('processing')      <i class="bi bi-bell-fill"></i>         @break
                    @case('preparing')       <i class="bi bi-box-seam"></i>          @break
                    @case('shipped')         <i class="bi bi-truck"></i>             @break
                    @case('completed')       <i class="bi bi-check-circle-fill"></i> @break
                    @case('cancelled')       <i class="bi bi-x-circle-fill"></i>     @break
                    @default                 <i class="bi bi-circle"></i>
                @endswitch
                {{ $sellerLabel }}
            </span>
            @if($refundStatus === 'requested')
                <span class="status-badge" style="background:#fff3cd;color:#92400e;">
                    <i class="bi bi-arrow-return-left"></i> Refund Requested
                </span>
            @elseif($refundStatus === 'refunded')
                <span class="status-badge" style="background:#dcfce7;color:#166534;">
                    <i class="bi bi-check-circle-fill"></i> Refunded
                </span>
            @elseif($refundStatus === 'rejected')
                <span class="status-badge" style="background:#fee2e2;color:#991b1b;">
                    <i class="bi bi-x-circle-fill"></i> Refund Rejected
                </span>
            @endif
            <span style="font-size:var(--fs-sm);color:var(--muted);">
                <i class="bi bi-calendar3"></i>
                {{ $order->created_at->format('d M Y, h:i A') }}
            </span>
        </div>
    </div>

    {{-- ── ROW 1: Buyer + Order Info ── --}}
    <div class="od-grid">

        {{-- Buyer --}}
        <div class="od-card">
            <div class="od-card-header">
                <i class="bi bi-person-fill"></i> Buyer Information
            </div>
            <div class="buyer-block">
                <div class="buyer-avatar">
                    @if($order->user->profile_image)
                        <img src="{{ asset('storage/' . $order->user->profile_image) }}"
                             alt="{{ $order->user->fullname }}">
                    @else
                        <div class="avatar-placeholder">
                            {{ strtoupper(substr($order->user->fullname ?? '?', 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="buyer-details">
                    <div class="buyer-name-lg">{{ $order->user->fullname }}</div>
                    <div class="buyer-meta">
                        <div class="buyer-meta-item">
                            <i class="bi bi-envelope"></i>
                            {{ $order->user->email }}
                        </div>
                        @if($order->user->phone)
                        <div class="buyer-meta-item">
                            <i class="bi bi-telephone"></i>
                            {{ $order->user->phone }}
                        </div>
                        @endif
                        <div class="buyer-meta-item">
                            <i class="bi bi-calendar-check"></i>
                            Member since {{ $order->user->created_at->format('M Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Info --}}
        <div class="od-card">
            <div class="od-card-header">
                <i class="bi bi-receipt"></i> Order Information
            </div>
            <div class="od-card-body">
                <div class="info-row">
                    <span class="info-label">Order ID</span>
                    <span class="info-val mono">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date Placed</span>
                    <span class="info-val">{{ $order->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment</span>
                    <span class="info-val">
                        @if($order->payment_status === 'paid')
                            <span style="color:#2e7d32;font-weight:700;">
                                <i class="bi bi-check-circle-fill"></i> Paid
                            </span>
                        @else
                            <span style="color:#e65100;font-weight:700;">
                                <i class="bi bi-clock"></i> Unpaid
                            </span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Delivery</span>
                    <span class="info-val">
                        @if($isAllDigital)
                            <span style="color:#1d4ed8;font-weight:600;">
                                <i class="bi bi-cloud-download"></i> Digital Delivery
                            </span>
                        @else
                            <span style="color:var(--muted);">
                                <i class="bi bi-box-seam"></i> Physical Shipping
                            </span>
                        @endif
                    </span>
                </div>
                @if($order->stripe_session_id)
                <div class="info-row">
                    <span class="info-label">Stripe Ref</span>
                    <span class="info-val mono" style="font-size:11px;">{{ Str::limit($order->stripe_session_id, 28) }}</span>
                </div>
                @endif
                @if($order->notes)
                <div class="info-row">
                    <span class="info-label">Notes</span>
                    <span class="info-val" style="font-style:italic;color:var(--muted);">{{ $order->notes }}</span>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── ROW 2: Shipping Address (skip for all-digital) ── --}}
    @if(!$isAllDigital)
    <div class="od-card">
        <div class="od-card-header">
            <i class="bi bi-geo-alt-fill"></i> Shipping Address
        </div>
        <div class="od-card-body">
            @if($shippingAddress)
                <div class="address-block">
                    <div class="address-icon"><i class="bi bi-house-fill"></i></div>
                    <div class="address-lines">
                        @if($shippingName)
                            <div class="address-name">{{ $shippingName }}
                                @if($shippingPhone)
                                    <span style="font-size:var(--fs-sm);color:var(--muted);font-weight:400;margin-left:8px;">
                                        <i class="bi bi-telephone"></i> {{ $shippingPhone }}
                                    </span>
                                @endif
                            </div>
                        @endif
                        <div class="address-line">{{ $shippingAddress }}</div>
                        @if($shippingCity || $shippingPostcode)
                        <div class="address-line">
                            {{ implode(', ', array_filter([$shippingPostcode, $shippingCity])) }}
                        </div>
                        @endif
                        @if($shippingState || $shippingCountry)
                        <div class="address-line">
                            {{ implode(', ', array_filter([$shippingState, $shippingCountry])) }}
                        </div>
                        @endif
                    </div>
                </div>
            @else
                <div style="color:var(--muted);font-size:var(--fs-sm);padding:var(--sp-sm) 0;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-info-circle"></i>
                    No shipping address recorded for this order.
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Tracking (if applicable) ── --}}
    @if(in_array($order->status, ['shipped','completed']) && $order->tracking_number)
    <div class="od-card">
        <div class="od-card-header">
            <i class="bi bi-truck"></i> Tracking
        </div>
        <div class="tracking-courier-row">
            <i class="bi bi-truck"></i>
            <strong>{{ strtoupper($order->courier ?? 'Courier') }}</strong>
            &mdash;
            <span class="info-val mono" style="font-size:12px;">{{ $order->tracking_number }}</span>
            @if($order->getTrackingUrl())
                <a href="{{ $order->getTrackingUrl() }}" target="_blank"
                   style="margin-left:auto;display:inline-flex;align-items:center;gap:4px;
                          font-size:var(--fs-sm);font-weight:700;color:var(--primary);text-decoration:none;">
                    <i class="bi bi-box-arrow-up-right"></i> Track Parcel
                </a>
            @endif
        </div>
        <div class="track-steps">
            @foreach($trackingSteps as $i => $step)
                @php
                    $isDone    = in_array($order->status, $step['doneWhen']);
                    $isCurrent = $isDone && (
                        $i === count($trackingSteps) - 1
                        || !in_array($order->status, $trackingSteps[$i + 1]['doneWhen'] ?? [])
                    );
                @endphp
                <div class="track-step {{ $isDone ? 'step-done' : '' }} {{ $isCurrent ? 'step-current' : '' }}">
                    <div class="track-dot">
                        @if($isDone && !$isCurrent)
                            <i class="bi bi-check-lg" style="font-size:10px;"></i>
                        @elseif($isCurrent)
                            <i class="bi {{ $step['icon'] }}" style="font-size:10px;"></i>
                        @endif
                    </div>
                    <span class="track-label">{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Items ── --}}
    <div class="od-card">
        <div class="od-card-header">
            <i class="bi bi-bag-check-fill"></i> Items Ordered
            <span style="margin-left:auto;background:var(--white);color:var(--muted);padding:1px 8px;border-radius:10px;font-size:11px;font-weight:600;">
                {{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        @foreach($order->items as $item)
        @php
            $artwork       = $item->artwork;
            $imgPath       = $artwork?->image_path ?? $item->image_path ?? null;
            $isCustomItem  = is_null($item->artwork_sell_id);
            $isDigitalItem = $artwork?->artwork_type === 'digital';
        @endphp
        <div class="product-row">
            <div class="product-thumb">
                @if($imgPath)
                    <img src="{{ asset('storage/' . $imgPath) }}" alt="{{ $item->name }}">
                @elseif($isCustomItem)
                    <i class="bi bi-brush"></i>
                @else
                    <i class="bi bi-image"></i>
                @endif
            </div>
            <div class="product-info">
                <div class="product-name">{{ $item->name ?? 'Artwork' }}</div>
                <div class="product-tags">
                    @if($isCustomItem)
                        <span class="ptag ptag-custom"><i class="bi bi-brush"></i> Custom Order</span>
                    @else
                        @if($isDigitalItem)
                            <span class="ptag ptag-digital"><i class="bi bi-cloud-download"></i> Digital</span>
                        @elseif($artwork?->artwork_type)
                            <span class="ptag ptag-type"><i class="bi bi-tag"></i> {{ ucfirst($artwork->artwork_type) }}</span>
                        @endif
                        @if($artwork?->medium ?? $artwork?->material ?? null)
                            <span class="ptag ptag-mat"><i class="bi bi-palette"></i> {{ $artwork->medium ?? $artwork->material }}</span>
                        @endif
                        @if($artwork?->size ?? null)
                            <span class="ptag ptag-size"><i class="bi bi-aspect-ratio"></i> {{ $artwork->size }}</span>
                        @endif
                        @if($artwork?->style ?? null)
                            <span class="ptag ptag-type" style="background:#fdf4ff;color:#7e22ce;">
                                <i class="bi bi-stars"></i> {{ $artwork->style }}
                            </span>
                        @endif
                    @endif
                </div>
                @if($item->variant ?? null)
                    <div style="font-size:var(--fs-sm);color:var(--muted);">
                        <i class="bi bi-collection"></i> Variant: {{ $item->variant }}
                    </div>
                @endif
                @if($order->notes && $isCustomItem)
                    <div class="product-notes-box">
                        <i class="bi bi-chat-left-text"></i>
                        {{ $order->notes }}
                    </div>
                @endif
                @if($artwork?->description ?? null)
                    <div style="font-size:var(--fs-sm);color:var(--muted);line-height:1.5;">
                        {{ Str::limit($artwork->description, 100) }}
                    </div>
                @endif
            </div>
            <div class="product-price-col">
                <div class="product-qty"><i class="bi bi-layers"></i> × {{ $item->quantity ?? 1 }}</div>
                <div class="product-unit-price">
                    RM {{ number_format($item->price, 2) }}
                    @if(($item->quantity ?? 1) > 1)<span class="per-unit">/ unit</span>@endif
                </div>
                @if(($item->quantity ?? 1) > 1)
                <div class="product-subtotal">= RM {{ number_format($item->price * $item->quantity, 2) }}</div>
                @endif
            </div>
        </div>
        @endforeach

        {{-- Totals --}}
        <div style="padding:var(--sp-md);border-top:2px solid var(--divider);background:#fafafe;">
            <div class="totals-block">
                <div class="total-row">
                    <span>Subtotal ({{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }})</span>
                    <span>RM {{ number_format($subtotal, 2) }}</span>
                </div>
                @if(($order->shipping_fee ?? 0) > 0)
                <div class="total-row">
                    <span><i class="bi bi-truck"></i> Shipping Fee</span>
                    <span>RM {{ number_format($order->shipping_fee, 2) }}</span>
                </div>
                @endif
                <div class="total-row grand">
                    <span>Order Total</span>
                    <span class="grand-amount">RM {{ number_format($order->total ?? $order->price ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Refund Banner ── --}}
    @if($refundStatus === 'requested')
    <div class="refund-banner">
        <div class="refund-banner-header">
            <div class="refund-banner-left">
                <div class="refund-banner-icon"><i class="bi bi-arrow-return-left"></i></div>
                <div>
                    <div class="refund-banner-title">Buyer Requested a Refund</div>
                    <div class="refund-banner-sub">
                        <i class="bi bi-clock"></i>
                        {{ \Carbon\Carbon::parse($order->refund_requested_at)->format('d M Y, h:i A') }}
                        &nbsp;·&nbsp; RM {{ number_format($order->total ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="refund-banner-body">
            <div class="refund-reason-box">
                <div class="refund-reason-label"><i class="bi bi-chat-quote"></i> Reason</div>
                <div class="refund-reason-text">"{{ $order->refund_reason }}"</div>
            </div>
            <div class="refund-banner-actions">
                <div class="refund-action-row">
                    <form method="POST" action="{{ route('refund.approve.order', $order->id) }}"
                          onsubmit="return confirm('Approve refund? Stripe will process immediately.')">
                        @csrf
                        <button type="submit" class="rp-btn approve">
                            <i class="bi bi-check-circle-fill"></i> Approve Refund
                        </button>
                    </form>
                    <button type="button" class="rp-btn reject"
                            onclick="toggleRejectForm({{ $order->id }})">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                </div>
                <div class="rp-reject-form" id="rejectForm-{{ $order->id }}">
                    <form method="POST" action="{{ route('refund.reject.order', $order->id) }}">
                        @csrf
                        <textarea name="reject_reason" class="rp-reject-textarea" rows="2"
                            placeholder="Reason for rejection (required)..."
                            required minlength="5" maxlength="500"></textarea>
                        <div class="rp-reject-actions">
                            <button type="button" class="rp-btn cancel"
                                    onclick="toggleRejectForm({{ $order->id }})">Cancel</button>
                            <button type="submit" class="rp-btn confirm">Confirm Rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @elseif($refundStatus === 'refunded')
    <div class="refund-banner refunded">
        <div class="refund-banner-header">
            <div class="refund-banner-left">
                <div class="refund-banner-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="refund-banner-title">Refund Processed</div>
                    <div class="refund-banner-sub">
                        RM {{ number_format($order->refund_amount, 2) }} returned to buyer
                        · {{ \Carbon\Carbon::parse($order->refunded_at)->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @elseif($refundStatus === 'rejected')
    <div class="refund-banner rejected">
        <div class="refund-banner-header">
            <div class="refund-banner-left">
                <div class="refund-banner-icon"><i class="bi bi-x-circle-fill"></i></div>
                <div>
                    <div class="refund-banner-title">Refund Request Rejected</div>
                    @if($order->refund_reject_reason)
                    <div class="refund-banner-sub">{{ $order->refund_reject_reason }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Actions footer ── --}}
    <div class="od-card">
        <div class="od-actions">
            @if($order->status === 'pending_payment')
                <span class="status-note">
                    <i class="bi bi-clock"></i> Waiting for buyer to complete payment
                </span>

            @elseif($order->status === 'processing')
                <form action="{{ route('artist.orders.accept', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-accept">
                        <i class="bi bi-check-lg"></i> Accept Order
                    </button>
                </form>

            @elseif($order->status === 'preparing')
                @if($isAllDigital)
                    <form action="{{ route('artist.orders.ship', $order->id) }}" method="POST"
                          onsubmit="return confirm('Mark this digital order as delivered to buyer?')">
                        @csrf
                        <input type="hidden" name="courier" value="digital">
                        <input type="hidden" name="tracking_number" value="DIGITAL-DELIVERY">
                        <button type="submit" class="btn-ship">
                            <i class="bi bi-cloud-check-fill"></i> Mark as Delivered
                        </button>
                    </form>
                @else
                    <button class="btn-ship" onclick="openShipModal({{ $order->id }})">
                        <i class="bi bi-truck"></i> Mark as Shipped
                    </button>
                @endif

            @elseif($order->status === 'shipped')
                @if($order->getTrackingUrl())
                    <a href="{{ $order->getTrackingUrl() }}" target="_blank" class="btn-track">
                        <i class="bi bi-box-arrow-up-right"></i> Track Parcel
                    </a>
                @endif
                <span class="status-note">
                    <i class="bi bi-info-circle"></i> Waiting for buyer to confirm receipt
                </span>

            @elseif($order->status === 'completed')
                <span class="status-note status-note--green">
                    <i class="bi bi-check-circle-fill"></i> Order completed by buyer
                </span>

            @elseif($order->status === 'cancelled')
                <span class="status-note" style="color:#c62828;">
                    <i class="bi bi-x-circle-fill"></i> This order was cancelled
                </span>
            @endif
        </div>
    </div>

</main>

{{-- Ship Modal --}}
<div class="modal-backdrop" id="shipBackdrop" onclick="closeShipModal()"></div>
<div class="ship-modal" id="shipModal">
    <div class="ship-modal-header">
        <h3><i class="bi bi-truck"></i> Shipping Details</h3>
        <button onclick="closeShipModal()" class="modal-close-btn">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form id="shipForm" method="POST">
        @csrf
        <div class="ship-modal-body">
            <div class="form-group">
                <label>Courier <span class="req">*</span></label>
                <select name="courier" required>
                    <option value="">— Select Courier —</option>
                    <option value="poslaju">Pos Laju</option>
                    <option value="jnt">J&T Express</option>
                    <option value="dhl">DHL</option>
                    <option value="ninjavan">Ninja Van</option>
                    <option value="citylink">City-Link</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tracking Number <span class="req">*</span></label>
                <input type="text" name="tracking_number"
                       placeholder="e.g. EF123456789MY" required maxlength="100">
            </div>
        </div>
        <div class="ship-modal-footer">
            <button type="button" class="btn-cancel" onclick="closeShipModal()">Cancel</button>
            <button type="submit" class="btn-ship">
                <i class="bi bi-truck"></i> Confirm & Ship
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function openShipModal(orderId) {
    document.getElementById('shipForm').action = `/artist/orders/${orderId}/ship`;
    document.querySelector('[name="courier"]').value = '';
    document.querySelector('[name="tracking_number"]').value = '';
    document.getElementById('shipBackdrop').classList.add('show');
    document.getElementById('shipModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeShipModal() {
    document.getElementById('shipBackdrop').classList.remove('show');
    document.getElementById('shipModal').classList.remove('open');
    document.body.style.overflow = '';
}
function toggleRejectForm(orderId) {
    var f = document.getElementById('rejectForm-' + orderId);
    f.style.display = f.style.display === 'none' || f.style.display === '' ? 'block' : 'none';
}
</script>
@endsection