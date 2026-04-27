<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reports — Craftistry Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/adminReports.css') }}">
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
            <a href="{{ route('admin.reports') }}" class="snav-item active">
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
                <h1>Reports</h1>
                <p>Review user reports and take action on suspicious accounts</p>
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
                <form method="GET" action="{{ route('admin.reports') }}" class="filter-form">
                    <div class="filter-pills">
                        <button type="submit" name="status" value="" class="pill {{ !request('status') ? 'active' : '' }}">
                            All
                        </button>
                        <button type="submit" name="status" value="pending" class="pill pill-orange {{ request('status') === 'pending' ? 'active' : '' }}">
                            <i class="fas fa-clock"></i> Pending
                        </button>
                        <button type="submit" name="status" value="reviewed" class="pill pill-blue {{ request('status') === 'reviewed' ? 'active' : '' }}">
                            <i class="fas fa-eye"></i> Reviewed
                        </button>
                        <button type="submit" name="status" value="dismissed" class="pill pill-gray {{ request('status') === 'dismissed' ? 'active' : '' }}">
                            <i class="fas fa-times-circle"></i> Dismissed
                        </button>
                    </div>
                    <div class="filter-meta">
                        <span class="result-count">{{ $reports->total() }} {{ Str::plural('report', $reports->total()) }} found</span>
                    </div>
                </form>
            </div>

            {{-- Reports Table --}}
            <div class="table-card">
                @if($reports->isEmpty())
                    <div class="table-empty">
                        <div class="empty-icon"><i class="fas fa-flag"></i></div>
                        <p class="empty-title">No reports found</p>
                        <p class="empty-sub">Try adjusting your filter</p>
                        <a href="{{ route('admin.reports') }}" class="btn-outline">Clear filter</a>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="reports-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reporter</th>
                                    <th>Reported User</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="th-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $index => $report)
                                <tr class="report-row {{ $report->status === 'pending' ? 'pending-row' : '' }}">

                                    <td class="td-num">{{ $reports->firstItem() + $index }}</td>

                                    {{-- Reporter --}}
                                    <td class="td-user">
                                        <div class="user-cell">
                                            <div class="user-avatar reporter-av" style="--hue: {{ crc32($report->reporter->email ?? 'x') % 360 }}">
                                                {{ strtoupper(substr($report->reporter->fullname ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="user-info">
                                                <p class="user-name">{{ $report->reporter->fullname ?? 'Deleted User' }}</p>
                                                <p class="user-email">{{ $report->reporter->email ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Reported User --}}
                                    <td class="td-user">
                                        <div class="user-cell">
                                            <div class="user-avatar reported-av" style="--hue: {{ crc32($report->reportedUser->email ?? 'y') % 360 }}">
                                                {{ strtoupper(substr($report->reportedUser->fullname ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="user-info">
                                                <p class="user-name">{{ $report->reportedUser->fullname ?? 'Deleted User' }}</p>
                                                <p class="user-email">{{ $report->reportedUser->email ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Reason --}}
                                    <td class="td-reason">
                                        <span class="reason-text" title="{{ $report->details }}">
                                            {{ Str::limit($report->reason, 40) }}
                                        </span>
                                        @if($report->details)
                                            <button class="view-btn" onclick="openModal({{ $report->id }})">View details</button>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="td-status">
                                        @if($report->status === 'pending')
                                            <span class="status-badge pending"><i class="fas fa-clock"></i> Pending</span>
                                        @elseif($report->status === 'reviewed')
                                            <span class="status-badge reviewed"><i class="fas fa-eye"></i> Reviewed</span>
                                        @else
                                            <span class="status-badge dismissed"><i class="fas fa-times-circle"></i> Dismissed</span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="td-date">
                                        <span class="date-text">{{ $report->created_at->format('d M Y') }}</span>
                                        <span class="date-sub">{{ $report->created_at->diffForHumans() }}</span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="td-actions">
                                        <div class="action-group">

                                            {{-- Ban reported user --}}
                                            @if($report->reportedUser && $report->reportedUser->artist_status !== 'banned' && $report->reportedUser->role !== 'admin')
                                                <button class="action-btn ban"
                                                    onclick="openBanModal({{ $report->id }}, '{{ addslashes($report->reportedUser->fullname) }}', {{ $report->reportedUser->id }})"
                                                    title="Ban reported user">
                                                    <i class="fas fa-ban"></i>
                                                    <span>Ban</span>
                                                </button>
                                            @elseif($report->reportedUser && $report->reportedUser->artist_status === 'banned')
                                                <form method="POST" action="{{ route('admin.users.unban', $report->reportedUser) }}">
                                                    @csrf
                                                    <button type="submit" class="action-btn unban" title="Unban user"
                                                        onclick="return confirm('Unban {{ addslashes($report->reportedUser->fullname) }}?')">
                                                        <i class="fas fa-check-circle"></i>
                                                        <span>Unban</span>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Status dropdown --}}
                                            <div class="status-dropdown-wrap">
                                                <button class="action-btn status-toggle" title="Update status">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="status-dropdown">
                                                    <form method="POST" action="{{ route('admin.reports.status', $report) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="pending">
                                                        <button type="submit" class="dropdown-item {{ $report->status === 'pending' ? 'active' : '' }}">
                                                            <i class="fas fa-clock"></i> Pending
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.reports.status', $report) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="reviewed">
                                                        <button type="submit" class="dropdown-item {{ $report->status === 'reviewed' ? 'active' : '' }}">
                                                            <i class="fas fa-eye"></i> Reviewed
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.reports.status', $report) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="dismissed">
                                                        <button type="submit" class="dropdown-item {{ $report->status === 'dismissed' ? 'active' : '' }}">
                                                            <i class="fas fa-times-circle"></i> Dismiss
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                    </td>

                                </tr>

                                {{-- Hidden modal data --}}
                                <div class="modal-data" id="modal-data-{{ $report->id }}"
                                    data-reporter="{{ $report->reporter->fullname ?? 'Deleted User' }}"
                                    data-reported="{{ $report->reportedUser->fullname ?? 'Deleted User' }}"
                                    data-reason="{{ $report->reason }}"
                                    data-details="{{ $report->details }}"
                                    data-date="{{ $report->created_at->format('d M Y, g:i A') }}"
                                    data-status="{{ $report->status }}"
                                    style="display:none">
                                </div>

                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($reports->hasPages())
                        <div class="pagination-wrap">
                            <div class="pag-info">
                                Showing {{ $reports->firstItem() }}–{{ $reports->lastItem() }} of {{ $reports->total() }}
                            </div>
                            <div class="pag-links">
                                @if($reports->onFirstPage())
                                    <span class="pag-btn disabled"><i class="fas fa-chevron-left"></i></span>
                                @else
                                    <a href="{{ $reports->previousPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-left"></i></a>
                                @endif

                                @foreach($reports->getUrlRange(max(1, $reports->currentPage()-2), min($reports->lastPage(), $reports->currentPage()+2)) as $page => $url)
                                    @if($page == $reports->currentPage())
                                        <span class="pag-btn current">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="pag-btn">{{ $page }}</a>
                                    @endif
                                @endforeach

                                @if($reports->hasMorePages())
                                    <a href="{{ $reports->nextPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-right"></i></a>
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

{{-- ══════════════ REPORT DETAIL MODAL ══════════════ --}}
<div class="modal-overlay" id="modal-overlay" onclick="closeModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3><i class="fas fa-flag"></i> Report Details</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="modal-row">
                <span class="modal-label">Reporter</span>
                <span class="modal-value" id="modal-reporter">—</span>
            </div>
            <div class="modal-row">
                <span class="modal-label">Reported User</span>
                <span class="modal-value" id="modal-reported">—</span>
            </div>
            <div class="modal-row">
                <span class="modal-label">Reason</span>
                <span class="modal-value" id="modal-reason">—</span>
            </div>
            <div class="modal-row">
                <span class="modal-label">Status</span>
                <span class="modal-value" id="modal-status">—</span>
            </div>
            <div class="modal-row">
                <span class="modal-label">Date</span>
                <span class="modal-value" id="modal-date">—</span>
            </div>
            <div class="modal-details-wrap">
                <span class="modal-label">Details</span>
                <p class="modal-details" id="modal-details">—</p>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ BAN CONFIRM MODAL ══════════════ --}}
<div class="modal-overlay" id="ban-overlay" onclick="closeBanModal()">
    <div class="modal-box ban-modal-box" onclick="event.stopPropagation()">
        <div class="modal-header ban-header">
            <div class="ban-icon"><i class="fas fa-ban"></i></div>
            <button class="modal-close" onclick="closeBanModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="ban-body">
            <h3 class="ban-title">Ban User?</h3>
            <p class="ban-desc">You are about to ban <strong id="ban-username">this user</strong>. They will lose access to the platform immediately.</p>
            <div class="ban-actions">
                <button class="btn-cancel" onclick="closeBanModal()">Cancel</button>
                <form id="ban-form" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn-ban-confirm">
                        <i class="fas fa-ban"></i> Yes, Ban User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/adminReports.js') }}"></script>
</body>
</html>