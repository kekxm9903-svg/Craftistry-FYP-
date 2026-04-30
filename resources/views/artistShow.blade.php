@extends('layouts.app')

@section('title', ($user->fullname ?? $user->name) . ' - Artist Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistShow.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')

{{-- Breadcrumb bar --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.browse') }}">Artists</a>
        <span class="sep">/</span>
        <span class="cur">{{ Str::limit($user->fullname ?? $user->name, 40) }}</span>
    </div>
</div>

<div class="profile-container">

    {{-- ══ PROFILE HEADER CARD ══ --}}
    <div class="profile-header">

        {{-- Row 1: Avatar + Name + Specialization + Artwork Types --}}
        <div class="profile-top">
            <div class="artist-avatar">
                @if($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}"
                         alt="{{ $user->fullname ?? $user->name }}"
                         class="artist-avatar-img">
                @else
                    <div class="avatar-placeholder">
                        {{ strtoupper(substr($user->fullname ?? $user->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="profile-identity">
                <h1 class="artist-name">{{ $user->fullname ?? $user->name ?? 'Unknown Artist' }}</h1>

                {{-- Specialization --}}
                @if($user->artist && $user->artist->specialization)
                    <div class="artist-specialization">
                        <i class="fas fa-star"></i>
                        <span class="specialization-label">Specialization:</span>
                        {{ $user->artist->specialization }}
                    </div>
                @endif

                {{-- Artwork Types --}}
                @if($user->artist && $user->artist->artworkTypes->count() > 0)
                    <div class="artist-artwork-types">
                        <i class="fas fa-tags"></i>
                        @foreach($user->artist->artworkTypes as $artworkType)
                            <span class="artwork-type-tag">{{ $artworkType->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Row 2: Stats + Action Buttons --}}
        <div class="profile-actions-row">
            <div class="artist-meta">
                <div class="meta-item">
                    <strong>{{ number_format($user->seller_rating, 1) }}</strong>
                    <span>Rating</span>
                </div>
                <div class="meta-divider"></div>
                <div class="meta-item">
                    <strong>{{ $user->artist->demoArtworks->count() }}</strong>
                    <span>Demo</span>
                </div>
                <div class="meta-divider"></div>
                <div class="meta-item">
                    <strong>{{ $user->artist->artworkSells->count() }}</strong>
                    <span>Artworks</span>
                </div>
            </div>

            <div class="action-buttons">
            @auth
                <a href="{{ route('custom-orders.create', $user->id) }}" class="btn-request">
                    <i class="fas fa-paper-plane"></i> Request
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-request">
                    <i class="fas fa-paper-plane"></i> Request
                </a>
            @endauth

                @auth
                    @php
                        $isFavorited = auth()->user()->favorites()->where('artist_id', $user->id)->exists();
                    @endphp
                    <button
                        id="btn-favorite"
                        class="btn-favorite {{ $isFavorited ? 'active' : '' }}"
                        data-url="{{ route('artist.favorite', $user->id) }}"
                        data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                        aria-label="{{ $isFavorited ? 'Remove from favourites' : 'Add to favourites' }}"
                    >
                        <i class="{{ $isFavorited ? 'fas' : 'far' }} fa-heart"></i>
                        <span>{{ $isFavorited ? 'Favourited' : 'Favourite' }}</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="btn-favorite">
                        <i class="far fa-heart"></i>
                        <span>Favourite</span>
                    </a>
                @endauth

                <div class="dropdown-more" id="dropdown-more">
                    <button class="btn-more" id="btn-more" aria-label="More options" aria-expanded="false" aria-haspopup="true">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <div class="dropdown-menu-more" id="dropdown-menu" role="menu">
                        <button class="dropdown-item" id="btn-copy-link" role="menuitem">
                            <i class="fas fa-link"></i>
                            <span>Copy Profile Link</span>
                        </button>
                        <div class="dropdown-divider"></div>
                        @auth
                            <button class="dropdown-item dropdown-item--danger" id="btn-report" data-url="{{ route('artist.report', $user->id) }}" role="menuitem">
                                <i class="fas fa-flag"></i>
                                <span>Report Artist</span>
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="dropdown-item dropdown-item--danger" role="menuitem">
                                <i class="fas fa-flag"></i>
                                <span>Report Artist</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Bio only --}}
        @if($user->artist && $user->artist->bio)
        <div class="profile-body">
            <div class="bio-label">About</div>
            <p class="bio-content">{{ $user->artist->bio }}</p>
        </div>
        @endif

    </div>

    {{-- ══ DEMO ARTWORKS ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Demo Artworks
            </div>
            @if($user->demoArtworks->count() > 0)
                <span class="section-count">{{ $user->demoArtworks->count() }} items</span>
            @endif
        </div>

        <div class="sp-card-body">
            @if($user->demoArtworks->count() > 0)
                <div class="demo-grid">
                    @foreach($user->demoArtworks as $demo)
                        <div class="demo-item">
                            <div class="demo-image">
                                <img src="{{ asset('storage/' . $demo->image_path) }}"
                                     alt="{{ $demo->title }}">
                            </div>
                            <div class="demo-info">
                                <div class="demo-title">{{ $demo->title }}</div>
                                @if($demo->description)
                                    <div class="demo-description">{{ $demo->description }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-palette"></i>
                    <h3>No Demo Artworks</h3>
                    <p>This artist hasn't uploaded any demo artworks yet.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ══ ARTWORKS FOR SALE ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Artworks For Sale
            </div>
            @if($user->artworkSells->count() > 0)
                <span class="section-count">{{ $user->artworkSells->count() }} items</span>
            @endif
        </div>

        <div class="sp-card-body">
            @if($user->artworkSells->count() > 0)
                <div class="sell-grid">
                    @foreach($user->artworkSells as $artwork)
                    @php
                        $isSold = $artwork->status == 'sold';
                        $statusIcon = match($artwork->status) {
                            'sold'      => 'times-circle',
                            'available' => 'check-circle',
                            'pending'   => 'clock',
                            'reserved'  => 'bookmark',
                            default     => 'check-circle'
                        };
                    @endphp
                    <a href="{{ route('product.show', $artwork->id) }}"
                       class="sell-item {{ $isSold ? 'sold' : '' }}">
                        <div class="sell-image">
                            <img src="{{ asset('storage/' . $artwork->image_path) }}"
                                 alt="{{ $artwork->product_name }}">
                            @if($isSold)
                                <div class="sold-overlay">
                                    SOLD OUT
                                    <div class="thank-you">Thank you for supporting</div>
                                </div>
                            @endif
                        </div>
                        <div class="sell-details">
                            <h3 class="product-name">{{ $artwork->product_name ?? 'Untitled Artwork' }}</h3>
                            <div class="product-price">
                                @if($artwork->product_price)
                                    RM {{ number_format($artwork->product_price, 2) }}
                                @else
                                    <span style="color:var(--muted);font-size:12px;">Price not set</span>
                                @endif
                            </div>
                            @if($artwork->artwork_type)
                                <div class="artwork-type-badge">{{ $artwork->artwork_type }}</div>
                            @endif
                            <div class="sell-meta">
                                @if($artwork->material)
                                    <div class="meta-item">
                                        <i class="fas fa-layer-group"></i>
                                        <span class="meta-label">Material:</span>
                                        <span class="meta-value">{{ $artwork->material }}</span>
                                    </div>
                                @endif
                                @if($artwork->width && $artwork->height)
                                    <div class="meta-item">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span class="meta-label">Size:</span>
                                        <span class="meta-value">
                                            {{ $artwork->width }}×{{ $artwork->height }}
                                            @if($artwork->depth)×{{ $artwork->depth }}@endif
                                            {{ $artwork->unit ?? 'cm' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="status-section">
                                <span class="status-badge status-{{ $artwork->status ?? 'available' }}">
                                    <i class="fas fa-{{ $statusIcon }}"></i>
                                    {{ $artwork->status ? ucfirst($artwork->status) : 'Available' }}
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No Artworks For Sale</h3>
                    <p>This artist doesn't have any artworks listed for sale.</p>
                </div>
            @endif
        </div>
    </div>

</div>{{-- /profile-container --}}

{{-- Toast --}}
<div class="fav-toast" id="fav-toast" role="status" aria-live="polite"></div>

{{-- Report Modal --}}
<div class="report-modal-overlay" id="report-modal-overlay" aria-hidden="true">
    <div class="report-modal" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
        <div class="report-modal-header">
            <h3 id="report-modal-title"><i class="fas fa-flag"></i> Report Artist</h3>
            <button class="report-modal-close" id="report-modal-close" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="report-modal-body">
            <p class="report-modal-subtitle">Why are you reporting this artist?</p>
            <div class="report-reasons">
                <label class="report-reason-option">
                    <input type="radio" name="report_reason" value="inappropriate_content">
                    <span><i class="fas fa-ban"></i> Inappropriate content</span>
                </label>
                <label class="report-reason-option">
                    <input type="radio" name="report_reason" value="spam">
                    <span><i class="fas fa-envelope-open-text"></i> Spam or misleading</span>
                </label>
                <label class="report-reason-option">
                    <input type="radio" name="report_reason" value="harassment">
                    <span><i class="fas fa-user-slash"></i> Harassment or abuse</span>
                </label>
                <label class="report-reason-option">
                    <input type="radio" name="report_reason" value="fake_account">
                    <span><i class="fas fa-user-secret"></i> Fake or impersonation</span>
                </label>
                <label class="report-reason-option">
                    <input type="radio" name="report_reason" value="other">
                    <span><i class="fas fa-ellipsis-h"></i> Other</span>
                </label>
            </div>
            <textarea id="report-details" class="report-textarea"
                placeholder="Additional details (optional)..." rows="3" maxlength="500"></textarea>
        </div>
        <div class="report-modal-footer">
            <button class="btn-report-cancel" id="btn-report-cancel">Cancel</button>
            <button class="btn-report-submit" id="btn-report-submit">
                <i class="fas fa-flag"></i> Submit Report
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/artistShow.js') }}"></script>
@endsection