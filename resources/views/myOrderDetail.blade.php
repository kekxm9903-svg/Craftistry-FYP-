@extends('layouts.app')

@section('title', 'Order #' . str_pad($order->id, 5, '0', STR_PAD_LEFT))

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
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
body { font-family: 'Inter', sans-serif; font-size: var(--fs-base); background: var(--bg); color: var(--ink); -webkit-font-smoothing: antialiased; line-height: 1.5; }
.bi { font-size: inherit; line-height: 1; vertical-align: -0.125em; }

.bc-bar { background: var(--white); border-bottom: 1px solid var(--border); padding: var(--sp-xs) 0; font-size: var(--fs-sm); }
.bc-inner { max-width: 1000px; margin: 0 auto; padding: 0 var(--sp-lg); display: flex; align-items: center; gap: var(--sp-xs); }
.bc-inner a { color: var(--muted); text-decoration: none; transition: color .15s; }
.bc-inner a:hover { color: var(--primary); }
.bc-inner .sep { color: #ccc; }
.bc-inner .cur { color: var(--ink); font-weight: 500; }

.od-main { max-width: 1000px; margin: 0 auto; padding: var(--sp-md) var(--sp-lg) 60px; display: flex; flex-direction: column; gap: var(--sp-md); }

.back-btn { display: inline-flex; align-items: center; gap: var(--sp-xs); color: var(--muted); text-decoration: none; font-size: var(--fs-base); font-weight: 600; transition: color .15s; align-self: flex-start; }
.back-btn:hover { color: var(--primary); }

.od-title-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--sp-sm); }
.od-title { display: flex; align-items: center; gap: var(--sp-sm); }
.od-title h1 { font-size: var(--fs-lg); font-weight: 800; color: var(--ink); }
.od-title .order-num { font-size: var(--fs-md); color: var(--primary); font-weight: 700; }

