@extends('layouts.app')

@section('title', 'Reset Password')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="form-card">
        <img src="{{ asset('images/Logo.png') }}" alt="Craftistry" class="logo">

        <h1>Reset Password</h1>
        <p class="subtitle">Enter your new password below.</p>

        @if ($errors->any())
            <div class="alert alert-error">
                <div class="alert-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
                <div class="alert-content">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group">
                <label>New Password</label>
                <div class="password-input">
                    <input type="password" name="password" id="password"
                           placeholder="Min. 8 characters" required>
                    <button type="button" class="toggle-btn" onclick="togglePass('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="password-input">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           placeholder="Re-enter new password" required>
                    <button type="button" class="toggle-btn" onclick="togglePass('password_confirmation', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary">Reset Password</button>

            <p class="footer-text">
                Back to <a href="{{ route('login') }}">Login</a>
            </p>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
@endsection