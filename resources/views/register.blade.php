@extends('layouts.app')

@section('title', 'Register')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
<style>
    .tnc-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .tnc-modal-overlay.active {
        display: flex;
    }
    .tnc-modal {
        background: #fff;
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
        height: 85vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .tnc-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        border-radius: 12px 12px 0 0;
    }
    .tnc-modal-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    .tnc-modal-close {
        background: none;
        border: none;
        color: #fff;
        font-size: 22px;
        cursor: pointer;
        line-height: 1;
        padding: 0 4px;
    }
    .tnc-modal-close:hover {
        opacity: 0.7;
    }
    .tnc-modal iframe {
        flex: 1;
        width: 100%;
        border: none;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="form-card">
        <img src="{{ asset('images/Logo.png') }}" alt="Craftistry" class="logo">

        <h1>Create Account</h1>
        <p class="subtitle">Join Craftistry today</p>

        @if ($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="registerForm" method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" value="{{ old('fullname') }}" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Enter your phone number" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="password-input">
                    <input type="password" name="password" id="password" placeholder="Create a password" required>
                    <button type="button" class="toggle-btn" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <div class="password-input">
                    <input type="password" name="password_confirmation" id="confirmPassword" placeholder="Confirm your password" required>
                    <button type="button" class="toggle-btn" id="toggleConfirmPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <label class="checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <span>I agree to the
                    <a href="#" id="openTnc">Terms &amp; Conditions</a>
                </span>
            </label>

            <button type="submit" class="btn-primary">Create Account</button>

            <p class="footer-text">
                Already have an account? <a href="{{ route('login') }}">Login</a>
            </p>
        </form>
    </div>
</div>

{{-- Terms & Conditions Modal --}}
<div class="tnc-modal-overlay" id="tncModal">
    <div class="tnc-modal">
        <div class="tnc-modal-header">
            <h5><i class="fas fa-file-alt me-2"></i> Terms &amp; Conditions</h5>
            <button class="tnc-modal-close" id="closeTnc">&times;</button>
        </div>
        <iframe src="{{ asset('documents/terms_and_conditions.pdf') }}" title="Terms and Conditions"></iframe>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/register.js') }}"></script>
<script>
    const tncModal   = document.getElementById('tncModal');
    const openTnc    = document.getElementById('openTnc');
    const closeTnc   = document.getElementById('closeTnc');

    openTnc.addEventListener('click', function (e) {
        e.preventDefault();
        tncModal.classList.add('active');
    });

    closeTnc.addEventListener('click', function () {
        tncModal.classList.remove('active');
    });

    // Close when clicking outside the modal box
    tncModal.addEventListener('click', function (e) {
        if (e.target === tncModal) {
            tncModal.classList.remove('active');
        }
    });
</script>
@endsection