.status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px var(--sp-sm); border-radius: 20px; font-size: var(--fs-sm); font-weight: 700; white-space: nowrap; }
.status-yellow { background: #fff3e0; color: #e65100; }
.status-blue   { background: #e3f2fd; color: #1565c0; }
.status-orange { background: #fff7ed; color: #c05621; }
.status-purple { background: var(--lavender); color: #4527a0; }
.status-green  { background: #e8f5e9; color: #2e7d32; }
.status-red    { background: #ffebee; color: #c62828; }
.status-gray   { background: var(--divider); color: var(--muted); }

.od-card { background: var(--white); border-radius: var(--radius-lg); border: 1px solid var(--border); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.od-card-header { display: flex; align-items: center; gap: var(--sp-sm); padding: var(--sp-sm) var(--sp-md); background: var(--divider); border-bottom: 1px solid var(--border); font-size: var(--fs-sm); font-weight: 700; color: var(--primary-2); }
.od-card-header .bi { font-size: 14px; }
.od-card-body { padding: var(--sp-md); }

.od-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-md); }
@media (max-width: 680px) { .od-grid { grid-template-columns: 1fr; } }

.info-row { display: flex; align-items: flex-start; gap: var(--sp-sm); padding: var(--sp-xs) 0; border-bottom: 1px solid var(--divider); font-size: var(--fs-base); }
.info-row:last-child { border-bottom: none; }
.info-label { width: 130px; flex-shrink: 0; font-size: var(--fs-sm); font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; padding-top: 1px; }
.info-val { flex: 1; color: var(--ink); font-weight: 500; word-break: break-word; }
.info-val.mono { font-family: 'Courier New', monospace; font-size: var(--fs-sm); background: var(--divider); padding: 2px 8px; border-radius: var(--radius-sm); display: inline-block; }

.artist-block { display: flex; align-items: center; gap: var(--sp-md); padding: var(--sp-md); }
.artist-avatar { width: 52px; height: 52px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: var(--lavender); display: flex; align-items: center; justify-content: center; border: 2px solid var(--border); }
.artist-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.avatar-placeholder { font-size: var(--fs-lg); font-weight: 800; color: var(--primary); }
.artist-details { flex: 1; }
.artist-name-lg { font-size: var(--fs-md); font-weight: 800; color: var(--ink); }
.artist-meta { display: flex; flex-direction: column; gap: 3px; margin-top: 4px; }
.artist-meta-item { display: flex; align-items: center; gap: 5px; font-size: var(--fs-sm); color: var(--muted); }
.artist-meta-item .bi { color: var(--primary); font-size: 11px; }

.address-block { background: var(--lavender); border: 1px solid #ddd8f8; border-radius: var(--radius-md); padding: var(--sp-md); display: flex; gap: var(--sp-sm); align-items: flex-start; }
.address-icon { width: 34px; height: 34px; border-radius: 50%; background: var(--white); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 15px; flex-shrink: 0; border: 1.5px solid #ddd8f8; }
.address-lines { flex: 1; }
.address-name { font-size: var(--fs-base); font-weight: 700; color: var(--ink); margin-bottom: 3px; }
.address-line { font-size: var(--fs-sm); color: var(--muted); line-height: 1.6; }

.product-row { display: flex; align-items: flex-start; gap: var(--sp-md); padding: var(--sp-md); border-bottom: 1px solid var(--divider); }
.product-row:last-child { border-bottom: none; }
.product-row:hover { background: #fafafe; }
.product-thumb { width: 80px; height: 80px; border-radius: var(--radius-md); overflow: hidden; flex-shrink: 0; background: var(--lavender); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.6rem; border: 1px solid var(--border); }
.product-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.product-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 5px; }
.product-name { font-size: var(--fs-base); font-weight: 700; color: var(--ink); line-height: 1.3; }
.product-tags { display: flex; flex-wrap: wrap; gap: 5px; }
.ptag { display: inline-flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
.ptag-type    { background: var(--lavender); color: var(--primary-2); }
.ptag-digital { background: #eff6ff; color: #1d4ed8; }
.ptag-custom  { background: #fdf4ff; color: #7e22ce; }
.ptag-mat     { background: #f0fdf4; color: #166534; }
.ptag-size    { background: #fff7ed; color: #c2410c; }
.product-price-col { flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 4px; min-width: 90px; }
.product-qty { display: inline-flex; align-items: center; gap: 3px; font-size: var(--fs-sm); color: var(--muted); font-weight: 600; background: var(--divider); padding: 2px 8px; border-radius: 20px; }
.product-unit-price { font-size: var(--fs-base); font-weight: 700; color: var(--ink); }
.per-unit { font-size: 11px; color: var(--muted); font-weight: 400; }
.product-subtotal { font-size: var(--fs-sm); font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--primary-2)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

.totals-block { display: flex; flex-direction: column; gap: 0; }
.total-row { display: flex; justify-content: space-between; align-items: center; padding: var(--sp-xs) 0; font-size: var(--fs-base); color: var(--muted); border-bottom: 1px solid var(--divider); }
.total-row:last-child { border-bottom: none; }
.total-row.grand { font-size: var(--fs-md); font-weight: 800; color: var(--ink); padding-top: var(--sp-sm); margin-top: 2px; }
.grand-amount { background: linear-gradient(135deg, var(--primary), var(--primary-2)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: var(--fs-lg); font-weight: 800; }

.track-steps { display: flex; align-items: flex-start; justify-content: space-between; padding: var(--sp-md) var(--sp-lg); gap: 0; }
.track-step { display: flex; flex-direction: column; align-items: center; gap: 6px; flex: 1; position: relative; font-size: var(--fs-sm); color: var(--muted); text-align: center; }
.track-step:not(:last-child)::after { content: ''; position: absolute; top: 13px; left: 50%; right: -50%; height: 2px; background: var(--border); z-index: 0; }
.track-step.step-done:not(:last-child)::after { background: linear-gradient(90deg, var(--primary), var(--primary-2)); }
.track-dot { width: 26px; height: 26px; border-radius: 50%; background: var(--divider); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; z-index: 1; flex-shrink: 0; font-size: 10px; color: var(--muted); transition: all .2s; }
.track-step.step-done .track-dot { background: linear-gradient(135deg, var(--primary), var(--primary-2)); border-color: transparent; color: #fff; }
.track-step.step-current .track-dot { box-shadow: 0 0 0 4px rgba(102,126,234,.2); }
.track-label { font-size: 11px; font-weight: 600; color: var(--muted); }
.track-step.step-done .track-label    { color: var(--primary); }
.track-step.step-current .track-label { color: var(--primary-2); font-weight: 800; }
.tracking-courier-row { display: flex; align-items: center; gap: var(--sp-sm); padding: var(--sp-sm) var(--sp-md); background: var(--lavender); border-bottom: 1px solid #ddd8f8; font-size: var(--fs-sm); }
.tracking-courier-row .bi { color: var(--primary); }

.expiry-banner { border-radius: var(--radius-md); padding: 11px 14px; display: flex; align-items: flex-start; gap: 10px; font-size: var(--fs-sm); line-height: 1.6; }

.refund-banner { border-radius: var(--radius-md); overflow: hidden; border: 1.5px solid #fed7aa; }
.refund-banner-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; padding: 10px var(--sp-md); background: linear-gradient(135deg, #fff8f0, #fef3c7); }
.refund-banner.refunded { border-color: #bbf7d0; }
.refund-banner.refunded .refund-banner-header { background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
.refund-banner.rejected  { border-color: #fecaca; }
.refund-banner.rejected  .refund-banner-header { background: linear-gradient(135deg, #fef2f2, #fee2e2); }
.refund-banner-left { display: flex; align-items: center; gap: 10px; }
.refund-banner-icon { width: 36px; height: 36px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #d97706; flex-shrink: 0; border: 1.5px solid #fcd34d; box-shadow: 0 2px 6px rgba(252,211,77,.25); }
.refund-banner.refunded .refund-banner-icon { color: #166534; border-color: #bbf7d0; }
.refund-banner.rejected  .refund-banner-icon { color: #991b1b; border-color: #fecaca; }
.refund-banner-title { font-size: 13px; font-weight: 800; color: #92400e; }
.refund-banner.refunded .refund-banner-title { color: #166534; }
.refund-banner.rejected  .refund-banner-title { color: #991b1b; }
.refund-banner-sub { font-size: 11px; color: #b45309; margin-top: 1px; }
.refund-banner.refunded .refund-banner-sub { color: #15803d; }
.refund-banner.rejected  .refund-banner-sub { color: #dc2626; }
.refund-banner-body { padding: var(--sp-sm) var(--sp-md); background: #fff; display: flex; align-items: flex-start; gap: var(--sp-md); flex-wrap: wrap; }
.refund-reason-box { flex: 1; min-width: 180px; background: #fff8f0; border: 1px solid #fed7aa; border-radius: var(--radius-sm); padding: 8px 12px; }
.refund-reason-label { font-size: 11px; color: #b45309; font-weight: 700; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
.refund-reason-text  { font-size: 13px; color: #374151; font-style: italic; line-height: 1.5; }

.od-actions { display: flex; align-items: center; justify-content: flex-end; gap: var(--sp-sm); flex-wrap: wrap; padding: var(--sp-md); }
.btn-primary { display: inline-flex; align-items: center; gap: var(--sp-xs); padding: 9px var(--sp-lg); border-radius: var(--radius-sm); background: linear-gradient(135deg, var(--primary), var(--primary-2)); color: #fff; font-weight: 700; font-size: var(--fs-base); border: none; cursor: pointer; text-decoration: none; box-shadow: 0 3px 10px rgba(102,126,234,.28); transition: opacity .15s; font-family: 'Inter', sans-serif; }
.btn-primary:hover { opacity: .88; }
.btn-success { display: inline-flex; align-items: center; gap: var(--sp-xs); padding: 9px var(--sp-lg); border-radius: var(--radius-sm); background: linear-gradient(135deg, #48bb78, #38a169); color: #fff; font-weight: 700; font-size: var(--fs-base); border: none; cursor: pointer; box-shadow: 0 3px 10px rgba(72,187,120,.3); transition: opacity .15s; font-family: 'Inter', sans-serif; }
.btn-success:hover { opacity: .88; }
.btn-outline { display: inline-flex; align-items: center; gap: var(--sp-xs); padding: 9px var(--sp-lg); border-radius: var(--radius-sm); background: var(--white); color: var(--primary-2); font-weight: 700; font-size: var(--fs-base); border: 1.5px solid #ddd8f8; text-decoration: none; transition: all .15s; }
.btn-outline:hover { background: var(--lavender); }
.btn-danger { display: inline-flex; align-items: center; gap: var(--sp-xs); padding: 9px var(--sp-lg); border-radius: var(--radius-sm); background: #fff5f5; color: #c62828; font-weight: 700; font-size: var(--fs-base); border: 1.5px solid #fca5a5; cursor: pointer; transition: all .15s; font-family: 'Inter', sans-serif; }
.btn-danger:hover { background: #fee2e2; }
.status-note { font-size: var(--fs-sm); color: var(--muted); display: flex; align-items: center; gap: var(--sp-xs); }
.status-note--green { color: #2e7d32; }

.modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); backdrop-filter: blur(3px); z-index: 200; }
.modal-backdrop.show { display: block; }
.cancel-modal { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(.9); opacity: 0; pointer-events: none; width: 92%; max-width: 420px; background: var(--white); border-radius: var(--radius-lg); z-index: 201; transition: all .22s cubic-bezier(.34,1.56,.64,1); overflow: hidden; text-align: center; box-shadow: 0 24px 64px rgba(102,126,234,.22), 0 4px 16px rgba(0,0,0,.08); }
.cancel-modal.open { transform: translate(-50%, -50%) scale(1); opacity: 1; pointer-events: auto; }
.cancel-modal-body { padding: 36px 28px 28px; }
.cancel-modal-icon { width: 64px; height: 64px; background: linear-gradient(135deg, #fff5f5, #fed7d7); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; border: 2px solid #fca5a5; box-shadow: 0 4px 12px rgba(239,68,68,.15); }
.cancel-modal-footer { display: flex; gap: var(--sp-sm); padding: 0 28px 28px; }
.cancel-modal-footer button { flex: 1; padding: 12px; border-radius: var(--radius-sm); font-size: var(--fs-base); font-weight: 700; cursor: pointer; font-family: 'Inter', sans-serif; }

@media (max-width: 768px) {
    .od-main { padding: var(--sp-sm) var(--sp-sm) 80px; }
    .od-title-row { flex-direction: column; align-items: flex-start; }
    .artist-block { flex-direction: column; align-items: flex-start; }
    .product-thumb { width: 60px; height: 60px; }
    .product-price-col { min-width: 70px; }
    .track-steps { padding: var(--sp-sm); }
    .od-actions { flex-direction: column; }
    .od-actions button, .od-actions a { width: 100%; justify-content: center; }
}
</style>
@endsection

@section('content')

@php
    $refundStatus = $order->refund_status ?? 'none';
    $canCancel    = $order->status === 'pending_payment';
    $autoRefund   = $refundStatus === 'none'
                 && $order->payment_status === 'paid'
                 && $order->status === 'processing';
    $canRefund    = $refundStatus === 'none'
                 && $order->payment_status === 'paid'
                 && in_array($order->status, ['preparing','shipped','completed'])
                 && ($order->status !== 'completed' || now()->diffInDays($order->updated_at) <= 7);

    $buyerLabel = match($order->status) {
        'pending_payment' => 'Pending Payment',
        'processing'      => 'Order Placed',
        'preparing'       => 'Preparing',
        'shipped'         => 'Shipped',
        'completed'       => 'Completed',
        'cancelled'       => 'Cancelled',
        default           => ucfirst($order->status),
    };
    $buyerClass = match($order->status) {
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

    $firstArtwork = $order->items->first()?->artwork;
    $artistUser   = $order->artist?->user ?? $firstArtwork?->artist?->user;
    $artistName   = $artistUser?->fullname ?? $artistUser?->name
                 ?? $order->artist?->name ?? $firstArtwork?->artist?->name
                 ?? 'Unknown Artist';
    $artistEmail  = $artistUser?->email ?? null;
    $artistPhone  = $artistUser?->phone ?? null;
    $artistAvatar = $artistUser?->profile_image ?? $order->artist?->profile_image ?? null;
    $artistInitial = strtoupper(substr($artistName, 0, 1));

    $buyer            = auth()->user();
    $shippingName     = $buyer->fullname ?? $buyer->name ?? null;
    $shippingPhone    = $buyer->phone ?? null;
    $shippingAddress  = $buyer->address ?? null;
    $shippingCity     = $buyer->city ?? null;
    $shippingState    = $buyer->state ?? null;
    $shippingPostcode = $buyer->postcode ?? $buyer->postal_code ?? null;
    $shippingCountry  = $buyer->country ?? 'Malaysia';

    $subtotal      = $order->items->sum(fn($i) => ($i->price ?? 0) * ($i->quantity ?? 1));
    $orderShipping = (float) ($order->shipping_fee ?? 0);
    $orderTotal    = (float) ($order->total ?? $order->price ?? 0);

    // Custom order reference image (from the original request)
    $customRefImage = $order->customOrderRequest?->reference_image ?? null;

    // Auto-cancel countdown
    $expiresAt  = $order->created_at->addHours(24);
    $hoursLeft  = (int) now()->diffInHours($expiresAt, false);
    $minsLeft   = (int) now()->diffInMinutes($expiresAt, false);
    $isUrgent   = $hoursLeft < 2 && $hoursLeft >= 0;
    $isExpiring = $hoursLeft >= 2 && $hoursLeft < 6;

    // Is this a custom order?
    $isCustomOrder = $order->items->every(fn($i) => is_null($i->artwork_sell_id));
@endphp

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('orders.index') }}">My Orders</a>
        <span class="sep">/</span>
        <span class="cur">Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
    </div>
</div>

<main class="od-main">

    <a href="{{ route('orders.index') }}" class="back-btn">
        <i class="bi bi-arrow-left"></i> Back to My Orders
    </a>

    {{-- Title row --}}
    <div class="od-title-row">
        <div class="od-title">
            <h1>Order Detail</h1>
            <span class="order-num">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span class="status-badge status-{{ $buyerClass }}">
                @switch($order->status)
                    @case('pending_payment') <i class="bi bi-clock"></i>             @break
                    @case('processing')      <i class="bi bi-bell-fill"></i>         @break
                    @case('preparing')       <i class="bi bi-box-seam"></i>          @break
                    @case('shipped')         <i class="bi bi-truck"></i>             @break
                    @case('completed')       <i class="bi bi-check-circle-fill"></i> @break
                    @case('cancelled')       <i class="bi bi-x-circle-fill"></i>     @break
                    @default                 <i class="bi bi-circle"></i>
                @endswitch
                {{ $buyerLabel }}
            </span>
            @if($isCustomOrder)
                <span class="status-badge" style="background:#fdf4ff;color:#7e22ce;">
                    <i class="bi bi-brush"></i> Custom Order
                </span>
            @endif
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

    {{-- Expiry disclaimer --}}
    @if($order->status === 'pending_payment')
    <div class="expiry-banner" style="
        background: {{ $hoursLeft <= 0 ? '#fff5f5' : ($isUrgent ? '#fff5f5' : ($isExpiring ? '#fff8f0' : '#f0f4ff')) }};
        border: 1.5px solid {{ $hoursLeft <= 0 ? '#fca5a5' : ($isUrgent ? '#fca5a5' : ($isExpiring ? '#fcd34d' : '#c7d2fe')) }};
        color: {{ $hoursLeft <= 0 ? '#991b1b' : ($isUrgent ? '#991b1b' : ($isExpiring ? '#92400e' : '#3730a3')) }};">
        <i class="bi {{ $hoursLeft <= 0 ? 'bi-exclamation-circle-fill' : ($isUrgent ? 'bi-exclamation-circle-fill' : 'bi-clock-fill') }}"
           style="font-size:15px;flex-shrink:0;margin-top:1px;color:{{ $hoursLeft <= 0 ? '#ef4444' : ($isUrgent ? '#ef4444' : ($isExpiring ? '#d97706' : '#667eea')) }};"></i>
        <span>
            @if($hoursLeft <= 0)
                <strong>Payment overdue.</strong> This order will be cancelled automatically very soon.
            @elseif($isUrgent)
                <strong>Expiring soon!</strong> Automatically cancelled in <strong>{{ $minsLeft }} minute{{ $minsLeft !== 1 ? 's' : '' }}</strong>. Pay now to secure your order.
            @elseif($isExpiring)
                <strong>Payment due soon.</strong> Expires in <strong>{{ $hoursLeft }} hour{{ $hoursLeft !== 1 ? 's' : '' }}</strong> (by {{ $expiresAt->format('d M, g:i A') }}).
            @else
                Complete payment by <strong>{{ $expiresAt->format('d M Y, g:i A') }}</strong> or this order will be automatically cancelled ({{ $hoursLeft }} hour{{ $hoursLeft !== 1 ? 's' : '' }} remaining).
            @endif
        </span>
    </div>
    @endif

    {{-- ROW 1: Artist + Order Info --}}
    <div class="od-grid">

        {{-- Artist --}}
        <div class="od-card">
            <div class="od-card-header">
                <i class="bi bi-palette-fill"></i> Artist / Seller
            </div>
            <div class="artist-block">
                <div class="artist-avatar">
                    @if($artistAvatar)
                        <img src="{{ asset('storage/' . $artistAvatar) }}" alt="{{ $artistName }}">
                    @else
                        <div class="avatar-placeholder">{{ $artistInitial }}</div>
                    @endif
                </div>
                <div class="artist-details">
                    <div class="artist-name-lg">{{ $artistName }}</div>
                    <div class="artist-meta">
                        @if($artistEmail)
                        <div class="artist-meta-item">
                            <i class="bi bi-envelope"></i> {{ $artistEmail }}
                        </div>
                        @endif
                        @if($artistPhone)
                        <div class="artist-meta-item">
                            <i class="bi bi-telephone"></i> {{ $artistPhone }}
                        </div>
                        @endif
                        @if($artistUser?->created_at)
                        <div class="artist-meta-item">
                            <i class="bi bi-calendar-check"></i>
                            Member since {{ $artistUser->created_at->format('M Y') }}
                        </div>
                        @endif
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
                    <span class="info-label">Order Type</span>
                    <span class="info-val">
                        @if($isCustomOrder)
                            <span style="color:#7e22ce;font-weight:600;">
                                <i class="bi bi-brush"></i> Custom Order
                            </span>
                        @else
                            <span style="color:var(--muted);">
                                <i class="bi bi-bag"></i> Regular Order
                            </span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment</span>
                    <span class="info-val">
                        @if($order->payment_status === 'paid')
                            <span style="color:#2e7d32;font-weight:700;"><i class="bi bi-check-circle-fill"></i> Paid</span>
                        @else
                            <span style="color:#e65100;font-weight:700;"><i class="bi bi-clock"></i> Unpaid</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Delivery</span>
                    <span class="info-val">
                        @if($isAllDigital)
                            <span style="color:#1d4ed8;font-weight:600;"><i class="bi bi-cloud-download"></i> Digital</span>
                        @else
                            <span style="color:var(--muted);"><i class="bi bi-box-seam"></i> Physical Shipping</span>
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

    {{-- Custom Order Request Details --}}
    @if($isCustomOrder && $order->customOrderRequest)
    @php $req = $order->customOrderRequest; @endphp
    <div class="od-card">
        <div class="od-card-header">
            <i class="bi bi-brush"></i> Custom Order Request Details
        </div>
        <div style="display:flex;gap:var(--sp-md);padding:var(--sp-md);flex-wrap:wrap;">
            {{-- Reference image --}}
            @if($req->reference_image)
            <div style="flex-shrink:0;">
                <div style="font-size:var(--fs-sm);font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px;">Reference Image</div>
                <div style="width:120px;height:120px;border-radius:var(--radius-md);overflow:hidden;border:1px solid var(--border);">
                    <img src="{{ asset('storage/' . $req->reference_image) }}"
                         alt="Reference"
                         style="width:100%;height:100%;object-fit:cover;display:block;">
                </div>
            </div>
            @endif
            {{-- Request details --}}
            <div style="flex:1;min-width:200px;">
                <div class="info-row">
                    <span class="info-label">Title</span>
                    <span class="info-val">{{ $req->title }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Type</span>
                    <span class="info-val">{{ ucfirst($req->product_type) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Your Price</span>
                    <span class="info-val">RM {{ number_format($req->buyer_price, 2) }}</span>
                </div>
                @if($req->counter_price && $req->counter_price != $req->buyer_price)
                <div class="info-row">
                    <span class="info-label">Final Price</span>
                    <span class="info-val" style="color:var(--primary);font-weight:700;">RM {{ number_format($req->counter_price, 2) }}</span>
                </div>
                @endif
                @if($req->description)
                <div class="info-row">
                    <span class="info-label">Description</span>
                    <span class="info-val" style="font-style:italic;color:var(--muted);">{{ $req->description }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Submitted</span>
                    <span class="info-val">{{ $req->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Shipping Address --}}
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
                        <div class="address-line">{{ implode(', ', array_filter([$shippingPostcode, $shippingCity])) }}</div>
                        @endif
                        @if($shippingState || $shippingCountry)
                        <div class="address-line">{{ implode(', ', array_filter([$shippingState, $shippingCountry])) }}</div>
                        @endif
                    </div>
                </div>
            @else
                <div style="color:var(--muted);font-size:var(--fs-sm);padding:var(--sp-sm) 0;display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-info-circle"></i>
                    No shipping address recorded. Please update your profile with a delivery address.
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Tracking --}}
    @if(in_array($order->status, ['shipped','completed']) && $order->tracking_number)
    <div class="od-card">
        <div class="od-card-header">
            <i class="bi bi-truck"></i> Parcel Tracking
        </div>
        <div class="tracking-courier-row">
            <i class="bi bi-truck"></i>
            <strong>{{ strtoupper($order->courier ?? 'Courier') }}</strong>
            &mdash;
            <span class="info-val mono" style="font-size:12px;">{{ $order->tracking_number }}</span>
            @if($order->getTrackingUrl())
                <a href="{{ $order->getTrackingUrl() }}" target="_blank"
                   style="margin-left:auto;display:inline-flex;align-items:center;gap:4px;font-size:var(--fs-sm);font-weight:700;color:var(--primary);text-decoration:none;">
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
                        || !in_array($order->status, $trackingSteps[$i+1]['doneWhen'] ?? [])
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

    {{-- Items --}}
    <div class="od-card">
        <div class="od-card-header">
            <i class="bi bi-bag-check-fill"></i> Items Ordered
            <span style="margin-left:auto;background:var(--white);color:var(--muted);padding:1px 8px;border-radius:10px;font-size:11px;font-weight:600;">
                {{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}
            </span>
        </div>

        @foreach($order->items as $item)
        @php
            $artwork      = $item->artwork;
            $isCustomItem = is_null($item->artwork_sell_id);

            // Image: artwork image → custom request reference image → item image
            $imgPath = $artwork?->image_path
                ?? ($isCustomItem ? $customRefImage : null)
                ?? $item->image_path
                ?? null;

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
                        @if($order->customOrderRequest?->product_type)
                            <span class="ptag ptag-type"><i class="bi bi-tag"></i> {{ ucfirst($order->customOrderRequest->product_type) }}</span>
                        @endif
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
                    @endif
                </div>
                @if($item->variant ?? null)
                    <div style="font-size:var(--fs-sm);color:var(--muted);">
                        <i class="bi bi-collection"></i> Variant: {{ $item->variant }}
                    </div>
                @endif
                @if($isCustomItem && $order->customOrderRequest?->description)
                    <div style="background:var(--divider);border-radius:var(--radius-sm);padding:6px 10px;font-size:var(--fs-sm);color:var(--muted);display:flex;gap:5px;align-items:flex-start;line-height:1.5;">
                        <i class="bi bi-chat-left-text" style="flex-shrink:0;margin-top:1px;color:var(--primary);"></i>
                        {{ Str::limit($order->customOrderRequest->description, 120) }}
                    </div>
                @elseif($artwork?->description ?? null)
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
                @if($orderShipping > 0)
                <div class="total-row">
                    <span><i class="bi bi-truck"></i> Shipping Fee</span>
                    <span>RM {{ number_format($orderShipping, 2) }}</span>
                </div>
                @endif
                <div class="total-row grand">
                    <span>Order Total</span>
                    <span class="grand-amount">RM {{ number_format($orderTotal, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Refund Banner --}}
    @if($refundStatus === 'requested')
    <div class="refund-banner">
        <div class="refund-banner-header">
            <div class="refund-banner-left">
                <div class="refund-banner-icon"><i class="bi bi-arrow-return-left"></i></div>
                <div>
                    <div class="refund-banner-title">Refund Request Submitted</div>
                    <div class="refund-banner-sub">
                        <i class="bi bi-clock"></i>
                        {{ \Carbon\Carbon::parse($order->refund_requested_at)->format('d M Y, h:i A') }}
                        &nbsp;·&nbsp; RM {{ number_format($orderTotal, 2) }}
                        &nbsp;·&nbsp; Awaiting seller review
                    </div>
                </div>
            </div>
        </div>
        @if($order->refund_reason)
        <div class="refund-banner-body">
            <div class="refund-reason-box">
                <div class="refund-reason-label"><i class="bi bi-chat-quote"></i> Your Reason</div>
                <div class="refund-reason-text">"{{ $order->refund_reason }}"</div>
            </div>
        </div>
        @endif
    </div>

    @elseif($refundStatus === 'refunded')
    <div class="refund-banner refunded">
        <div class="refund-banner-header">
            <div class="refund-banner-left">
                <div class="refund-banner-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="refund-banner-title">Refund Processed</div>
                    <div class="refund-banner-sub">
                        RM {{ number_format($order->refund_amount ?? $orderTotal, 2) }} returned to your original payment method
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

    {{-- Actions --}}
    <div class="od-card">
        <div class="od-actions">
            @if($order->status === 'pending_payment')
                <span class="status-note"><i class="bi bi-clock"></i> Awaiting payment</span>
                <a href="{{ route('order.checkout.repay', $order->id) }}" class="btn-primary">
                    <i class="bi bi-credit-card-fill"></i> Pay Now
                </a>
                <button type="button" class="btn-danger" onclick="openCancelModal()">
                    <i class="bi bi-x-circle"></i> Cancel Order
                </button>

            @elseif($order->status === 'processing')
                <span class="status-note"><i class="bi bi-bell-fill"></i> Waiting for seller to accept your order</span>
                @if($autoRefund)
                <button type="button" class="btn-outline"
                    onclick="openRefundModal({{ $order->id }}, '{{ addslashes($order->items->first()?->name ?? 'this order') }}', 'RM {{ number_format($orderTotal, 2) }}', true)">
                    <i class="bi bi-arrow-return-left"></i> Request Refund
                </button>
                @endif

            @elseif($order->status === 'preparing')
                <span class="status-note"><i class="bi bi-box-seam"></i> Seller is preparing your order</span>
                @if($canRefund)
                <button type="button" class="btn-outline"
                    onclick="openRefundModal({{ $order->id }}, '{{ addslashes($order->items->first()?->name ?? 'this order') }}', 'RM {{ number_format($orderTotal, 2) }}', false)">
                    <i class="bi bi-arrow-return-left"></i> Request Refund
                </button>
                @endif

            @elseif($order->status === 'shipped')
                @if($order->getTrackingUrl())
                    <a href="{{ $order->getTrackingUrl() }}" target="_blank" class="btn-outline">
                        <i class="bi bi-box-arrow-up-right"></i> Track Parcel
                    </a>
                @endif
                <form action="{{ route('orders.complete', $order->id) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Confirm that you have received this order?')">
                    @csrf
                    <button type="submit" class="btn-success">
                        <i class="bi bi-check-circle-fill"></i> Order Received
                    </button>
                </form>
                @if($canRefund)
                <button type="button" class="btn-outline"
                    onclick="openRefundModal({{ $order->id }}, '{{ addslashes($order->items->first()?->name ?? 'this order') }}', 'RM {{ number_format($orderTotal, 2) }}', false)">
                    <i class="bi bi-arrow-return-left"></i> Request Refund
                </button>
                @endif

            @elseif($order->status === 'completed')
                @if($canRefund)
                <button type="button" class="btn-outline"
                    onclick="openRefundModal({{ $order->id }}, '{{ addslashes($order->items->first()?->name ?? 'this order') }}', 'RM {{ number_format($orderTotal, 2) }}', false)">
                    <i class="bi bi-arrow-return-left"></i> Request Refund
                </button>
                @endif
                @if(!$order->has_review)
                    <a href="{{ route('reviews.create', $order->id) }}" class="btn-primary">
                        <i class="bi bi-star-fill"></i> Leave a Review
                    </a>
                @else
                    <span class="status-note status-note--green">
                        <i class="bi bi-check-circle-fill"></i> You have reviewed this order
                    </span>
                @endif

            @elseif($order->status === 'cancelled')
                <span class="status-note" style="color:#c62828;">
                    <i class="bi bi-x-circle-fill"></i> This order was cancelled
                </span>
            @endif

            @if($order->status === 'completed')
                <a href="{{ route('orders.receipt', $order->id) }}" class="btn-outline">
                    <i class="bi bi-file-earmark-arrow-down"></i> Download Receipt
                </a>
            @endif
        </div>
    </div>

</main>

{{-- Cancel Modal --}}
<div class="modal-backdrop" id="cancelBackdrop" onclick="closeCancelModal()"></div>
<div class="cancel-modal" id="cancelModal">
    <div class="cancel-modal-body">
        <div class="cancel-modal-icon">
            <i class="bi bi-ban" style="color:#ef4444;font-size:1.6rem;"></i>
        </div>
        <h3 style="font-size:1.1rem;font-weight:800;color:var(--ink);margin-bottom:8px;">Cancel Order?</h3>
        <p style="font-size:var(--fs-sm);color:var(--muted);line-height:1.7;margin-bottom:6px;">
            You are about to cancel Order <strong style="color:var(--ink);">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong>.
        </p>
        <p style="font-size:var(--fs-sm);color:var(--muted);line-height:1.65;margin-bottom:10px;">
            This order has not been paid yet. Cancelling will remove the order permanently. This action cannot be undone.
        </p>
    </div>
    <div class="cancel-modal-footer">
        <button onclick="closeCancelModal()"
            style="border:1.5px solid var(--border);background:var(--white);color:var(--muted);"
            onmouseover="this.style.background='var(--divider)';" onmouseout="this.style.background='var(--white)';">
            Keep Order
        </button>
        <button onclick="document.getElementById('cancelOrderForm').submit()"
            style="border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;box-shadow:0 4px 14px rgba(239,68,68,.35);"
            onmouseover="this.style.opacity='.88';" onmouseout="this.style.opacity='1';">
            <i class="bi bi-ban" style="margin-right:4px;"></i> Yes, Cancel
        </button>
    </div>
</div>
<form id="cancelOrderForm" method="POST" action="{{ route('orders.cancel', $order->id) }}" style="display:none;">@csrf</form>

{{-- Refund Modal --}}
<div id="refundBackdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.48);backdrop-filter:blur(3px);z-index:200;" onclick="closeRefundModal()"></div>
<div id="refundModalEl" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:92%;max-width:480px;background:#fff;border-radius:16px;padding:32px 28px 26px;box-shadow:0 24px 64px rgba(102,126,234,.22);z-index:201;">
    <div style="text-align:center;margin-bottom:20px;">
        <div style="width:56px;height:56px;background:linear-gradient(135deg,#fff8f0,#fef3c7);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;border:2px solid #fcd34d;">
            <i class="bi bi-arrow-return-left" style="color:#d97706;font-size:1.3rem;"></i>
        </div>
        <h3 style="font-size:1.08rem;font-weight:800;color:var(--ink);margin:0 0 4px;">Request Refund</h3>
        <p id="refundSubtitle" style="font-size:var(--fs-sm);color:var(--muted);margin:0;"></p>
    </div>
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:9px;padding:11px 14px;margin-bottom:16px;display:flex;align-items:flex-start;gap:9px;">
        <i class="bi bi-info-circle-fill" style="color:#2563eb;font-size:14px;margin-top:1px;flex-shrink:0;"></i>
        <span id="refundInfoText" style="font-size:12px;color:#1d4ed8;line-height:1.55;"></span>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
        <button type="button" onclick="pickReason('Item not as described')" style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:500;background:#ede9fe;color:#5b21b6;border:1px solid #c4b5fd;cursor:pointer;font-family:inherit;">Item not as described</button>
        <button type="button" onclick="pickReason('Item damaged or defective')" style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:500;background:#ede9fe;color:#5b21b6;border:1px solid #c4b5fd;cursor:pointer;font-family:inherit;">Item damaged or defective</button>
        <button type="button" onclick="pickReason('Wrong item received')" style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:500;background:#ede9fe;color:#5b21b6;border:1px solid #c4b5fd;cursor:pointer;font-family:inherit;">Wrong item received</button>
        <button type="button" onclick="pickReason('Item not received')" style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:500;background:#ede9fe;color:#5b21b6;border:1px solid #c4b5fd;cursor:pointer;font-family:inherit;">Item not received</button>
        <button type="button" onclick="pickReason('Changed my mind')" style="padding:5px 12px;border-radius:20px;font-size:12px;font-weight:500;background:#ede9fe;color:#5b21b6;border:1px solid #c4b5fd;cursor:pointer;font-family:inherit;">Changed my mind</button>
    </div>
    <form id="refundFormEl" method="POST">
        @csrf
        <input type="hidden" name="auto_refund" id="refundAutoInput" value="0">
        <label style="font-size:13px;font-weight:600;color:var(--ink);display:block;margin-bottom:7px;">Reason <span style="color:#ef4444;">*</span></label>
        <textarea id="refundReasonInput" name="reason" rows="4"
            placeholder="Describe why you are requesting a refund..."
            minlength="10" maxlength="1000" required
            style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:11px 13px;font-size:13px;font-family:inherit;color:var(--ink);resize:vertical;outline:none;box-sizing:border-box;"
            onfocus="this.style.borderColor='#667eea'" onblur="this.style.borderColor='#e5e7eb'"></textarea>
        <p style="font-size:11px;color:#9ca3af;margin:5px 0 18px;">Minimum 10 characters.</p>
        <div style="display:flex;gap:10px;">
            <button type="button" onclick="closeRefundModal()" style="flex:1;padding:11px;border-radius:8px;border:1.5px solid var(--border);background:#fff;color:var(--muted);font-size:var(--fs-base);font-weight:600;cursor:pointer;font-family:inherit;">Cancel</button>
            <button type="submit" style="flex:2;padding:11px;border-radius:8px;border:none;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;font-size:var(--fs-base);font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:7px;box-shadow:0 4px 14px rgba(102,126,234,.35);">
                <i class="bi bi-send-fill"></i> Submit Request
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function openCancelModal() {
    document.getElementById('cancelBackdrop').classList.add('show');
    document.getElementById('cancelModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeCancelModal() {
    document.getElementById('cancelBackdrop').classList.remove('show');
    document.getElementById('cancelModal').classList.remove('open');
    document.body.style.overflow = '';
}
function openRefundModal(orderId, orderName, orderAmount, isAuto) {
    document.getElementById('refundFormEl').action = '/refund/order/' + orderId;
    document.getElementById('refundAutoInput').value = isAuto ? '1' : '0';
    document.getElementById('refundSubtitle').textContent = '\u201c' + orderName + '\u201d \u2022 ' + orderAmount;
    document.getElementById('refundReasonInput').value = '';
    document.getElementById('refundInfoText').innerHTML = isAuto
        ? 'The seller has not accepted your order yet. Your refund will be processed <strong>automatically</strong> and returned to your original payment method within <strong>3\u20135 business days</strong>.'
        : 'The seller will review your request. If approved, the refund returns to your original payment method within <strong>3\u20135 business days</strong>.';
    document.getElementById('refundBackdrop').style.display = 'block';
    document.getElementById('refundModalEl').style.display  = 'block';
    document.body.style.overflow = 'hidden';
}
function closeRefundModal() {
    document.getElementById('refundBackdrop').style.display = 'none';
    document.getElementById('refundModalEl').style.display  = 'none';
    document.body.style.overflow = '';
}
function pickReason(text) { document.getElementById('refundReasonInput').value = text; }

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeCancelModal(); closeRefundModal(); }
});

@if(session('success'))
(function(){
    var el = document.getElementById('successPopup');
    if (el) { document.getElementById('successMessage').textContent = @json(session('success')); el.classList.add('show'); setTimeout(function(){ el.classList.add('hide'); setTimeout(function(){ el.classList.remove('show','hide'); }, 300); }, 3000); }
})();
@endif
@if(session('error'))
(function(){
    var el = document.getElementById('errorPopup');
    if (el) { document.getElementById('errorMessage').textContent = @json(session('error')); el.classList.add('show'); setTimeout(function(){ el.classList.add('hide'); setTimeout(function(){ el.classList.remove('show','hide'); }, 300); }, 3000); }
})();
@endif
</script>
@endsection