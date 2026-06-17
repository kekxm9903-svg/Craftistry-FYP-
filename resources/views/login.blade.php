@extends('layouts.app')

@section('title', 'Login')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="form-card">
        <a href="{{ route('welcome') }}">
            <img src="{{ asset('images/Logo.png') }}" alt="Craftistry" class="logo">
        </a>

        <h1>Welcome Back!</h1>
        <p class="subtitle">Login to continue</p>

        {{-- Success message after password reset --}}
        @if (session('status'))
            <div class="alert alert-success">
                <div class="alert-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="alert-content">{{ session('status') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <div class="alert-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
                <div class="alert-content">{{ session('error') }}</div>
            </div>
        @endif

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

        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="password-input">
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    <button type="button" class="toggle-btn" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-primary">Login</button>

            <p class="footer-text">
                Don't have an account? <a href="{{ route('register') }}">Sign Up</a>
            </p>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/login.js') }}"></script>
@endsection