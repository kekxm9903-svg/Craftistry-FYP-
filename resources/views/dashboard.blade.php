@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
<main class="main">

    {{-- ══ WELCOME BANNER ══ --}}
    <div class="welcome-banner">
        <div class="welcome-text">
            <div class="welcome-greeting">Good day, <span class="welcome-name">{{ $user->fullname }}</span></div>
            <div class="welcome-sub">Discover art, classes, and support local Malaysian artists.</div>
        </div>
        <div class="welcome-actions">
            <a href="{{ route('artist.browse') }}" class="btn-banner">
                <i class="bi bi-palette-fill"></i> Browse Artworks
            </a>
            <a href="{{ route('class.event.browse') }}" class="btn-banner btn-banner-outline">
                <i class="bi bi-mortarboard-fill"></i> Browse Classes
            </a>
        </div>
    </div>

    {{-- ══ STATS ROW ══ --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-heart-fill"></i></div>
            <div class="stat-body">
                <div class="stat-num">{{ $favoriteArtists }}</div>
                <div class="stat-label">Favourite List</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-bag-fill"></i></div>
            <div class="stat-body">
                <div class="stat-num">{{ $activeOrders }}</div>
                <div class="stat-label">Active Orders</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="stat-body">
                <div class="stat-num">{{ $enrolledClasses }}</div>
                <div class="stat-label">Enrolled Classes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-brush-fill"></i></div>
            <div class="stat-body">
                <div class="stat-num">{{ $customOrdersCount ?? 0 }}</div>
                <div class="stat-label">Custom Requests</div>
            </div>
        </div>
    </div>

    {{-- ══ QUICK ACTIONS ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Quick Actions
            </div>
        </div>
        <div class="sp-card-body">
            <div class="action-grid">

                <a href="{{ route('favorites.index') }}" class="action-card">
                    <div class="action-icon purple"><i class="bi bi-heart-fill"></i></div>
                    <div class="action-body">
                        <div class="action-title">Favourite List</div>
                        <div class="action-desc">Browse your saved artists and products</div>
                    </div>
                    <i class="bi bi-chevron-right action-arrow"></i>
                </a>

                <a href="{{ route('orders.index') }}" class="action-card">
                    <div class="action-icon blue"><i class="bi bi-clipboard-check-fill"></i></div>
                    <div class="action-body">
                        <div class="action-title">My Orders</div>
                        <div class="action-desc">Track and view order history</div>
                    </div>
                    @if(($activeOrdersPending ?? 0) > 0)
                        <span class="action-noti-badge">{{ ($activeOrdersPending ?? 0) > 99 ? '99+' : $activeOrdersPending }}</span>
                    @endif
                    <i class="bi bi-chevron-right action-arrow"></i>
                </a>

                <a href="{{ route('my.classes') }}" class="action-card">
                    <div class="action-icon orange"><i class="bi bi-mortarboard-fill"></i></div>
                    <div class="action-body">
                        <div class="action-title">My Classes</div>
                        <div class="action-desc">View enrolled classes</div>
                    </div>
                    <i class="bi bi-chevron-right action-arrow"></i>
                </a>

                <a href="{{ route('custom-orders.index') }}" class="action-card">
                    <div class="action-icon green"><i class="bi bi-brush-fill"></i></div>
                    <div class="action-body">
                        <div class="action-title">Custom Orders</div>
                        <div class="action-desc">View your custom requests</div>
                    </div>
                    @if(($customOrdersPending ?? 0) > 0)
                        <span class="action-noti-badge">{{ ($customOrdersPending ?? 0) > 99 ? '99+' : $customOrdersPending }}</span>
                    @endif
                    <i class="bi bi-chevron-right action-arrow"></i>
                </a>

            </div>
        </div>
    </div>

    {{-- ══ HOT ARTWORKS ══ --}}
    @if(isset($hotProducts) && $hotProducts->count() > 0)
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                🔥 Hot Artworks
            </div>
            <a href="{{ route('artist.browse') }}" class="see-all">See All <i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="sp-card-body">
            <div class="product-grid">
                @foreach($hotProducts as $product)
                @php
                    $pArtist = $product->artist?->user;
                    $pName   = $pArtist?->fullname ?? 'Artist';
                    $pImg    = $pArtist?->profile_image ?? null;
                    $pInit   = strtoupper(substr($pName, 0, 1));
                    $pPromo  = $product->promotion_price;
                    $sold    = $product->total_sold ?? 0;
                @endphp
                <a href="{{ $product->artist?->user_id === auth()->id() ? route('artist.artwork.preview', $product->id) : route('product.show', $product->id) }}" class="product-card">
                    <div class="product-img">
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->product_name }}">
                        @else
                            <div class="product-img-empty"><i class="bi bi-palette"></i></div>
                        @endif

                        @if($product->artwork_type)
                            <span class="product-type-badge {{ strtolower($product->artwork_type) }}">
                                {{ ucfirst($product->artwork_type) }}
                            </span>
                        @endif
                        @if($pPromo !== null)
                            <span class="product-promo-badge">-{{ number_format($product->promotion_discount, 0) }}%</span>
                        @endif
                    </div>
                    <div class="product-info">
                        <div class="product-name">{{ Str::limit($product->product_name ?? 'Artwork', 28) }}</div>
                        <div class="product-artist-row">
                            <div class="product-artist-ava">
                                @if($pImg)
                                    <img src="{{ asset('storage/' . $pImg) }}" alt="{{ $pName }}">
                                @else
                                    {{ $pInit }}
                                @endif
                            </div>
                            <span class="product-artist-name">{{ Str::limit($pName, 14) }}</span>
                        </div>
                        @if($pPromo !== null)
                            <div class="product-price-promo">RM {{ number_format($pPromo, 2) }}</div>
                            <div class="product-price-original">RM {{ number_format($product->product_price, 2) }}</div>
                        @else
                            <div class="product-price">RM {{ number_format($product->product_price, 2) }}</div>
                        @endif
                        @if($sold > 0)
                            <div class="product-sold-count">🔥 {{ $sold }} sold</div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ══ ON SALE ══ --}}
    @if(isset($onSaleProducts) && $onSaleProducts->count() > 0)
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                🏷️ On Sale
            </div>
            <a href="{{ route('artist.browse') }}" class="see-all">See All <i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="sp-card-body">
            <div class="product-grid">
                @foreach($onSaleProducts as $product)
                @php
                    $pArtist = $product->artist?->user;
                    $pName   = $pArtist?->fullname ?? 'Artist';
                    $pImg    = $pArtist?->profile_image ?? null;
                    $pInit   = strtoupper(substr($pName, 0, 1));
                    $pPromo  = $product->promotion_price;
                @endphp
                <a href="{{ $product->artist?->user_id === auth()->id() ? route('artist.artwork.preview', $product->id) : route('product.show', $product->id) }}" class="product-card">
                    <div class="product-img">
                        @if($product->image_path)
                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->product_name }}">
                        @else
                            <div class="product-img-empty"><i class="bi bi-palette"></i></div>
                        @endif
                        @if($product->artwork_type)
                            <span class="product-type-badge {{ strtolower($product->artwork_type) }}">
                                {{ ucfirst($product->artwork_type) }}
                            </span>
                        @endif
                        @if($pPromo !== null)
                            <span class="product-promo-badge">-{{ number_format($product->promotion_discount, 0) }}%</span>
                        @endif
                    </div>
                    <div class="product-info">
                        <div class="product-name">{{ Str::limit($product->product_name ?? 'Artwork', 28) }}</div>
                        <div class="product-artist-row">
                            <div class="product-artist-ava">
                                @if($pImg)
                                    <img src="{{ asset('storage/' . $pImg) }}" alt="{{ $pName }}">
                                @else
                                    {{ $pInit }}
                                @endif
                            </div>
                            <span class="product-artist-name">{{ Str::limit($pName, 14) }}</span>
                        </div>
                        @if($pPromo !== null)
                            <div class="product-price-promo">RM {{ number_format($pPromo, 2) }}</div>
                            <div class="product-price-original">RM {{ number_format($product->product_price, 2) }}</div>
                        @else
                            <div class="product-price">RM {{ number_format($product->product_price, 2) }}</div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ══ HOT ARTISTS ══ --}}
    @if(isset($hotArtists) && $hotArtists->count() > 0)
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                🔥 Hot Artists
            </div>
        </div>
        <div class="sp-card-body no-pad">
            <div class="artist-scroll-row">
                @foreach($hotArtists as $artist)
                @php
                    $aUser  = $artist->user;
                    $aImg   = $artist->profile_image ?? $aUser->profile_image ?? null;
                    $aName  = $aUser->fullname ?? 'Artist';
                    $aInit  = strtoupper(substr($aName, 0, 1));
                    $aFirst = $artist->artworkSells->first();
                    $aSold  = $artist->total_sold ?? 0;
                @endphp
                <a href="{{ route('artist.browse.show', $aUser->id) }}" class="artist-scroll-card">
                    <div class="asc-cover">
                        @if($aFirst && $aFirst->image_path)
                            <img src="{{ asset('storage/' . $aFirst->image_path) }}" alt="{{ $aName }}">
                        @else
                            <div class="asc-cover-empty"></div>
                        @endif
                    </div>
                    <div class="asc-avatar">
                        @if($aImg)
                            <img src="{{ asset('storage/' . $aImg) }}" alt="{{ $aName }}">
                        @else
                            <span>{{ $aInit }}</span>
                        @endif
                    </div>
                    <div class="asc-name">{{ Str::limit($aName, 14) }}</div>
                    @if($artist->specialization)
                        <div class="asc-spec">{{ Str::limit($artist->specialization, 16) }}</div>
                    @endif
                    <div class="asc-count">
                        {{ $artist->artworkSells->count() }} artwork{{ $artist->artworkSells->count() !== 1 ? 's' : '' }}
                    </div>
                    @if($aSold > 0)
                        <div class="asc-sold">🔥 {{ $aSold }} sold</div>
                    @endif
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ══ UPCOMING CLASSES ══ --}}
    @if(isset($upcomingClasses) && $upcomingClasses->count() > 0)
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Upcoming Classes
            </div>
            <a href="{{ route('class.event.browse') }}" class="see-all">See All <i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="sp-card-body">
            <div class="class-list">
                @foreach($upcomingClasses as $class)
                <a href="{{ route('class.event.show', $class->id) }}" class="class-row">
                    <div class="class-row-img">
                        @if($class->poster_image)
                            <img src="{{ asset('storage/' . $class->poster_image) }}" alt="{{ $class->title }}">
                        @else
                            <div class="class-row-img-empty"><i class="bi bi-mortarboard-fill"></i></div>
                        @endif
                    </div>
                    <div class="class-row-body">
                        <div class="class-row-title">{{ Str::limit($class->title, 40) }}</div>
                        <div class="class-row-meta">
                            <span><i class="bi bi-calendar3"></i> {{ \Carbon\Carbon::parse($class->start_date)->format('d M Y') }}</span>
                            <span><i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }}</span>
                            <span class="class-type-tag {{ $class->media_type }}">
                                {{ $class->media_type == 'online' ? '💻 Online' : '📍 Physical' }}
                            </span>
                        </div>
                        <div class="class-row-artist">by {{ $class->user->fullname ?? 'Artist' }}</div>
                    </div>
                    <div class="class-row-fee">
                        @if($class->is_paid && $class->price)
                            <span class="fee-price">RM {{ number_format($class->price, 2) }}</span>
                        @else
                            <span class="fee-free">Free</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</main>
@endsection

@section('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection