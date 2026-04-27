<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard — Craftistry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/adminDashboard.css') }}">
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
            <a href="{{ route('admin.dashboard') }}" class="snav-item active">
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
                @if($stats['unread_feedbacks'] > 0)
                    <span class="snav-badge">{{ $stats['unread_feedbacks'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.reports') }}" class="snav-item">
                <i class="fas fa-flag"></i>
                <span>Reports</span>
                @if($stats['pending_reports'] > 0)
                    <span class="snav-badge">{{ $stats['pending_reports'] }}</span>
                @endif
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
                <h1>Dashboard</h1>
                <p>System overview — {{ now()->format('d M Y, g:i A') }}</p>
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

            {{-- Stats --}}
            <div class="stats-grid">

                <div class="stat-card purple" style="--i:0">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-body">
                        <h3>{{ $stats['total_users'] }}</h3>
                        <p>Total Users</p>
                    </div>
                    <a href="{{ route('admin.users') }}" class="stat-arrow"><i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="stat-card red" style="--i:1">
                    <div class="stat-icon"><i class="fas fa-ban"></i></div>
                    <div class="stat-body">
                        <h3>{{ $stats['banned_users'] }}</h3>
                        <p>Banned Users</p>
                    </div>
                    <a href="{{ route('admin.users') }}?status=banned" class="stat-arrow"><i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="stat-card blue" style="--i:2">
                    <div class="stat-icon"><i class="fas fa-comment-alt"></i></div>
                    <div class="stat-body">
                        <h3>{{ $stats['unread_feedbacks'] }}<span class="stat-sub">/{{ $stats['total_feedbacks'] }}</span></h3>
                        <p>Unread Feedbacks</p>
                    </div>
                    <a href="{{ route('admin.feedbacks') }}" class="stat-arrow"><i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="stat-card orange" style="--i:3">
                    <div class="stat-icon"><i class="fas fa-flag"></i></div>
                    <div class="stat-body">
                        <h3>{{ $stats['pending_reports'] }}<span class="stat-sub">/{{ $stats['total_reports'] }}</span></h3>
                        <p>Pending Reports</p>
                    </div>
                    <a href="{{ route('admin.reports') }}" class="stat-arrow"><i class="fas fa-arrow-right"></i></a>
                </div>

            </div>

            {{-- Two Panels --}}
            <div class="panels-grid">

                {{-- Recent Feedbacks --}}
                <div class="panel">
                    <div class="panel-head">
                        <h2><i class="fas fa-comment-alt"></i> Recent Feedbacks</h2>
                        <a href="{{ route('admin.feedbacks') }}" class="panel-link">View all</a>
                    </div>

                    @forelse($recentFeedbacks as $fb)
                        <div class="panel-row {{ !$fb->is_read ? 'unread' : '' }}">
                            <div class="row-avatar purple">
                                {{ strtoupper(substr($fb->user->fullname ?? 'U', 0, 1)) }}
                            </div>
                            <div class="row-body">
                                <p class="row-name">{{ $fb->user->fullname ?? 'Unknown User' }}</p>
                                <p class="row-sub">{{ Str::limit($fb->subject, 45) }}</p>
                                <p class="row-time">{{ $fb->created_at->diffForHumans() }}</p>
                            </div>
                            @if(!$fb->is_read)
                                <span class="unread-dot"></span>
                            @endif
                        </div>
                    @empty
                        <div class="panel-empty">
                            <i class="fas fa-inbox"></i>
                            <p>No feedbacks yet</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pending Reports --}}
                <div class="panel">
                    <div class="panel-head">
                        <h2><i class="fas fa-flag"></i> Pending Reports</h2>
                        <a href="{{ route('admin.reports') }}" class="panel-link">View all</a>
                    </div>

                    @forelse($recentReports as $report)
                        <div class="panel-row">
                            <div class="row-avatar orange">
                                {{ strtoupper(substr($report->reporter->fullname ?? 'U', 0, 1)) }}
                            </div>
                            <div class="row-body">
                                <p class="row-name">
                                    {{ $report->reporter->fullname ?? 'Unknown' }}
                                    <span class="row-muted"> reported </span>
                                    {{ $report->reportedUser->fullname ?? 'Unknown' }}
                                </p>
                                <p class="row-sub">{{ Str::limit($report->reason, 45) }}</p>
                                <p class="row-time">{{ $report->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="badge yellow">Pending</span>
                        </div>
                    @empty
                        <div class="panel-empty">
                            <i class="fas fa-check-circle"></i>
                            <p>No pending reports</p>
                        </div>
                    @endforelse
                </div>

            </div>

        </main>
    </div>

</div>

<script src="{{ asset('js/adminDashboard.js') }}"></script>
</body>
</html>