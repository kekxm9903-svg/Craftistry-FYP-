<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | Craftistry</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fc;
            color: #1a202c;
        }
    </style>
    
    {{-- HEADER/NAV STYLES --}}
    <style>
        /* ============================================
           NAVIGATION STYLES - DO NOT OVERRIDE
           ============================================ */
        
        .header {
            background: white !important;
            height: 64px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 32px !important;
            border-bottom: 1px solid #e5e7eb !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 100 !important;
        }

        .header-left img {
            height: 52px !important;
        }

        .nav {
            display: flex !important;
            gap: 8px !important;
        }

        .nav-link {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 10px 16px !important;
            border-radius: 8px !important;
            color: #6b7280 !important;
            text-decoration: none !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            transition: all 0.2s !important;
        }

        .nav-link i {
            font-size: 16px !important;
        }

        .nav-link:hover {
            background: #f3f4f6 !important;
            color: #667eea !important;
        }

        .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
        }

        .header-right {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
        }

        /* ============================================
           CART ICON STYLES
           ============================================ */

        .cart-icon-btn {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            color: #6b7280 !important;
            text-decoration: none !important;
            transition: all 0.2s !important;
            background: transparent !important;
        }

        .cart-icon-btn:hover {
            background: #f3f4f6 !important;
            color: #667eea !important;
        }

        .cart-icon-btn.active-cart {
            color: #667eea !important;
        }

        .cart-icon-btn i {
            font-size: 18px !important;
        }

        .cart-icon-badge {
            position: absolute !important;
            top: 0px !important;
            right: 0px !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            min-width: 18px !important;
            height: 18px !important;
            border-radius: 9px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 4px !important;
            line-height: 1 !important;
            border: 2px solid white !important;
            opacity: 0 !important;
            transform: scale(0) !important;
            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        }

        .cart-icon-badge.has-items {
            opacity: 1 !important;
            transform: scale(1) !important;
        }

        /* ============================================
           USER DROPDOWN — CLICK-BASED (not hover)
           ============================================ */

        .user-dropdown {
            position: relative !important;
            display: inline-block !important;
        }

        .profile-avatar {
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            overflow: hidden !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: transform 0.2s, box-shadow 0.2s !important;
            border: 2px solid transparent !important;
            cursor: pointer !important;
            position: relative !important;
        }

        .profile-avatar:hover {
            transform: scale(1.05) !important;
            border-color: #e5e7eb !important;
        }

        .user-dropdown.open .profile-avatar {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15) !important;
        }

        .avatar-image {
            width: 40px !important;
            height: 40px !important;
            object-fit: cover !important;
            border-radius: 50% !important;
            display: block !important;
        }

        .avatar-placeholder-nav {
            width: 40px !important;
            height: 40px !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-family: 'Inter', sans-serif !important;
            font-weight: 600 !important;
            font-size: 1rem !important;
            border-radius: 50% !important;
        }

        .dropdown-menu {
            display: none !important;
            position: absolute !important;
            right: 0 !important;
            top: calc(100% + 8px) !important;
            background-color: white !important;
            min-width: 190px !important;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12), 0 4px 10px -3px rgba(0,0,0,0.07) !important;
            border-radius: 10px !important;
            border: 1px solid #f0f0f0 !important;
            z-index: 1000 !important;
            overflow: hidden !important;
            opacity: 0 !important;
            transform: translateY(-6px) scale(0.97) !important;
            transition: opacity 0.15s ease, transform 0.15s ease !important;
        }

        .user-dropdown.open .dropdown-menu {
            display: block !important;
            opacity: 1 !important;
            transform: translateY(0) scale(1) !important;
        }

        .dropdown-item {
            display: flex !important;
            align-items: center !important;
            padding: 11px 16px !important;
            color: #4b5563 !important;
            text-decoration: none !important;
            font-size: 14px !important;
            transition: background-color 0.15s !important;
        }

        .dropdown-item:hover {
            background-color: #f9fafb !important;
            color: #667eea !important;
        }

        .dropdown-item i {
            width: 20px !important;
            margin-right: 10px !important;
            color: #9ca3af !important;
        }

        .dropdown-item:hover i {
            color: #667eea !important;
        }

        .auth-buttons {
            display: flex !important;
            gap: 12px !important;
            align-items: center !important;
        }

        .btn-login {
            padding: 8px 16px !important;
            border-radius: 8px !important;
            color: #667eea !important;
            text-decoration: none !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            transition: all 0.2s !important;
        }

        .btn-login:hover {
            background: #f3f4f6 !important;
        }

        .btn-register {
            padding: 8px 16px !important;
            border-radius: 8px !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            text-decoration: none !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            transition: all 0.2s !important;
        }

        .btn-register:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
        }

        @media (max-width: 768px) {
            .header {
                padding: 0 16px !important;
                height: 60px !important;
            }

            .header-left img {
                height: 40px !important;
            }

            .nav {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                background: white !important;
                border-top: 1px solid #e5e7eb !important;
                padding: 12px 8px !important;
                justify-content: space-around !important;
                z-index: 100 !important;
            }

            .nav-link {
                flex-direction: column !important;
                padding: 8px 12px !important;
                gap: 4px !important;
                font-size: 11px !important;
            }

            .nav-link i {
                font-size: 18px !important;
            }

            .dropdown-menu {
                top: auto !important;
                bottom: calc(100% + 8px) !important;
                right: 0 !important;
            }
        }
    </style>

    {{-- PAGE STYLES LOAD AFTER NAV — so page CSS can override body/layout --}}
    @yield('styles')

    {{-- ── Global sticky breadcrumb & category bar ──────────────────────
         Navbar:       64px tall, z-index 100  (60px on mobile)
         Breadcrumb:   sticky top 64px,  z-index 99
         Category bar: sticky top 95px,  z-index 98  (64 + ~31px bc-bar)
         !important overrides any per-page declarations.
    ──────────────────────────────────────────────────────────────────── --}}
    <style>
        /* Breadcrumb — flush under navbar */
        .bc-bar {
            position: sticky !important;
            top: 64px !important;
            z-index: 99 !important;
        }

        /* Category / filter tab bar — flush under breadcrumb */
        .category-bar {
            position: sticky !important;
            top: 95px !important;
            z-index: 98 !important;
        }

        @media (max-width: 768px) {
            .bc-bar {
                top: 60px !important;
            }

            .category-bar {
                top: 91px !important;  /* 60px navbar + 31px bc-bar */
            }
        }
    </style>
