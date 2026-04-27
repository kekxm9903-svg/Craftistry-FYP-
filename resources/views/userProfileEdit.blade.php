@extends('layouts.app')

@section('title', 'Edit Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistProfile.css') }}">
<link rel="stylesheet" href="{{ asset('css/userProfileEdit.css') }}">
@endsection

@section('content')
<header class="header">
    <div class="header-left">
        <img src="{{ asset('images/Logo.png') }}" alt="Craftistry">
    </div>

    <nav class="nav">
        <a href="{{ url('/dashboard') }}" class="nav-link">
            <i class="fas fa-th-large"></i> <span>Dashboard</span>
        </a>
        <a href="#" class="nav-link">
            <i class="fas fa-palette"></i> <span>Artist</span>
        </a>
        <a href="#" class="nav-link">
            <i class="fas fa-graduation-cap"></i> <span>Class</span>
        </a>
        <a href="{{ route('artist.profile') }}" class="nav-link {{ request()->is('artist/studio*') ? 'active' : '' }}">
            <i class="fas fa-store"></i> <span>Studio</span>
        </a>
        <a href="{{ route('user.profile.show') }}" class="nav-link active">
            <i class="fas fa-user"></i> <span>Profile</span>
        </a>
    </nav>

    <div class="navbar-right">
        <a href="{{ route('user.profile.show') }}" class="profile-avatar">
            @if(Auth::user()->profile_image)
                <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile" class="avatar-image">
            @else
                <div class="avatar-placeholder-nav">
                    {{ strtoupper(substr(Auth::user()->fullname, 0, 1)) }}
                </div>
            @endif
        </a>
    </div>
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
</header>

<main class="main" style="max-width: 1000px; margin: 0 auto; padding: 20px; padding-top: 100px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 4px;">Edit Profile</h1>
            <p style="color: #6b7280; font-size: 14px;">Update your personal information</p>
        </div>
        
        <a href="{{ route('user.profile.show') }}" class="btn-secondary" style="text-decoration: none; padding: 10px 24px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; cursor: pointer;">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>

    @if(session('success'))
        <div style="background-color: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-header-card">
        @csrf
        @method('PUT')
        
        <!-- Hidden input for removal flag -->
        <input type="hidden" name="remove_profile_picture" id="remove_profile_picture" value="0">

        <h2 class="section-header"><i class="fas fa-camera"></i> Profile Picture</h2>
        
        <div class="avatar-upload-row">
            <div id="image-preview-container">
                @if($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->fullname }}" class="avatar-preview" id="preview-img-tag">
                @else
                    <div class="avatar-placeholder" id="preview-placeholder">
                        {{ strtoupper(substr($user->fullname, 0, 1)) }}
                    </div>
                    <img src="" class="avatar-preview" id="preview-img-tag" style="display: none;"> 
                @endif
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <label for="profile_picture" class="btn-primary" style="cursor: pointer; padding: 10px 24px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
                    <i class="fas fa-upload"></i> Change Photo
                </label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;">
                
                <p style="font-size: 13px; color: #6b7280; margin: 0;">JPG, PNG or GIF (Max 2MB)</p>
                @error('profile_picture')
                    <p class="error-msg">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <h2 class="section-header" style="margin-top: 40px;"><i class="fas fa-user-edit"></i> Personal Details</h2>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Full Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="fullname" value="{{ old('fullname', $user->fullname) }}" required class="form-input">
                @error('fullname') <p class="error-msg">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Email Address <span style="color: #ef4444;">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
                @error('email') <p class="error-msg">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+60123456789" class="form-input">
                @error('phone') <p class="error-msg">{{ $message }}</p> @enderror
            </div>

            <div class="form-group form-full-width">
                <label class="form-label">Street Address</label>
                <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="123 Jalan Merdeka" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">City</label>
                <input type="text" name="city" value="{{ old('city', $user->city) }}" placeholder="Kuala Lumpur" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">State</label>
                <select name="state" class="form-input">
                    <option value="">Select State</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" {{ old('state', $user->state) == $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Postcode</label>
                <input type="text" name="postcode" value="{{ old('postcode', $user->postcode) }}" placeholder="50000" maxlength="5" class="form-input">
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 40px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
            <a href="{{ route('user.profile.show') }}" class="btn-secondary" style="text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: 500; cursor: pointer; display: inline-block; background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb;">
                Cancel
            </a>
            <button type="submit" class="btn-primary" style="padding: 12px 30px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer;">
                Save Changes
            </button>
        </div>

    </form>
</main>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview functionality
    const profilePictureInput = document.getElementById('profile_picture');
    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const placeholder = document.getElementById('preview-placeholder');
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    
                    const img = document.getElementById('preview-img-tag');
                    if (img) {
                        img.src = e.target.result;
                        img.style.display = 'block';
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
@endsection