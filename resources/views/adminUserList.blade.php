<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Users — Craftistry Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/adminUserList.css') }}">
</head>
<body>

<div class="admin-wrapper">

    {{-- ══════════════ SIDEBAR ══════════════ --}}
    <aside class="admin-sidebar" id="admin-sidebar">

        <div class="sidebar-logo">
            <img src="{{ asset('images/Logo.png') }}" alt="Craftistry" class="sidebar-logo-img">
            <div class="logo-text">
                <em>Admin Panel</em>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="snav-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.users') }}" class="snav-item active">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="{{ route('admin.feedbacks') }}" class="snav-item">
                <i class="fas fa-comment-alt"></i>
                <span>Feedbacks</span>
            </a>
            <a href="{{ route('admin.reports') }}" class="snav-item">
                <i class="fas fa-flag"></i>
                <span>Reports</span>
            </a>
            <a href="{{ route('admin.admins') }}" class="snav-item">
                <i class="fas fa-user-shield"></i>
                <span>Admins</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sf-user">
                <div class="sf-avatar">
                    {{ strtoupper(substr(auth()->user()->fullname ?? 'A', 0, 1)) }}
                </div>
                <div class="sf-info">
                    <p class="sf-name">{{ auth()->user()->fullname }}</p>
                    <p class="sf-role">Administrator</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sf-logout" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>

    </aside>

    {{-- ══════════════ MAIN ══════════════ --}}
    <div class="admin-content" id="admin-content">

        <header class="admin-topbar">
            <button class="topbar-toggle" id="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-right">
                <a href="{{ route('welcome') }}" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
            </div>
        </header>

        <main class="admin-main">

            {{-- Page Title --}}
            <div class="page-header">
                <h1>Users</h1>
                <p>Manage all registered users on the platform</p>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div class="admin-alert alert-success" id="admin-alert">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                    <button class="alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            @endif
            @if(session('error'))
                <div class="admin-alert alert-error" id="admin-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                    <button class="alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
            @endif

            {{-- Filter Bar --}}
            <div class="filter-bar">
                <form method="GET" action="{{ route('admin.users') }}" class="filter-form" id="filterForm">
                    <div class="search-wrap">
                        <i class="fas fa-search search-icon"></i>
                        <input
                            type="text"
                            name="search"
                            class="search-input"
                            placeholder="Search by name or email…"
                            value="{{ request('search') }}"
                        >
                        @if(request('search'))
                            <button type="button" class="search-clear" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>

                    <div class="filter-pills">
                        <button type="submit" name="status" value="" class="pill {{ !request('status') ? 'active' : '' }}">
                            All
                        </button>
                        <button type="submit" name="status" value="artist" class="pill pill-blue {{ request('status') === 'artist' ? 'active' : '' }}">
                            <i class="fas fa-palette"></i> Artists
                        </button>
                        <button type="submit" name="status" value="buyer" class="pill {{ request('status') === 'buyer' ? 'active' : '' }}">
                            <i class="fas fa-shopping-bag"></i> Buyers only
                        </button>
                        <button type="submit" name="status" value="banned" class="pill pill-red {{ request('status') === 'banned' ? 'active' : '' }}">
                            <i class="fas fa-ban"></i> Banned
                        </button>
                    </div>

                    <div class="filter-meta">
                        <span class="result-count">
                            {{ $users->total() }} {{ Str::plural('user', $users->total()) }} found
                        </span>
                    </div>
                </form>
            </div>

            {{-- Users Table --}}
            <div class="table-card">
                @if($users->isEmpty())
                    <div class="table-empty">
                        <div class="empty-icon"><i class="fas fa-users-slash"></i></div>
                        <p class="empty-title">No users found</p>
                        <p class="empty-sub">Try adjusting your search or filter</p>
                        <a href="{{ route('admin.users') }}" class="btn-outline">Clear filters</a>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th class="th-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $index => $user)
                                <tr class="user-row">
                                    <td class="td-num">{{ $users->firstItem() + $index }}</td>

                                    <td class="td-user">
                                        <div class="user-cell">
                                            <div class="user-avatar" style="--hue: {{ crc32($user->email) % 360 }}">
                                                @if($user->profile_image)
                                                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->fullname }}">
                                                @else
                                                    {{ strtoupper(substr($user->fullname ?? 'U', 0, 1)) }}
                                                @endif
                                            </div>
                                            <div class="user-info">
                                                <p class="user-name">{{ $user->fullname }}</p>
                                                <p class="user-email">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="td-role">
                                        <div class="role-badges">
                                            {{-- Buyer badge — always shown --}}
                                            <span class="badge gray">
                                                <i class="fas fa-shopping-bag"></i> Buyer
                                            </span>
                                            {{-- Artist badge — only shown if registered as artist --}}
                                            @if($user->is_artist)
                                                <span class="badge blue">
                                                    <i class="fas fa-palette"></i> Artist
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="td-status">
                                        @if($user->artist_status === 'banned')
                                            <span class="status-dot banned">
                                                <i class="fas fa-circle"></i> Banned
                                            </span>
                                        @else
                                            <span class="status-dot active">
                                                <i class="fas fa-circle"></i> Active
                                            </span>
                                        @endif
                                    </td>

                                    <td class="td-date">
                                        <span class="date-text">{{ $user->created_at->format('d M Y') }}</span>
                                        <span class="date-sub">{{ $user->created_at->diffForHumans() }}</span>
                                    </td>

                                    <td class="td-actions">
                                        @if($user->artist_status === 'banned')
                                            <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="inline-form">
                                                @csrf
                                                <button type="submit" class="action-btn unban" title="Unban user"
                                                    onclick="return confirm('Unban {{ addslashes($user->fullname) }}?')">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span>Unban</span>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="inline-form">
                                                @csrf
                                                <button type="submit" class="action-btn ban" title="Ban user"
                                                    onclick="return confirm('Ban {{ addslashes($user->fullname) }}? They will lose access to the platform.')">
                                                    <i class="fas fa-ban"></i>
                                                    <span>Ban</span>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($users->hasPages())
                        <div class="pagination-wrap">
                            <div class="pag-info">
                                Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
                            </div>
                            <div class="pag-links">
                                {{-- Previous --}}
                                @if($users->onFirstPage())
                                    <span class="pag-btn disabled"><i class="fas fa-chevron-left"></i></span>
                                @else
                                    <a href="{{ $users->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pag-btn">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                @endif

                                {{-- Page numbers --}}
                                @foreach($users->getUrlRange(max(1, $users->currentPage()-2), min($users->lastPage(), $users->currentPage()+2)) as $page => $url)
                                    @if($page == $users->currentPage())
                                        <span class="pag-btn current">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}&{{ http_build_query(request()->except('page')) }}" class="pag-btn">{{ $page }}</a>
                                    @endif
                                @endforeach

                                {{-- Next --}}
                                @if($users->hasMorePages())
                                    <a href="{{ $users->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="pag-btn">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <span class="pag-btn disabled"><i class="fas fa-chevron-right"></i></span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </div>

        </main>
    </div>

</div>

<script src="{{ asset('js/adminUserList.js') }}"></script>
</body>
</html>