@extends('layouts.app')

@section('title', 'My Favourite Artists')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/favoriteList.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">My Favourites</span>
    </div>
</div>

{{-- Back button --}}
<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="{{ route('dashboard') }}" class="back-btn">
        ← Back
    </a>
</div>

<div class="fav-page">

    {{-- ══ PAGE HEADER CARD ══ --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">My Favourite Artists</div>
            <div class="page-subtitle">
                @if($favorites->count() > 0)
                    {{ $favorites->count() }} {{ Str::plural('artist', $favorites->count()) }} in your collection
                @else
                    Start building your personal artist collection
                @endif
            </div>
        </div>
        <a href="{{ route('artist.browse') }}" class="btn-browse">
            <i class="fas fa-compass"></i> Browse Artists
        </a>
    </div>

    {{-- ══ FILTER CARD ══ --}}
    @if($favorites->count() > 0)
    <div class="filter-card">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text"
                   id="fav-search"
                   placeholder="Search by name or specialization..."
                   autocomplete="off">
            <button class="search-clear" id="search-clear" style="display:none" aria-label="Clear">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <select id="fav-sort" class="filter-select">
            <option value="newest">Latest Saved</option>
            <option value="oldest">Oldest Saved</option>
            <option value="name-az">Name A–Z</option>
            <option value="name-za">Name Z–A</option>
            <option value="rating">Top Rated</option>
        </select>
    </div>
    @endif

    {{-- ══ ARTIST CARDS GRID ══ --}}
    @if($favorites->count() > 0)

        <div class="artist-grid" id="fav-grid">
            @foreach($favorites as $artist)
            <div class="artist-card"
                 data-name="{{ strtolower($artist->fullname ?? $artist->name ?? '') }}"
                 data-specialization="{{ strtolower($artist->artist->specialization ?? '') }}"
                 data-rating="{{ $artist->artist->rating ?? 0 }}"
                 data-favorited-at="{{ $artist->pivot->created_at ?? now() }}"
                 style="--i: {{ $loop->index }}">

                {{-- Unfav button --}}
                <button class="btn-unfav"
                        data-artist-id="{{ $artist->id }}"
                        data-url="{{ route('artist.favorite', $artist->id) }}"
                        title="Remove from favourites"
                        aria-label="Remove {{ $artist->fullname ?? $artist->name }}">
                    <i class="fas fa-heart"></i>
                </button>

                {{-- Artist info row --}}
                <a href="{{ route('artist.browse.show', $artist->id) }}" class="artist-info-row">
                    <div class="artist-avatar">
                        @if($artist->profile_image)
                            <img src="{{ asset('storage/' . $artist->profile_image) }}"
                                 alt="{{ $artist->fullname ?? $artist->name }}"
                                 loading="lazy">
                        @else
                            <div class="avatar-placeholder">
                                {{ strtoupper(substr($artist->fullname ?? $artist->name ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="artist-meta">
                        <div class="artist-name">{{ $artist->fullname ?? $artist->name ?? 'Unknown Artist' }}</div>
                        @if($artist->artist && $artist->artist->specialization)
                            <span class="artist-spec">
                                <i class="fas fa-palette"></i>
                                {{ $artist->artist->specialization }}
                            </span>
                        @endif
                    </div>
                </a>

                {{-- Demo thumbnail strip --}}
                @if($artist->artist && $artist->artist->demoArtworks->count() > 0)
                    <div class="demo-thumbs">
                        @foreach($artist->artist->demoArtworks->take(3) as $demo)
                            <div class="thumb">
                                <img src="{{ asset('storage/' . $demo->image_path) }}"
                                     alt="{{ $demo->title }}"
                                     loading="lazy">
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="demo-thumbs demo-empty">
                        <div class="no-demo">
                            <i class="fas fa-image"></i>
                            <span>No demo artworks yet</span>
                        </div>
                    </div>
                @endif

                {{-- Card footer --}}
                <div class="card-footer">
                    <span class="saved-date">
                        <i class="fas fa-heart" style="color:var(--primary);font-size:10px;"></i>
                        Saved {{ \Carbon\Carbon::parse($artist->pivot->created_at)->diffForHumans() }}
                    </span>
                    <span class="card-stat">
                        <i class="fas fa-star" style="color:#f97316;"></i>
                        {{ number_format($artist->artist->rating ?? 5.0, 1) }}
                    </span>
                </div>

            </div>
            @endforeach
        </div>

        {{-- No search results --}}
        <div class="empty-search" id="empty-search" style="display:none">
            <i class="fas fa-search"></i>
            <h3>No artists found</h3>
            <p>Try searching with a different name or specialization</p>
            <button id="clear-search-btn" class="btn-clear-search">Clear Search</button>
        </div>

    @else

        {{-- ══ EMPTY STATE ══ --}}
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-heart"></i>
            </div>
            <h3>No favourite artists yet</h3>
            <p>Discover talented artists and tap <strong>Favourite</strong> on their profile to save them here.</p>
            <a href="{{ route('artist.browse') }}" class="btn-browse-empty">
                <i class="fas fa-compass"></i> Browse Artists
            </a>
        </div>

    @endif

</div>

{{-- Toast --}}
<div class="fav-toast" id="fav-toast" role="status" aria-live="polite"></div>

@endsection

@section('scripts')
<script src="{{ asset('js/favorites.js') }}"></script>
@endsection