@extends('layouts.app')

@section('title', 'Edit Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/userProfileUpdate.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('user.profile.show') }}">My Profile</a>
        <span class="sep">/</span>
        <span class="cur">Edit Profile</span>
    </div>
</div>

{{-- Back button — identical placement to orderSummary --}}
<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="{{ route('user.profile.show') }}" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back
    </a>
</div>

<div class="profile-page">

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

    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="remove_profile_picture" id="remove_profile_picture" value="0">

        {{-- ══ PROFILE HEADER CARD ══ --}}
        <div class="sp-card profile-header-card">
            <div class="profile-top">

                {{-- Avatar --}}
                <div class="profile-avatar-wrap">
                    <div class="avatar-circle" id="image-preview-container">
                        @if($user->profile_image)
                            <img src="{{ asset('storage/' . $user->profile_image) }}?v={{ time() }}"
                                 alt="{{ $user->fullname }}"
                                 class="avatar-img"
                                 id="preview-img-tag">
                            <div class="avatar-letter" id="preview-placeholder" style="display:none;">
                                {{ strtoupper(substr($user->fullname, 0, 1)) }}
                            </div>
                        @else
                            <div class="avatar-letter" id="preview-placeholder">
                                {{ strtoupper(substr($user->fullname, 0, 1)) }}
                            </div>
                            <img src="" class="avatar-img" id="preview-img-tag" style="display:none;">
                        @endif
                    </div>
                    <label for="profile_picture" class="avatar-camera-btn" title="Change Photo">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="profile_picture" name="profile_picture"
                           accept="image/*" style="display:none;">
                </div>

                {{-- Name + email + hint --}}
                <div class="profile-identity">
                    <div class="artist-name">{{ $user->fullname }}</div>
                    <div class="artist-specialization">{{ $user->email }}</div>
                    <div class="avatar-hint" style="margin-top:4px;">
                        <i class="fas fa-camera"></i> Click avatar to change photo
                        @if($user->profile_image)
                            &nbsp;·&nbsp;
                            <span class="remove-photo-link" id="remove-photo-btn">Remove photo</span>
                        @endif
                    </div>
                </div>

                {{-- Save button (top-right of header card) --}}
                <button type="submit" class="btn-edit-profile">
                    <i class="fas fa-save"></i> Save Changes
                </button>

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
                        <label class="info-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="fullname"
                               value="{{ old('fullname', $user->fullname) }}"
                               required class="form-input"
                               placeholder="Your full name">
                        @error('fullname')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>

                    <div class="info-item">
                        <label class="info-label">Email Address <span class="req">*</span></label>
                        <input type="email" name="email"
                               value="{{ old('email', $user->email) }}"
                               required class="form-input"
                               placeholder="you@email.com">
                        @error('email')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>

                    <div class="info-item">
                        <label class="info-label">Phone Number</label>
                        <input type="text" name="phone"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="+60123456789" class="form-input">
                        @error('phone')<p class="error-msg">{{ $message }}</p>@enderror
                    </div>

                    <div class="info-item info-full">
                        <label class="info-label">Street Address</label>
                        <input type="text" name="address"
                               value="{{ old('address', $user->address) }}"
                               placeholder="123 Jalan Merdeka" class="form-input">
                    </div>

                    <div class="info-item">
                        <label class="info-label">City</label>
                        <input type="text" name="city"
                               value="{{ old('city', $user->city) }}"
                               placeholder="Kuala Lumpur" class="form-input">
                    </div>

                    <div class="info-item">
                        <label class="info-label">State</label>
                        <select name="state" class="form-input">
                            <option value="">Select State</option>
                            @foreach($states as $state)
                                <option value="{{ $state }}"
                                    {{ old('state', $user->state) == $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="info-item">
                        <label class="info-label">Postcode</label>
                        <input type="text" name="postcode"
                               value="{{ old('postcode', $user->postcode) }}"
                               placeholder="50000" maxlength="5" class="form-input">
                    </div>

                </div>
            </div>

            {{-- ══ FORM ACTIONS FOOTER (inside card, below sp-card-body) ══ --}}
            <div class="form-actions">
                <a href="{{ route('user.profile.show') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>

        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input       = document.getElementById('profile_picture');
    const removeBtn   = document.getElementById('remove-photo-btn');
    const removeInput = document.getElementById('remove_profile_picture');
    const previewImg  = document.getElementById('preview-img-tag');
    const placeholder = document.getElementById('preview-placeholder');
    const userInitial = '{{ strtoupper(substr($user->fullname, 0, 1)) }}';

    // Preview new photo
    if (input) {
        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (ev) {
                if (placeholder) placeholder.style.display = 'none';
                if (previewImg)  { previewImg.src = ev.target.result; previewImg.style.display = 'block'; }
                if (removeInput) removeInput.value = '0';
            };
            reader.readAsDataURL(file);
        });
    }

    // Remove photo
    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            if (previewImg)  { previewImg.style.display = 'none'; previewImg.src = ''; }
            if (placeholder) { placeholder.style.display = 'flex'; placeholder.textContent = userInitial; }
            if (input)       input.value = '';
            if (removeInput) removeInput.value = '1';
            removeBtn.style.display = 'none';
        });
    }
});
</script>
@endsection