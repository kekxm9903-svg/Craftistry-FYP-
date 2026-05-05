@extends('layouts.app')

@section('title', 'My Favourites')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/favoriteList.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

<div style="max-width:1100px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="{{ route('dashboard') }}" class="back-btn">← Back</a>
</div>

<div class="co-page">

    {{-- ══ PAGE HEADER ══ --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">My Favourites</div>
            <div class="page-subtitle">Artists and artworks you have saved</div>
        </div>
        @php
            $artistCount  = $favoriteArtists->count();
            $productCount = $favoriteProducts->count();
            $totalSaved   = $artistCount + $productCount;
        @endphp
        @if($totalSaved > 0)
            <span class="status-badge purple">
                <i class="fas fa-heart"></i>
                {{ $totalSaved }} saved
                &nbsp;&middot;&nbsp;
                {{ $artistCount }} {{ $artistCount === 1 ? 'artist' : 'artists' }}
                &nbsp;&middot;&nbsp;
                {{ $productCount }} {{ $productCount === 1 ? 'artwork' : 'artworks' }}
            </span>
        @endif
    </div>

    {{-- ══ TAB BAR ══ --}}
    @php $activeTab = request('tab', 'artists'); @endphp
    <div class="tab-bar">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'artists']) }}"
           class="tab-item {{ $activeTab === 'artists' ? 'active' : '' }}">
            <i class="fas fa-heart"></i>
            Favourite Artists
            @if($favoriteArtists->count() > 0)
                <span class="tab-badge">{{ $favoriteArtists->count() }}</span>
            @endif
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'products']) }}"
           class="tab-item {{ $activeTab === 'products' ? 'active' : '' }}">
            <i class="fas fa-star"></i>
            Favourite Artworks
            @if($favoriteProducts->count() > 0)
                <span class="tab-badge">{{ $favoriteProducts->count() }}</span>
            @endif
        </a>
    </div>

    {{-- ══ ARTISTS TAB ══ --}}
    @if($activeTab === 'artists')
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Favourite Artists
            </div>
            @if($favoriteArtists->count() > 0)
                <span class="section-count">{{ $favoriteArtists->count() }} total</span>
            @endif
        </div>
        <div class="sp-card-body">

            @if($favoriteArtists->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-heart"></i></div>
                    <h3>No favourite artists yet</h3>
                    <p>Discover talented artists and tap <strong>Favourite</strong> on their profile to save them here.</p>
                    <a href="{{ route('artist.browse') }}" class="btn-browse-empty">
                        <i class="fas fa-compass"></i> Browse Artists
                    </a>
                </div>
            @else
                <div class="request-list" id="artist-list">
                    @foreach($favoriteArtists as $artist)
                    <div class="request-row artist-row"
                         data-name="{{ strtolower($artist->fullname ?? $artist->name ?? '') }}"
                         data-spec="{{ strtolower($artist->artist->specialization ?? '') }}">

                        {{-- Avatar --}}
                        <div class="request-thumb">
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

                        {{-- Info --}}
                        <a href="{{ route('artist.browse.show', $artist->id) }}" class="request-body">
                            <div class="request-title">{{ $artist->fullname ?? $artist->name ?? 'Unknown Artist' }}</div>
                            <div class="request-meta">
                                @if($artist->artist && $artist->artist->specialization)
                                    <span>
                                        <i class="fas fa-palette meta-icon"></i>
                                        {{ $artist->artist->specialization }}
                                    </span>
                                @endif
                                <span>
                                    <i class="fas fa-heart meta-icon"></i>
                                    Saved {{ \Carbon\Carbon::parse($artist->pivot->created_at)->diffForHumans() }}
                                </span>
                                @if(($artist->artist->rating ?? 0) > 0)
                                    <span class="rating-lbl">
                                        <i class="fas fa-star" style="color:#f97316;"></i>
                                        {{ number_format($artist->artist->rating, 1) }}
                                    </span>
                                @endif
                            </div>
                        </a>

                        {{-- Unfav --}}
                        <div class="request-right">
                            <button class="btn-unfav"
                                    data-url="{{ route('artist.favorite', $artist->id) }}"
                                    title="Remove from favourites"
                                    aria-label="Remove {{ $artist->fullname ?? $artist->name }}">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>

                    </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
    @endif

    {{-- ══ PRODUCTS TAB ══ --}}
    @if($activeTab === 'products')
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Favourite Artworks
            </div>
            @if($favoriteProducts->count() > 0)
                <span class="section-count">{{ $favoriteProducts->count() }} total</span>
            @endif
        </div>
        <div class="sp-card-body">

            @if($favoriteProducts->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-star"></i></div>
                    <h3>No favourite artworks yet</h3>
                    <p>Browse artworks and tap <strong>Favourite</strong> on any listing to save it here.</p>
                    <a href="{{ route('artist.browse') }}" class="btn-browse-empty">
                        <i class="fas fa-compass"></i> Browse Artworks
                    </a>
                </div>
            @else
                <div class="request-list" id="product-list">
                    @foreach($favoriteProducts as $product)
                    <div class="request-row product-row"
                         data-title="{{ strtolower($product->product_name ?? '') }}"
                         data-artist="{{ strtolower($product->artist->user->fullname ?? $product->artist->user->name ?? '') }}"
                         data-type="{{ $product->artwork_type ?? 'physical' }}">

                        {{-- Thumbnail --}}
                        <div class="request-thumb product-thumb">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}"
                                     alt="{{ $product->product_name }}"
                                     loading="lazy">
                            @else
                                <i class="fas fa-image"></i>
                            @endif
                        </div>

                        {{-- Info --}}
                        <a href="{{ route('product.show', $product->id) }}" class="request-body">
                            <div class="request-title">{{ $product->product_name ?? 'Untitled' }}</div>
                            <div class="request-meta">
                                <span>
                                    <i class="fas fa-user meta-icon"></i>
                                    {{ $product->artist->user->fullname ?? $product->artist->user->name ?? 'Unknown' }}
                                </span>
                                <span class="type-badge type-{{ $product->artwork_type ?? 'physical' }}">
                                    {{ ucfirst($product->artwork_type ?? 'Physical') }}
                                </span>
                                <span>
                                    <i class="fas fa-heart meta-icon"></i>
                                    Saved {{ \Carbon\Carbon::parse($product->pivot->created_at)->diffForHumans() }}
                                </span>
                                @if(isset($product->average_rating) && $product->average_rating > 0)
                                    <span class="rating-lbl">
                                        <i class="fas fa-star" style="color:#f97316;"></i>
                                        {{ number_format($product->average_rating, 1) }}
                                    </span>
                                @endif
                            </div>
                        </a>

                        {{-- Price + Unfav --}}
                        <div class="request-right">
                            <div class="request-price">RM {{ number_format($product->product_price ?? 0, 2) }}</div>
                            <button class="btn-unfav"
                                    data-url="{{ route('product.favorite', $product->id) }}"
                                    title="Remove from favourites"
                                    aria-label="Remove {{ $product->product_name }}">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>

                    </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
    @endif

</div>

{{-- Toast --}}
<div class="fav-toast" id="fav-toast" role="status" aria-live="polite"></div>

@endsection

@section('scripts')
<script src="{{ asset('js/favorites.js') }}"></script>
@endsection