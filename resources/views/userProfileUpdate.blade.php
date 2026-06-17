@extends('layouts.app')

@section('title', 'Edit Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/userProfileUpdate.css') }}">

<style>
.success-popup,
.delete-popup {
    display: none;
    position: fixed;
    top: 80px;
    right: var(--sp-lg, 20px);
    z-index: 999;
}
.success-popup.show,
.delete-popup.show { display: block; animation: slideInRight .3s ease-out; }
.success-popup.hide,
.delete-popup.hide { animation: slideOutRight .3s ease-out forwards; }
.success-content {
    background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 10px;
    padding: 16px 20px; box-shadow: 0 4px 12px rgba(16,185,129,.15);
    display: flex; align-items: center; gap: 10px; min-width: 280px;
}
.delete-content {
    background: #fee2e2; border: 1px solid #fca5a5; border-radius: 10px;
    padding: 16px 20px; box-shadow: 0 4px 12px rgba(239,68,68,.15);
    display: flex; align-items: center; gap: 10px; min-width: 280px;
}
.success-icon { font-size: 15px; color: #065f46; flex-shrink: 0; }
.delete-icon  { font-size: 15px; color: #991b1b; flex-shrink: 0; }
.success-content p { font-size: 13px; font-weight: 600; color: #065f46; margin: 0; }
.delete-content  p { font-size: 13px; font-weight: 600; color: #991b1b; margin: 0; }
@keyframes slideInRight  { from { opacity:0; transform:translateX(360px); } to { opacity:1; transform:translateX(0); } }
@keyframes slideOutRight { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(360px); } }
@media (max-width: 768px) {
    .success-popup, .delete-popup { top:10px; right:10px; left:10px; }
    .success-content, .delete-content { min-width:auto; }
}
</style>
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

{{-- Back button --}}
<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="{{ route('user.profile.show') }}" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back
    </a>
</div>

<div class="profile-page">

    {{-- Validation errors stay inline next to the form --}}
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

                    {{-- Email: read-only, no input element --}}
                    <div class="info-item">
                        <label class="info-label">Email Address</label>
                        <p class="form-input" style="margin:0;background:#f3f4f6;color:#6b7280;cursor:default;user-select:none;">
                            {{ $user->email }}
                        </p>
                        <p style="font-size:11.5px;color:#9ca3af;margin-top:5px;">
                            Email address cannot be changed.
                        </p>
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

            {{-- ══ FORM ACTIONS FOOTER ══ --}}
            <div class="form-actions">
                <a href="{{ route('user.profile.show') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>

        </div>

    </form>
</div>

{{-- Success Toast --}}
<div class="success-popup" id="successPopup">
    <div class="success-content">
        <div class="success-icon"><i class="fas fa-check-circle"></i></div>
        <div><p id="successMessage">Success!</p></div>
    </div>
</div>

{{-- Error Toast --}}
<div class="delete-popup" id="errorPopup">
    <div class="delete-content">
        <div class="delete-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div><p id="errorMessage">Something went wrong.</p></div>
    </div>
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

// ── Success / Error toast ──
(function () {
    function showSuccessPopup(msg) {
        var p = document.getElementById('successPopup');
        var m = document.getElementById('successMessage');
        if (!p || !m) return;
        m.textContent = msg;
        p.classList.add('show');
        setTimeout(function () { p.classList.add('hide'); setTimeout(function () { p.classList.remove('show','hide'); }, 300); }, 3000);
    }
    function showErrorPopup(msg) {
        var p = document.getElementById('errorPopup');
        var m = document.getElementById('errorMessage');
        if (!p || !m) return;
        m.textContent = msg;
        p.classList.add('show');
        setTimeout(function () { p.classList.add('hide'); setTimeout(function () { p.classList.remove('show','hide'); }, 300); }, 3000);
    }
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success')) showSuccessPopup(@json(session('success'))); @endif
        @if(session('error'))   showErrorPopup(@json(session('error')));     @endif
    });
})();
</script>
@endsection