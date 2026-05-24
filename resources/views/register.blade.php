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
    .tnc-modal-close:hover { opacity: 0.7; }
    .tnc-modal iframe {
        flex: 1;
        width: 100%;
        border: none;
    }

    /* Phone input with prefix */
    .phone-input-wrap {
        display: flex;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        transition: border-color .2s, box-shadow .2s;
    }
    .phone-input-wrap:focus-within {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102,126,234,.1);
    }
    .phone-prefix {
        display: flex;
        align-items: center;
        padding: 0 12px;
        background: #f3f4f6;
        border-right: 2px solid #e2e8f0;
        font-size: 14px;
        font-weight: 600;
        color: #4a5568;
        white-space: nowrap;
        user-select: none;
        flex-shrink: 0;
    }
    .phone-input-wrap input {
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        flex: 1;
        min-width: 0;
        padding: 12px 16px;
    }
    .phone-input-wrap input:focus {
        outline: none;
        box-shadow: none !important;
    }
    .phone-error {
        font-size: 12px;
        color: #e53e3e;
        margin-top: 5px;
        display: none;
    }
    .phone-error.show { display: block; }
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
                {{-- Hidden field that stores the full number (+60...) sent to server --}}
                <input type="hidden" name="phone" id="phoneFull">
                <div class="phone-input-wrap">
                    <span class="phone-prefix">🇲🇾 +60</span>
                    <input type="tel"
                           id="phoneLocal"
                           placeholder="123456789"
                           maxlength="10"
                           autocomplete="tel"
                           inputmode="numeric">
                </div>
                <span class="phone-error" id="phoneError">Please enter a valid Malaysian phone number (e.g. 12-3456789).</span>
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
    // ── T&C Modal ──
    const tncModal = document.getElementById('tncModal');
    document.getElementById('openTnc').addEventListener('click', function (e) {
        e.preventDefault();
        tncModal.classList.add('active');
    });
    document.getElementById('closeTnc').addEventListener('click', function () {
        tncModal.classList.remove('active');
    });
    tncModal.addEventListener('click', function (e) {
        if (e.target === tncModal) tncModal.classList.remove('active');
    });
</script>
@endsection