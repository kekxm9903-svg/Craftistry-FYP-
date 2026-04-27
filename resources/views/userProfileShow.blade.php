@extends('layouts.app')

@section('title', 'My Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/userProfileShow.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">My Profile</span>
    </div>
</div>

<div class="profile-page">

    {{-- Success toast --}}
    @if(session('success'))
        <div class="success-toast" id="successAlert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ══ PROFILE HEADER CARD ══ --}}
    <div class="sp-card profile-header-card">

        {{-- Avatar + name row --}}
        <div class="profile-top">
            <div class="profile-avatar-wrap">
                @if($user->profile_image && $user->profile_image !== 'images/Profile.png')
                    <img src="{{ asset('storage/' . $user->profile_image) }}?v={{ time() }}"
                         alt="{{ $user->fullname }}"
                         class="profile-avatar-img"
                         onerror="this.style.display='none'; document.getElementById('avatar-placeholder').style.display='flex';">
                    <div class="profile-avatar-letter" id="avatar-placeholder" style="display:none;">
                        {{ strtoupper(substr($user->fullname, 0, 1)) }}
                    </div>
                @else
                    <div class="profile-avatar-letter">
                        {{ strtoupper(substr($user->fullname, 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="profile-identity">
                <div class="profile-name">{{ $user->fullname }}</div>
                <div class="profile-email">{{ $user->email }}</div>
            </div>

            <a href="{{ route('user.profile.edit') }}" class="btn-edit-profile">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
        </div>

    </div>

    {{-- ══ PERSONAL INFORMATION CARD ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Personal Information
            </div>
        </div>
        <div class="sp-card-body">
            <div class="info-grid">

                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">{{ $user->fullname }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $user->email }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">{{ $user->phone ?? '—' }}</div>
                </div>

                <div class="info-item info-full">
                    <div class="info-label">Street Address</div>
                    <div class="info-value">{{ $user->address ?? '—' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">City</div>
                    <div class="info-value">{{ $user->city ?? '—' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">State</div>
                    <div class="info-value">{{ $user->state ?? '—' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Postcode</div>
                    <div class="info-value">{{ $user->postcode ?? '—' }}</div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══ SECURITY CARD ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Security
            </div>
        </div>
        <div class="sp-card-body">
            <div class="security-row">
                <div class="security-info">
                    <div class="security-title">Password</div>
                    <div class="security-desc">Manage your account password</div>
                </div>
                <a href="{{ route('user.profile.change-password') }}" class="btn-change-pw">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="{{ asset('js/userProfileShow.js') }}"></script>
<script>
    // Auto-hide success toast
    const toast = document.getElementById('successAlert');
    if (toast) {
        setTimeout(() => {
            toast.style.animation = 'slideOutRight .4s ease-in forwards';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }
</script>
@endsection