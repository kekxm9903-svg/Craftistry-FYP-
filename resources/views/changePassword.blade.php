@extends('layouts.app')

@section('title', 'Change Password')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/changePassword.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('user.profile.show') }}">My Profile</a>
        <span class="sep">/</span>
        <span class="cur">Change Password</span>
    </div>
</div>

{{-- Back button — identical placement to orderSummary --}}
<div style="max-width:860px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="{{ route('user.profile.show') }}" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back
    </a>
</div>

<div class="password-page">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ══ PAGE HEADER CARD ══ --}}
    <div class="password-header-card">
        <div class="password-header-left">
            <div class="password-title">Change Password</div>
            <div class="password-subtitle">Update your account security settings</div>
        </div>
    </div>

    <form action="{{ route('user.profile.update-password') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- ══ SECURITY INFORMATION CARD ══ --}}
        <div class="sp-card">
            <div class="sp-card-header">
                <div class="sp-card-header-left">
                    <div class="hline"></div>
                    Security Information
                </div>
            </div>
            <div class="sp-card-body">

                {{-- Current Password --}}
                <div class="form-group">
                    <label for="current_password" class="form-label">
                        Current Password <span class="req">*</span>
                    </label>
                    <div class="form-input-wrapper">
                        <input type="password" name="current_password" id="current_password"
                               class="form-input" required placeholder="Enter current password">
                        <button type="button" class="password-toggle"
                                onclick="togglePassword('current_password')">
                            <i class="fas fa-eye" id="current_password-icon"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div class="form-group">
                    <label for="password" class="form-label">
                        New Password <span class="req">*</span>
                    </label>
                    <div class="form-input-wrapper">
                        <input type="password" name="password" id="password"
                               class="form-input" required placeholder="Enter new password">
                        <button type="button" class="password-toggle"
                                onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                    <p class="helper-text">Minimum 8 characters</p>
                    @error('password')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        Confirm New Password <span class="req">*</span>
                    </label>
                    <div class="form-input-wrapper">
                        <input type="password" name="password_confirmation"
                               id="password_confirmation"
                               class="form-input" required placeholder="Re-enter new password">
                        <button type="button" class="password-toggle"
                                onclick="togglePassword('password_confirmation')">
                            <i class="fas fa-eye" id="password_confirmation-icon"></i>
                        </button>
                    </div>
                </div>

                {{-- Info box --}}
                <div class="info-box">
                    <div class="info-box-title">
                        <i class="fas fa-info-circle"></i> Password Requirements
                    </div>
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>Mix of uppercase and lowercase letters recommended</li>
                        <li>Include numbers and special characters for better security</li>
                    </ul>
                </div>

            </div>

            {{-- ══ FORM ACTIONS FOOTER (inside card, below sp-card-body) ══ --}}
            <div class="form-actions">
                <a href="{{ route('user.profile.show') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-lock"></i> Update Password
                </button>
            </div>
        </div>

    </form>
</div>

@endsection

@section('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon  = document.getElementById(fieldId + '-icon');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endsection