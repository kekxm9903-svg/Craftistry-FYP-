@extends('layouts.app')

@section('title', 'Studio')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/studio.css') }}">
@endsection

@section('content')
<main class="main" style="padding-top: 80px;">
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
    @endif

    @if($view === 'landing')
        <section class="studio-hero">
            <div class="hero-content">
                <h1>Welcome to Craftistry Studio</h1>
                <p class="subtitle">Your creative space to showcase and sell your art</p>
            </div>
        </section>

        <section class="studio-info">
            <h2>Become an Artist on Craftistry</h2>
            <p class="info-description">
                Join our community of talented Malaysian artists and share your creativity with art lovers across the country.
            </p>

            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon purple">
                        <i class="fas fa-store-alt"></i>
                    </div>
                    <h3>Your Own Studio</h3>
                    <p>Create your personalized artist profile and showcase your portfolio to potential buyers</p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon blue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Sell Your Art</h3>
                    <p>List your artwork, set your prices, and manage orders through our easy-to-use platform</p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon orange">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Custom Orders</h3>
                    <p>Accept custom commissions and work directly with clients on personalized projects</p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon green">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Build Community</h3>
                    <p>Connect with fellow artists, share techniques, and grow your following</p>
                </div>
            </div>
        </section>

        <section class="requirements">
            <h2>Artist Requirements</h2>
            <div class="requirements-list">
                <div class="requirement-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Must be a Malaysian citizen or resident</span>
                </div>
                <div class="requirement-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Provide a detailed bio about your artistic journey (minimum 50 words)</span>
                </div>
                <div class="requirement-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Select at least one artwork type you specialize in</span>
                </div>
                <div class="requirement-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Agree to our artist terms and conditions</span>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="cta-card">
                <h2>Ready to Share Your Art?</h2>
                <p>Start your journey as an artist on Craftistry today</p>
                <a href="{{ route('studio.register') }}" class="btn-primary btn-large">
                    <i class="fas fa-paint-brush"></i>
                    Register as an Artist
                </a>
            </div>
        </section>

    @elseif($view === 'register')
        <div class="register-container">
            <div class="register-header">
                <h1>Artist Registration</h1>
                <p>Tell us about yourself and your art</p>
            </div>

            @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('studio.register.submit') }}" class="artist-form">
                @csrf

                <div class="form-section">
                    <h2><i class="fas fa-user-edit"></i> About You</h2>
                    
                    <div class="form-group">
                        <label for="bio">Artist Bio <span class="required">*</span></label>
                        <p class="help-text">Tell buyers about your artistic journey, style, and inspiration (minimum 50 words)</p>
                        <textarea 
                            name="bio" 
                            id="bio" 
                            rows="6" 
                            placeholder="Example: I am a Malaysian artist specializing in traditional batik with a modern twist..."
                            required>{{ old('bio') }}</textarea>
                        <span class="char-count" id="bioCount">0 / 1000 characters</span>
                    </div>

                    <div class="form-group">
                        <label for="specialization">Primary Specialization</label>
                        <p class="help-text">What do you consider your main artistic focus?</p>
                        <input 
                            type="text" 
                            name="specialization" 
                            id="specialization" 
                            placeholder="e.g., Contemporary Batik Art, Traditional Woodcarving"
                            value="{{ old('specialization') }}">
                    </div>
                </div>

                <div class="form-section">
                    <h2><i class="fas fa-palette"></i> Artwork Types</h2>
                    <p class="help-text">Select all types of artwork you create (minimum 1 required)</p>
                    
                    <div class="artwork-types-grid">
                        @foreach($artworkTypes as $type)
                        <label class="artwork-type-card">
                            <input 
                                type="checkbox" 
                                name="artwork_types[]" 
                                value="{{ $type->id }}"
                                {{ in_array($type->id, old('artwork_types', [])) ? 'checked' : '' }}>
                            <div class="card-content">
                                <h3>{{ $type->name }}</h3>
                                <p>{{ $type->description }}</p>
                            </div>
                            <div class="checkmark">
                                <i class="fas fa-check"></i>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-section">
                    <h2><i class="fas fa-magic"></i> Custom Orders</h2>
                    
                    <label class="checkbox-card">
                        <input 
                            type="checkbox" 
                            name="allow_customization" 
                            value="1"
                            {{ old('allow_customization') ? 'checked' : '' }}>
                        <div class="checkbox-content">
                            <h3>I accept custom orders</h3>
                            <p>Allow buyers to request personalized artwork based on their preferences</p>
                        </div>
                    </label>
                </div>

                <div class="form-section">
                    <label class="checkbox-simple">
                        <input type="checkbox" id="terms" required>
                        <span>I agree to the <a href="#" target="_blank">Artist Terms & Conditions</a></span>
                    </label>
                </div>

                <div class="form-actions">
                    <a href="{{ route('studio') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Be an Artist
                    </button>
                </div>
            </form>
        </div>

    @elseif($view === 'dashboard')
        <div style="text-align: center; padding: 4rem;">
            <h1>🎨 Artist Dashboard</h1>
            <p>Welcome back, {{ Auth::user()->fullname }}!</p>
            <p>Your artist profile is: <strong>{{ $artist->verification_status }}</strong></p>
            <a href="{{ route('dashboard') }}" class="btn-secondary" style="display: inline-block; margin-top: 2rem;">Back to Dashboard</a>
        </div>
    @endif
</main>
@endsection

@section('scripts')
<script src="{{ asset('js/studio.js') }}"></script>
@endsection