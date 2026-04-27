@extends('layouts.app')

@section('title', 'Edit Artist Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistEditProfile.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <span class="cur">Edit Profile</span>
    </div>
</div>

{{-- Back button --}}
<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="{{ route('artist.profile') }}" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Studio
    </a>
</div>

<div class="edit-page">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="success-toast" id="successToast">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
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

    <form action="{{ route('artist.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- ══ PAGE HEADER CARD ══ --}}
        <div class="edit-header-card">
            <div class="edit-header-left">
                <div class="edit-title">Edit Artist Profile</div>
                <div class="edit-subtitle">Update your studio information and specializations</div>
            </div>
            <button type="submit" class="btn-save-top">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>

        {{-- ══ PROFILE PICTURE CARD ══ --}}
        <div class="sp-card">
            <div class="sp-card-header">
                <div class="sp-card-header-left">
                    <div class="hline"></div>
                    Profile Picture
                </div>
            </div>
            <div class="sp-card-body">
                <div class="avatar-row">

                    {{-- Avatar preview with camera button --}}
                    <div class="avatar-preview">
                        <div class="avatar-circle">
                            @if($user->profile_image && $user->profile_image !== 'images/Profile.png')
                                <img id="profilePreview"
                                     src="{{ asset('storage/' . $user->profile_image) }}?v={{ time() }}"
                                     alt="{{ $user->fullname }}"
                                     class="avatar-img"
                                     onerror="this.style.display='none'; document.getElementById('profilePreviewPlaceholder').style.display='flex';">
                                <div class="avatar-letter" id="profilePreviewPlaceholder" style="display:none;">
                                    {{ strtoupper(substr($user->fullname, 0, 1)) }}
                                </div>
                            @else
                                <div class="avatar-letter" id="profilePreviewPlaceholder">
                                    {{ strtoupper(substr($user->fullname, 0, 1)) }}
                                </div>
                                <img id="profilePreview" src="" class="avatar-img" style="display:none;">
                            @endif
                        </div>
                        <label for="profile_image" class="avatar-camera-btn" title="Change Photo">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="profile_image" name="profile_image"
                               accept="image/jpeg,image/jpg,image/png,image/webp"
                               style="display:none;">
                        <input type="hidden" id="remove_profile_image" name="remove_profile_image" value="0">
                    </div>

                    {{-- Upload controls --}}
                    <div class="avatar-controls">
                        <div class="avatar-actions">
                            <label for="profile_image" class="btn-upload">
                                <i class="fas fa-camera"></i> Choose Photo
                            </label>
                            @if($user->profile_image && $user->profile_image !== 'images/Profile.png')
                                <button type="button" class="btn-remove" id="removeBtn"
                                        onclick="removeProfilePicture()">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            @endif
                        </div>
                        <div class="avatar-hint">
                            <i class="fas fa-info-circle"></i> JPEG, JPG, PNG, WEBP — max 5MB
                        </div>
                    </div>

                </div>
                @error('profile_image')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- ══ BASIC INFORMATION CARD ══ --}}
        <div class="sp-card">
            <div class="sp-card-header">
                <div class="sp-card-header-left">
                    <div class="hline"></div>
                    Basic Information
                </div>
            </div>
            <div class="sp-card-body">

                <div class="form-group">
                    <label class="form-label">Full Name <span class="req">*</span></label>
                    <input type="text" name="fullname" id="fullname"
                           value="{{ old('fullname', $user->fullname) }}"
                           required maxlength="255"
                           class="form-input"
                           placeholder="Your full name">
                    @error('fullname')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Specialization <span class="req">*</span></label>
                    <input type="text" name="specialization" id="specialization"
                           value="{{ old('specialization', $artist->specialization) }}"
                           required maxlength="255"
                           class="form-input"
                           placeholder="e.g., Portrait Artist, Digital Illustrator">
                    @error('specialization')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Bio <span class="req">*</span></label>
                    <textarea name="bio" id="bio"
                              rows="6" required maxlength="1000"
                              class="form-input"
                              placeholder="Tell us about yourself, your art style, experience, and what makes your work unique...">{{ old('bio', $artist->bio) }}</textarea>
                    <div class="char-count">
                        <span id="bioCount">{{ strlen(old('bio', $artist->bio)) }}</span> / 1000 characters
                    </div>
                    @error('bio')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ══ ARTWORK TYPES CARD ══ --}}
        <div class="sp-card">
            <div class="sp-card-header">
                <div class="sp-card-header-left">
                    <div class="hline"></div>
                    Artwork Types <span class="req" style="margin-left:4px;">*</span>
                </div>
            </div>
            <div class="sp-card-body">
                <p class="section-desc">Select the types of artwork you specialize in (choose at least one)</p>
                <div class="artwork-types-grid">
                    @foreach($artworkTypes as $type)
                    <label class="artwork-type-card">
                        <input type="checkbox"
                               name="artwork_types[]"
                               value="{{ $type->id }}"
                               {{ in_array($type->id, old('artwork_types', $artist->artworkTypes->pluck('id')->toArray())) ? 'checked' : '' }}>
                        <div class="type-card-inner">
                            <i class="fas fa-{{ $type->icon ?? 'palette' }}"></i>
                            <span>{{ $type->name }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('artwork_types')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
            </div>

            {{-- ══ FORM ACTIONS FOOTER ══ --}}
            <div class="form-actions">
                <a href="{{ route('artist.profile') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>

    </form>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/editArtistProfile.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Bio character count ──
    const bio      = document.getElementById('bio');
    const bioCount = document.getElementById('bioCount');
    if (bio && bioCount) {
        bio.addEventListener('input', () => {
            bioCount.textContent = bio.value.length;
        });
    }

    // ── Profile image preview ──
    const input       = document.getElementById('profile_image');
    const preview     = document.getElementById('profilePreview');
    const placeholder = document.getElementById('profilePreviewPlaceholder');

    if (input) {
        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (ev) {
                if (placeholder) placeholder.style.display = 'none';
                if (preview)     { preview.src = ev.target.result; preview.style.display = 'block'; }
                document.getElementById('remove_profile_image').value = '0';
            };
            reader.readAsDataURL(file);
        });
    }

    // ── Success toast auto-dismiss ──
    const toast = document.getElementById('successToast');
    if (toast) {
        setTimeout(() => {
            toast.classList.add('slide-out');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

});

function removeProfilePicture() {
    const preview     = document.getElementById('profilePreview');
    const placeholder = document.getElementById('profilePreviewPlaceholder');
    const input       = document.getElementById('profile_image');
    const removeInput = document.getElementById('remove_profile_image');
    const removeBtn   = document.getElementById('removeBtn');

    if (preview)     { preview.style.display = 'none'; preview.src = ''; }
    if (placeholder) placeholder.style.display = 'flex';
    if (input)       input.value = '';
    if (removeInput) removeInput.value = '1';
    if (removeBtn)   removeBtn.style.display = 'none';
}
</script>
@endsection