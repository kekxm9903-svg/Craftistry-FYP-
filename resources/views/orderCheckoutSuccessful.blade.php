@extends('layouts.app')

@section('title', 'Order Successful!')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; background: #f7fafc; }

    .success-container {
        max-width: 500px;
        margin: 60px auto;
        padding: 0 15px;
        text-align: center;
    }

    .success-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #48bb78, #38a169);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 2rem;
        color: white;
        box-shadow: 0 4px 20px rgba(72,187,120,0.4);
    }

    .success-card {
        background: white;
        border-radius: 16px;
        padding: 40px 32px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .success-card h1 { font-size: 1.6rem; font-weight: 700; color: #1a202c; margin-bottom: 8px; }
    .success-card p  { color: #718096; font-size: 0.95rem; margin-bottom: 24px; }

    .order-items {
        background: #f7fafc;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 24px;
        text-align: left;
    }

    .order-item-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.87rem;
        padding: 6px 0;
        border-bottom: 1px solid #edf2f7;
    }

    .order-item-row:last-child { border-bottom: none; }
    .order-item-row .name  { color: #4a5568; }
    .order-item-row .price { font-weight: 600; color: #1a202c; }

    .order-total-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.95rem;
        font-weight: 700;
        padding-top: 12px;
        margin-top: 4px;
        border-top: 2px solid #e2e8f0;
        color: #1a202c;
    }

    .order-total-row .total-val { color: #667eea; }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.92rem;
        text-decoration: none;
        transition: all 0.3s;
        margin-right: 8px;
    }

    .btn-primary:hover { transform: translateY(-2px); color: white; }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: white;
        color: #4a5568;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.92rem;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-secondary:hover { border-color: #667eea; color: #667eea; }
</style>
@endsection

@section('content')
<div class="success-container">
    <div class="success-card">

        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h1>Order Confirmed!</h1>
        <p>Thank you for your purchase on Craftistry. Your order has been received!</p>

        @if($order && $order->items->count())
        <div class="order-items">
            @foreach($order->items as $item)
            <div class="order-item-row">
                <span class="name">{{ $item->name }} × {{ $item->quantity }}</span>
                <span class="price">RM {{ number_format($item->price * $item->quantity, 2) }}</span>
            </div>
            @endforeach

            <div class="order-total-row">
                <span>Total Paid</span>
                <span class="total-val">RM {{ number_format($order->total, 2) }}</span>
            </div>
        </div>
        @endif

        <div>
            <a href="{{ route('artist.browse') }}" class="btn-primary">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
            <a href="{{ route('dashboard') }}" class="btn-secondary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>

    </div>
</div>
@endsection