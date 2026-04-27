@extends('layouts.app')

@section('title', 'Order Details')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/myOrderDetail.css') }}">
@endsection

@section('content')
<main class="orders-main">

    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('orders.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> My Orders
            </a>
            <h1>Order <span>#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span></h1>
            <p>Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <span class="status-badge status-{{ $order->getStatusClass() }} status-lg">
            {{ $order->getStatusLabel() }}
        </span>
    </div>

    <div class="order-detail-grid">

        {{-- Left Column: Order Info --}}
        <div class="detail-main">

            {{-- Artwork Details --}}
            <div class="detail-card">
                <h2><i class="fas fa-palette"></i> Order Summary</h2>
                <div class="summary-row">
                    <span class="summary-label">Title</span>
                    <span class="summary-value">{{ $order->title ?? 'Artwork Order' }}</span>
                </div>
                @if($order->description)
                <div class="summary-row">
                    <span class="summary-label">Description</span>
                    <span class="summary-value">{{ $order->description }}</span>
                </div>
                @endif
                @if($order->artist)
                <div class="summary-row">
                    <span class="summary-label">Artist</span>
                    <span class="summary-value">{{ $order->artist->fullname ?? $order->artist->user->fullname ?? '—' }}</span>
                </div>
                @endif
                <div class="summary-row">
                    <span class="summary-label">Type</span>
                    <span class="summary-value">{{ ucfirst($order->type ?? 'Artwork') }}</span>
                </div>
            </div>

            {{-- Order Items --}}
            @if($order->items && $order->items->count() > 0)
            <div class="detail-card">
                <h2><i class="fas fa-list"></i> Items</h2>
                <div class="items-table">
                    <div class="items-header">
                        <span>Item</span>
                        <span>Qty</span>
                        <span>Price</span>
                    </div>
                    @foreach($order->items as $item)
                    <div class="items-row">
                        <span>{{ $item->name ?? 'Item' }}</span>
                        <span>×{{ $item->quantity ?? 1 }}</span>
                        <span>RM {{ number_format($item->price, 2) }}</span>
                    </div>
                    @endforeach
                    <div class="items-total">
                        <span>Total</span>
                        <span></span>
                        <span>RM {{ number_format($order->total ?? $order->price, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tracking Timeline --}}
            @if(in_array($order->status, ['shipped', 'delivered']) && $order->tracking_number)
            <div class="detail-card">
                <h2><i class="fas fa-truck"></i> Parcel Tracking</h2>
                <div class="tracking-info-row">
                    <div class="tracking-field">
                        <span class="field-label">Courier</span>
                        <span class="field-value">{{ strtoupper($order->courier ?? '—') }}</span>
                    </div>
                    <div class="tracking-field">
                        <span class="field-label">Tracking Number</span>
                        <code class="field-value">{{ $order->tracking_number }}</code>
                    </div>
                </div>

                <div class="tracking-steps tracking-steps-vertical">
                    @php
                        $steps = [
                            ['label' => 'Order Placed',       'icon' => 'fa-file-alt',    'statuses' => ['pending','processing','shipped','delivered']],
                            ['label' => 'Payment Confirmed',  'icon' => 'fa-check-circle','statuses' => ['processing','shipped','delivered']],
                            ['label' => 'Processing',         'icon' => 'fa-cog',         'statuses' => ['processing','shipped','delivered']],
                            ['label' => 'Shipped',            'icon' => 'fa-truck',       'statuses' => ['shipped','delivered']],
                            ['label' => 'Delivered',          'icon' => 'fa-box-open',    'statuses' => ['delivered']],
                        ];
                    @endphp
                    @foreach($steps as $step)
                    @php $done = in_array($order->status, $step['statuses']); @endphp
                    <div class="v-step {{ $done ? 'done' : '' }}">
                        <div class="v-dot">
                            <i class="fas {{ $step['icon'] }}"></i>
                        </div>
                        <div class="v-content">
                            <span class="v-label">{{ $step['label'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($order->getTrackingUrl())
                <a href="{{ $order->getTrackingUrl() }}" target="_blank" rel="noopener" class="btn-track btn-full">
                    <i class="fas fa-external-link-alt"></i>
                    Track on {{ strtoupper($order->courier ?? 'Courier') }} Website
                </a>
                @endif
            </div>
            @endif

        </div>

        {{-- Right Column: Payment Info --}}
        <div class="detail-sidebar">
            <div class="detail-card">
                <h2><i class="fas fa-receipt"></i> Payment</h2>
                <div class="summary-row">
                    <span class="summary-label">Status</span>
                    <span class="payment-status payment-{{ $order->payment_status }}">
                        {{ $order->payment_status === 'paid' ? '✓ Paid' : ucfirst($order->payment_status ?? 'Unpaid') }}
                    </span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Method</span>
                    <span class="summary-value">{{ ucfirst($order->payment_method ?? 'Stripe') }}</span>
                </div>
                <div class="price-total-block">
                    <span>Total Paid</span>
                    <strong>RM {{ number_format($order->total ?? $order->price, 2) }}</strong>
                </div>
            </div>
        </div>

    </div>

</main>
@endsection