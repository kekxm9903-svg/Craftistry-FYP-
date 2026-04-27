@extends('layouts.app')

@section('title', 'Browse Artworks')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistBrowse.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">Browse Artworks</span>
    </div>
</div>

{{-- Category filter pills --}}
<div class="category-bar">
    <div class="category-inner">
        @php
            $currentSpec = request('specialty', '');
            $allSpecs    = array_merge([''], is_array($specialties) ? $specialties : $specialties->toArray());
        @endphp
        @foreach($allSpecs as $spec)
            <a href="{{ route('artist.browse', array_merge(request()->query(), ['specialty' => $spec])) }}"
               class="cat-pill {{ $currentSpec === $spec ? 'active' : '' }}">
                {{ $spec === '' ? 'All' : $spec }}
            </a>
        @endforeach
    </div>
</div>

<div class="browse-page">

    {{-- Filter bar --}}
    <div class="sp-card filter-card">
        <form action="{{ route('artist.browse') }}" method="GET" class="filter-form">

            @if(request('specialty'))
                <input type="hidden" name="specialty" value="{{ request('specialty') }}">
            @endif

            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text"
                       name="search"
                       class="search-input"
                       placeholder="Search artworks or artists..."
                       value="{{ request('search') }}">
            </div>

            <select name="sort" class="filter-select">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                <option value="name"   {{ request('sort') == 'name'   ? 'selected' : '' }}>Name (A-Z)</option>
                <option value="price"  {{ request('sort') == 'price'  ? 'selected' : '' }}>Price (Low-High)</option>
            </select>

            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>

    {{-- Results --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Browse Artworks
            </div>
            @if($artworks->count() > 0)
                <span class="section-count">{{ $artworks->total() }} artwork{{ $artworks->total() !== 1 ? 's' : '' }}</span>
            @endif
        </div>

        <div class="sp-card-body">
            @if($artworks->count() > 0)

                <div class="artists-grid">
                    @foreach($artworks as $artwork)
                    @php
                        $artistUser = $artwork->artist?->user;
                        $artistName = $artistUser?->fullname ?? $artistUser?->name ?? 'Unknown Artist';
                        $artistImg  = $artwork->artist?->profile_image ?? $artistUser?->profile_image ?? null;
                        $isSold     = in_array(strtolower($artwork->status ?? ''), ['sold', 'sold_out']);
                    @endphp
                    <a href="{{ route('product.show', $artwork->id) }}" class="artist-card {{ $isSold ? 'is-sold' : '' }}">

                        {{-- Artwork cover image --}}
                        <div class="card-cover">
                            @if($artwork->image_path)
                                <img src="{{ asset('storage/' . $artwork->image_path) }}"
                                     alt="{{ $artwork->product_name }}">
                            @else
                                <div class="cover-empty">
                                    <i class="fas fa-palette"></i>
                                </div>
                            @endif

                            {{-- Sold out overlay --}}
                            @if($isSold)
                                <div class="sold-stamp">SOLD</div>
                            @endif

                            {{-- Artwork type badge --}}
                            @if($artwork->artwork_type)
                                @php $typeClass = 'type-' . strtolower(str_replace([' ', '-'], '', $artwork->artwork_type ?? '')); @endphp
                                <div class="type-badge {{ $typeClass }}">{{ ucfirst($artwork->artwork_type) }}</div>
                            @endif
                        </div>

                        {{-- Info below image --}}
                        <div class="card-info">
                            <div class="card-avatar">
                                @if($artistImg)
                                    <img src="{{ asset('storage/' . $artistImg) }}" alt="{{ $artistName }}">
                                @else
                                    <div class="avatar-letter">
                                        {{ strtoupper(substr($artistName, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="card-text">
                                <div class="card-name">{{ $artwork->product_name ?? 'Untitled' }}</div>
                                <div class="card-spec">by {{ $artistName }}</div>
                            </div>
                            @if($artwork->product_price)
                                <div class="card-price">
                                    RM {{ number_format($artwork->product_price, 2) }}
                                </div>
                            @endif
                        </div>

                    </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($artworks->hasPages())
                <div class="pagination-row">
                    <nav class="pagination-nav">
                        @if($artworks->onFirstPage())
                            <span class="pg-btn disabled"><i class="fas fa-chevron-left"></i></span>
                        @else
                            <a href="{{ $artworks->previousPageUrl() }}" class="pg-btn">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        @endif

                        @foreach($artworks->getUrlRange(1, $artworks->lastPage()) as $page => $url)
                            @if($page == $artworks->currentPage())
                                <span class="pg-btn active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pg-btn">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($artworks->hasMorePages())
                            <a href="{{ $artworks->nextPageUrl() }}" class="pg-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="pg-btn disabled"><i class="fas fa-chevron-right"></i></span>
                        @endif
                    </nav>
                </div>
                @endif

            @else
                <div class="empty-state">
                    <i class="fas fa-palette"></i>
                    <h3>No Artworks Found</h3>
                    <p>Try adjusting your search or filters.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection