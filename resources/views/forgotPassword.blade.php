@extends('layouts.app')

@section('title', 'Forgot Password')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="form-card">
        <img src="{{ asset('images/Logo.png') }}" alt="Craftistry" class="logo">

        <h1>Forgot Password?</h1>
        <p class="subtitle">Enter your email and we'll send you a reset link.</p>

        @if (session('status'))
            <div class="alert alert-success">
                <div class="alert-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="alert-content">{{ session('status') }}</div>
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

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="Enter your registered email" required>
            </div>

            <button type="submit" class="btn-primary">Send Reset Link</button>

            <p class="footer-text">
                Remembered it? <a href="{{ route('login') }}">Back to Login</a>
            </p>
        </form>
    </div>
</div>
@endsection