</head>
<body>
    @if (!request()->routeIs('login') && !request()->routeIs('register'))
        
        <header class="header">
            <div class="header-left">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/Logo.png') }}" alt="Craftistry">
                </a>
            </div>

            <nav class="nav">
                <a href="{{ url('/dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('artist.browse') }}" 
                   class="nav-link {{ request()->routeIs('artist.browse*') || request()->routeIs('product.show') ? 'active' : '' }}">
                    <i class="fas fa-palette"></i>
                    <span>Art Gallery</span>
                </a>

                <a href="{{ route('class.event.browse') }}" 
                   class="nav-link {{ request()->routeIs('class.event.browse') || (request()->routeIs('class.event.show') && request('context') !== 'studio') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Event</span>
                </a>

                <a href="{{ url('/studio') }}" 
                   class="nav-link {{ Request::is('studio*') || Request::is('artist/studio*') || Route::is('artist.profile') || Route::is('class.event.index') || Route::is('class.event.edit') || (request()->routeIs('class.event.show') && request('context') === 'studio') ? 'active' : '' }}">
                    <i class="fas fa-store"></i>
                    <span>Studio</span>
                </a>
            </nav>

            <div class="header-right">
                @auth
                    @php $cartCount = count(session('cart', [])); @endphp
                    <a href="{{ route('cart.index') }}" 
                       class="cart-icon-btn {{ request()->routeIs('cart.*') ? 'active-cart' : '' }}"
                       title="My Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-icon-badge {{ $cartCount > 0 ? 'has-items' : '' }}" id="global-cart-badge">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    </a>

                    <div class="user-dropdown" id="userDropdown">
                        <div class="profile-avatar" id="profileAvatarBtn" role="button" aria-haspopup="true" aria-expanded="false">
                            @if(Auth::user()->profile_image)
                                <img src="{{ asset('storage/' . Auth::user()->profile_image) }}?v={{ time() }}" 
                                     alt="Profile" 
                                     class="avatar-image">
                            @else
                                <div class="avatar-placeholder-nav">
                                    {{ strtoupper(substr(Auth::user()->fullname ?? Auth::user()->name ?? '?', 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <div class="dropdown-menu" id="dropdownMenu" role="menu">
                            <a href="{{ route('user.profile.show') }}" class="dropdown-item">
                                <i class="fas fa-user-circle"></i> My Profile
                            </a>

                            <a href="{{ route('cart.index') }}" class="dropdown-item">
                                <i class="fas fa-shopping-cart"></i> My Cart
                                @if($cartCount > 0)
                                    <span style="margin-left:auto; background:linear-gradient(135deg,#667eea,#764ba2); color:white; font-size:11px; font-weight:700; padding:2px 8px; border-radius:10px;">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            </a>

                            <a href="{{ route('feedback.create') }}" class="dropdown-item">
                                <i class="fas fa-comment-dots"></i> Feedback
                            </a>

                            <div style="border-top: 1px solid #e5e7eb;"></div>

                            <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>

                    <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @else
                    <div class="auth-buttons">
                        <a href="{{ route('login') }}" class="btn-login">Login</a>
                        <a href="{{ route('register') }}" class="btn-register">Register</a>
                    </div>
                @endauth
            </div>
        </header>

    @endif

    @yield('content')
    
    @yield('scripts')

    <script>
        const userDropdown     = document.getElementById('userDropdown');
        const profileAvatarBtn = document.getElementById('profileAvatarBtn');

        if (profileAvatarBtn && userDropdown) {
            profileAvatarBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const isOpen = userDropdown.classList.toggle('open');
                profileAvatarBtn.setAttribute('aria-expanded', isOpen);
            });

            document.addEventListener('click', function (e) {
                if (!userDropdown.contains(e.target)) {
                    userDropdown.classList.remove('open');
                    profileAvatarBtn.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    userDropdown.classList.remove('open');
                    profileAvatarBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        window.updateCartBadge = function(count) {
            const badge = document.getElementById('global-cart-badge');
            if (!badge) return;
            badge.textContent = count > 99 ? '99+' : count;
            if (count > 0) {
                badge.classList.add('has-items');
            } else {
                badge.classList.remove('has-items');
            }
        };
    </script>
</body>
</html>