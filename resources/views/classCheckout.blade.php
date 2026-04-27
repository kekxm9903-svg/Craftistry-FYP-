@extends('layouts.app')

@section('title', 'Checkout — ' . $classEvent->title)

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; background: #f7fafc; }

    .checkout-container {
        max-width: 520px;
        margin: 40px auto;
        padding: 0 15px;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #718096;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 24px;
        transition: color 0.2s;
    }
    .back-btn:hover { color: #667eea; }

    .checkout-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .checkout-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 28px;
        color: white;
    }

    .checkout-header h1 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0 0 4px 0;
    }

    .checkout-header p {
        font-size: 0.85rem;
        opacity: 0.85;
        margin: 0;
    }

    .checkout-body { padding: 28px; }

    .order-summary {
        background: #f7fafc;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .order-summary h3 {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #718096;
        font-weight: 700;
        margin: 0 0 16px 0;
    }

    .order-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }

    .order-row:last-child { margin-bottom: 0; }
    .order-row .label { color: #4a5568; }
    .order-row .value { font-weight: 600; color: #1a202c; }

    .order-divider {
        border: none;
        border-top: 1px solid #e2e8f0;
        margin: 14px 0;
    }

    .order-total .label { font-weight: 700; color: #1a202c; font-size: 1rem; }
    .order-total .value { font-weight: 700; color: #667eea; font-size: 1.1rem; }

    .payment-methods {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .payment-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #4a5568;
        background: white;
    }

    .payment-badge i { font-size: 0.9rem; color: #667eea; }

    .btn-pay {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: 'Inter', sans-serif;
    }

    .btn-pay:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102,126,234,0.4);
    }

    .btn-pay:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .secure-note {
        text-align: center;
        font-size: 0.8rem;
        color: #a0aec0;
        margin-top: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .class-info-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px;
        background: rgba(102,126,234,0.06);
        border: 1px solid rgba(102,126,234,0.15);
        border-radius: 10px;
        margin-bottom: 24px;
    }

    .class-info-row i { color: #667eea; font-size: 1rem; }

    .class-info-row .class-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #1a202c;
    }

    .class-info-row .class-date {
        font-size: 0.82rem;
        color: #718096;
        margin-top: 2px;
    }
</style>
@endsection

@section('content')
<div class="checkout-container">

    <a href="{{ route('class.event.show', $classEvent->id) }}" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Class
    </a>

    <div class="checkout-card">

        <div class="checkout-header">
            <h1><i class="fas fa-lock" style="font-size:1rem; margin-right:8px;"></i>Secure Checkout</h1>
            <p>Complete your enrollment for this class</p>
        </div>

        <div class="checkout-body">

            {{-- Class Info --}}
            <div class="class-info-row">
                <i class="fas fa-palette"></i>
                <div>
                    <div class="class-name">{{ $classEvent->title }}</div>
                    <div class="class-date">
                        <i class="far fa-calendar-alt"></i>
                        {{ \Carbon\Carbon::parse($classEvent->start_date)->format('M d, Y') }}
                        •
                        {{ \Carbon\Carbon::parse($classEvent->start_time)->format('g:i A') }}
                    </div>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="order-summary">
                <h3>Order Summary</h3>

                <div class="order-row">
                    <span class="label">Class fee</span>
                    <span class="value">RM {{ number_format($classEvent->price, 2) }}</span>
                </div>

                <div class="order-row">
                    <span class="label">Processing fee</span>
                    <span class="value">RM 0.00</span>
                </div>

                <hr class="order-divider">

                <div class="order-row order-total">
                    <span class="label">Total</span>
                    <span class="value">RM {{ number_format($classEvent->price, 2) }}</span>
                </div>
            </div>

            {{-- Payment Methods --}}
            <div style="margin-bottom: 12px;">
                <p style="font-size:0.82rem; color:#718096; font-weight:600; margin-bottom:10px;">
                    ACCEPTED PAYMENT METHODS
                </p>
                <div class="payment-methods">
                    <div class="payment-badge"><i class="fas fa-university"></i> FPX</div>
                    <div class="payment-badge"><i class="fas fa-mobile-alt"></i> GrabPay</div>
                    <div class="payment-badge"><i class="fas fa-credit-card"></i> Credit/Debit Card</div>
                </div>
            </div>

            {{-- Pay Button --}}
            <form action="{{ route('class.checkout.process', $classEvent->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-pay" id="payBtn"
                        onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Redirecting to payment...'; this.form.submit();">
                    <i class="fas fa-lock"></i>
                    Pay RM {{ number_format($classEvent->price, 2) }}
                </button>
            </form>

            <p class="secure-note">
                <i class="fas fa-shield-alt"></i>
                Secured by Stripe • 256-bit SSL encryption
            </p>

        </div>
    </div>
</div>
@endsection