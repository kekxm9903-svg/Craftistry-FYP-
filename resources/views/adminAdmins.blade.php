<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admins — Craftistry Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/adminAdmins.css') }}">
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
            <a href="{{ route('admin.users') }}" class="snav-item">
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
            <a href="{{ route('admin.admins') }}" class="snav-item active">
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
                <h1>Admins</h1>
                <p>Manage administrator accounts for the platform</p>
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

            <div class="two-col">

                {{-- ── ADD ADMIN FORM ── --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-card-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <h2>Add New Admin</h2>
                            <p>Enter an existing user's email to promote them, or fill all fields to create a new admin account.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.admins.add') }}" id="addAdminForm">
                        @csrf

                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address <span class="required">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-input"
                                placeholder="admin@craftistry.com"
                                value="{{ old('email') }}"
                                required
                                autocomplete="off"
                            >
                            @error('email')
                                <span class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                            <span class="field-hint">If this email already exists as a user, they will be promoted to admin.</span>
                        </div>

                        <div class="form-divider">
                            <span>New account only (optional if email exists)</span>
                        </div>

                        <div class="form-group {{ $errors->has('fullname') ? 'has-error' : '' }}">
                            <label for="fullname">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input
                                type="text"
                                name="fullname"
                                id="fullname"
                                class="form-input"
                                placeholder="e.g. John Doe"
                                value="{{ old('fullname') }}"
                                autocomplete="off"
                            >
                            @error('fullname')
                                <span class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label for="password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="password-wrap">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-input"
                                    placeholder="Minimum 8 characters"
                                    autocomplete="new-password"
                                >
                                <button type="button" class="pw-toggle" id="pw-toggle" title="Show/hide password">
                                    <i class="fas fa-eye" id="pw-icon"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-user-shield"></i>
                            Add Admin
                        </button>
                    </form>
                </div>

                {{-- ── CURRENT ADMINS LIST ── --}}
                <div class="admins-card">
                    <div class="admins-card-header">
                        <h2><i class="fas fa-shield-alt"></i> Current Admins</h2>
                        <span class="admin-count">{{ $admins->count() }} {{ Str::plural('admin', $admins->count()) }}</span>
                    </div>

                    @if($admins->isEmpty())
                        <div class="list-empty">
                            <i class="fas fa-user-shield"></i>
                            <p>No admins found</p>
                        </div>
                    @else
                        <div class="admins-list">
                            @foreach($admins as $admin)
                            <div class="admin-row {{ $admin->id === auth()->id() ? 'is-you' : '' }}">
                                <div class="admin-avatar" style="--hue: {{ crc32($admin->email) % 360 }}">
                                    @if($admin->profile_image)
                                        <img src="{{ asset('storage/' . $admin->profile_image) }}" alt="{{ $admin->fullname }}">
                                    @else
                                        {{ strtoupper(substr($admin->fullname ?? 'A', 0, 1)) }}
                                    @endif
                                </div>
                                <div class="admin-info">
                                    <p class="admin-name">
                                        {{ $admin->fullname }}
                                        @if($admin->id === auth()->id())
                                            <span class="you-badge">You</span>
                                        @endif
                                    </p>
                                    <p class="admin-email">{{ $admin->email }}</p>
                                    <p class="admin-since">Admin since {{ $admin->created_at->format('d M Y') }}</p>
                                </div>
                                <div class="admin-actions">
                                    @if($admin->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.admins.remove', $admin) }}">
                                            @csrf
                                            <button type="submit" class="remove-btn"
                                                title="Remove admin privileges"
                                                onclick="return confirm('Remove admin privileges from {{ addslashes($admin->fullname) }}? They will become a regular user.')">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="self-badge" title="You cannot remove yourself">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

        </main>
    </div>

</div>

<script src="{{ asset('js/adminAdmins.js') }}"></script>
</body>
</html>