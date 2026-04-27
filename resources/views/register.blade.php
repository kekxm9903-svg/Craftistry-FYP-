@extends('layouts.app')

@section('title', 'Register')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
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
                <label>State</label>
                <select name="location" required>
                    <option value="">Select your state</option>
                    @foreach (['johor','kedah','kelantan','kuala-lumpur','melaka','negeri-sembilan','pahang','penang','perak','perlis','sabah','sarawak','selangor','terengganu'] as $state)
                        <option value="{{ $state }}" {{ old('location') == $state ? 'selected' : '' }}>{{ ucfirst(str_replace('-', ' ', $state)) }}</option>
                    @endforeach
                </select>
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
                <span>I agree to the <a href="#">Terms & Conditions</a></span>
            </label>

            <button type="submit" class="btn-primary">Create Account</button>

            <p class="footer-text">
                Already have an account? <a href="{{ route('login') }}">Login</a>
            </p>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/register.js') }}"></script>
@endsection
