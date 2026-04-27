@extends('layouts.app')

@section('title', 'Custom Order Requests')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistRequestList.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">Custom Order Requests</span>
    </div>
</div>

<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="javascript:history.back()" class="back-btn">← Back</a>
</div>

<div class="co-page">

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- Page header --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">Custom Order Requests</div>
            <div class="page-subtitle">
                @if($requests->total() > 0)
                    {{ $requests->total() }} incoming {{ Str::plural('request', $requests->total()) }}
                @else
                    No requests yet — they will appear here when buyers send them
                @endif
            </div>
        </div>
        {{-- Pending count badge --}}
        @php $pendingCount = $requests->getCollection()->where('status', 'pending')->count(); @endphp
        @if($pendingCount > 0)
            <span class="status-badge orange" style="font-size:var(--fs-base);padding:6px 14px;">
                <i class="fas fa-inbox"></i> {{ $pendingCount }} pending
            </span>
        @endif
    </div>

    {{-- List card --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                All Requests
            </div>
            @if($requests->total() > 0)
                <span class="section-count">{{ $requests->total() }} total</span>
            @endif
        </div>
        <div class="sp-card-body">

            @if($requests->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                    <h3>No custom order requests yet</h3>
                    <p>When buyers send you custom order requests from your profile, they will appear here.</p>
                </div>
            @else
                <div class="request-list">
                    @foreach($requests as $req)
                    @php $order = $req->order; @endphp
                    <a href="{{ route('artist.custom-orders.show', $req->id) }}" class="request-row">

                        {{-- Thumbnail --}}
                        <div class="request-thumb">
                            @if($req->reference_image)
                                <img src="{{ asset('storage/' . $req->reference_image) }}" alt="">
                            @else
                                <i class="fas fa-paint-brush"></i>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="request-body">
                            <div class="request-title">{{ $req->title }}</div>
                            <div class="request-meta">
                                <span>
                                    <i class="fas fa-user"></i>
                                    {{ $req->buyer->fullname ?? $req->buyer->name }}
                                </span>
                                <span>
                                    <i class="fas fa-clock"></i>
                                    {{ $req->created_at->diffForHumans() }}
                                </span>
                                @if($req->isPending())
                                <span style="color:#d97706;font-weight:700;">
                                    <i class="fas fa-exclamation-circle"></i> Needs your response
                                </span>
                                @endif
                                @if($req->isRefused() && $req->hasCounterPrice())
                                <span style="color:var(--primary);font-weight:700;">
                                    <i class="fas fa-exchange-alt"></i> Awaiting buyer
                                </span>
                                @endif
                                @if($req->isAccepted() && !$req->order_id)
                                <span style="color:#2563eb;font-weight:700;">
                                    <i class="fas fa-credit-card"></i> Awaiting payment
                                </span>
                                @endif
                                @if($req->order_id && $order)
                                <span class="order-status-chip order-status-{{ $order->status }}">
                                    <i class="fas fa-box"></i>
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                                @endif
                            </div>
                        </div>

                        {{-- Right --}}
                        <div class="request-right">
                            <div class="request-price">RM {{ number_format($req->buyer_price, 2) }}</div>
                            @if($req->order_id && $order)
                                <span class="order-status-chip order-status-{{ $order->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            @else
                                <span class="status-badge {{ $req->statusColor() }}">
                                    {{ $req->statusLabel() }}
                                </span>
                            @endif
                        </div>

                    </a>
                    @endforeach
                </div>

                @if($requests->hasPages())
                <div class="pagination-wrapper">{{ $requests->links() }}</div>
                @endif
            @endif

        </div>
    </div>

</div>
@endsection