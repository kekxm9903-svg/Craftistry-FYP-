<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Feedbacks — Craftistry Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/adminFeedbacks.css') }}">
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
            <a href="{{ route('admin.feedbacks') }}" class="snav-item active">
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
                <h1>Feedbacks</h1>
                <p>Review and manage user submitted feedback</p>
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
                <form method="GET" action="{{ route('admin.feedbacks') }}" class="filter-form">
                    <div class="filter-pills">

                        {{-- Read status filters --}}
                        <button type="submit" name="status" value=""
                            class="pill {{ !request('status') && !request('category') ? 'active' : '' }}">
                            All
                        </button>
                        <button type="submit" name="status" value="unread"
                            class="pill pill-blue {{ request('status') === 'unread' ? 'active' : '' }}">
                            <i class="fas fa-envelope"></i> Unread
                        </button>
                        <button type="submit" name="status" value="read"
                            class="pill pill-green {{ request('status') === 'read' ? 'active' : '' }}">
                            <i class="fas fa-envelope-open"></i> Read
                        </button>

                        <span class="pill-divider"></span>

                        {{-- Category filters --}}
                        <button type="submit" name="category" value="general"
                            class="pill pill-purple {{ request('category') === 'general' ? 'active' : '' }}">
                            <i class="fas fa-comments"></i> General
                        </button>
                        <button type="submit" name="category" value="bug"
                            class="pill pill-red {{ request('category') === 'bug' ? 'active' : '' }}">
                            <i class="fas fa-bug"></i> Bug
                        </button>
                        <button type="submit" name="category" value="suggestion"
                            class="pill pill-orange {{ request('category') === 'suggestion' ? 'active' : '' }}">
                            <i class="fas fa-lightbulb"></i> Suggestion
                        </button>

                    </div>
                    <div class="filter-meta">
                        <span class="result-count">
                            {{ $feedbacks->total() }} {{ Str::plural('feedback', $feedbacks->total()) }} found
                        </span>
                    </div>
                </form>
            </div>

            {{-- Feedback Table --}}
            <div class="table-card">
                @if($feedbacks->isEmpty())
                    <div class="table-empty">
                        <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                        <p class="empty-title">No feedbacks found</p>
                        <p class="empty-sub">Try adjusting your filter</p>
                        <a href="{{ route('admin.feedbacks') }}" class="btn-outline">Clear filter</a>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="feedback-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Category</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="th-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($feedbacks as $index => $fb)
                                <tr class="feedback-row {{ !$fb->is_read ? 'unread-row' : '' }}">

                                    <td class="td-num">{{ $feedbacks->firstItem() + $index }}</td>

                                    <td class="td-user">
                                        <div class="user-cell">
                                            <div class="user-avatar" style="--hue: {{ crc32($fb->user->email ?? 'x') % 360 }}">
                                                {{ strtoupper(substr($fb->user->fullname ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="user-info">
                                                <p class="user-name">{{ $fb->user->fullname ?? 'Deleted User' }}</p>
                                                <p class="user-email">{{ $fb->user->email ?? '—' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Category badge --}}
                                    <td class="td-category">
                                        @php $cat = $fb->category ?? 'general'; @endphp
                                        @if($cat === 'bug')
                                            <span class="cat-badge cat-bug">
                                                <i class="fas fa-bug"></i> Bug
                                            </span>
                                        @elseif($cat === 'suggestion')
                                            <span class="cat-badge cat-suggestion">
                                                <i class="fas fa-lightbulb"></i> Suggestion
                                            </span>
                                        @else
                                            <span class="cat-badge cat-general">
                                                <i class="fas fa-comments"></i> General
                                            </span>
                                        @endif
                                    </td>

                                    <td class="td-subject">
                                        <span class="subject-text">{{ Str::limit($fb->subject, 40) }}</span>
                                    </td>

                                    <td class="td-message">
                                        <span class="message-preview" title="{{ $fb->message }}">
                                            {{ Str::limit($fb->message, 60) }}
                                        </span>
                                        <button class="view-btn" onclick="openModal({{ $fb->id }})">
                                            View
                                        </button>
                                    </td>

                                    <td class="td-status">
                                        @if($fb->is_read)
                                            <span class="status-badge read">
                                                <i class="fas fa-envelope-open"></i> Read
                                            </span>
                                        @else
                                            <span class="status-badge unread">
                                                <i class="fas fa-envelope"></i> Unread
                                            </span>
                                        @endif
                                    </td>

                                    <td class="td-date">
                                        <span class="date-text">{{ $fb->created_at->format('d M Y') }}</span>
                                        <span class="date-sub">{{ $fb->created_at->diffForHumans() }}</span>
                                    </td>

                                    <td class="td-actions">
                                        <div class="action-group">
                                            @if(!$fb->is_read)
                                                <form method="POST" action="{{ route('admin.feedbacks.read', $fb) }}">
                                                    @csrf
                                                    <button type="submit" class="action-btn mark-read" title="Mark as read">
                                                        <i class="fas fa-check"></i>
                                                        <span>Mark Read</span>
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.feedbacks.delete', $fb) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn delete"
                                                    title="Delete feedback"
                                                    onclick="return confirm('Delete this feedback permanently?')">
                                                    <i class="fas fa-trash"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                </tr>

                                {{-- Hidden modal data --}}
                                <div class="modal-data" id="modal-data-{{ $fb->id }}"
                                    data-name="{{ $fb->user->fullname ?? 'Deleted User' }}"
                                    data-email="{{ $fb->user->email ?? '—' }}"
                                    data-category="{{ $fb->category ?? 'general' }}"
                                    data-subject="{{ $fb->subject }}"
                                    data-message="{{ $fb->message }}"
                                    data-date="{{ $fb->created_at->format('d M Y, g:i A') }}"
                                    data-read="{{ $fb->is_read ? 'true' : 'false' }}"
                                    style="display:none">
                                </div>

                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($feedbacks->hasPages())
                        <div class="pagination-wrap">
                            <div class="pag-info">
                                Showing {{ $feedbacks->firstItem() }}–{{ $feedbacks->lastItem() }} of {{ $feedbacks->total() }}
                            </div>
                            <div class="pag-links">
                                @if($feedbacks->onFirstPage())
                                    <span class="pag-btn disabled"><i class="fas fa-chevron-left"></i></span>
                                @else
                                    <a href="{{ $feedbacks->previousPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-left"></i></a>
                                @endif

                                @foreach($feedbacks->getUrlRange(max(1, $feedbacks->currentPage()-2), min($feedbacks->lastPage(), $feedbacks->currentPage()+2)) as $page => $url)
                                    @if($page == $feedbacks->currentPage())
                                        <span class="pag-btn current">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}" class="pag-btn">{{ $page }}</a>
                                    @endif
                                @endforeach

                                @if($feedbacks->hasMorePages())
                                    <a href="{{ $feedbacks->nextPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-right"></i></a>
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

{{-- ══════════════ FULL MESSAGE MODAL ══════════════ --}}
<div class="modal-overlay" id="modal-overlay" onclick="closeModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-user">
                <div class="modal-avatar" id="modal-avatar">U</div>
                <div>
                    <p class="modal-name" id="modal-name">—</p>
                    <p class="modal-email" id="modal-email">—</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-meta">
            <span id="modal-category-badge"></span>
            <span class="modal-subject" id="modal-subject">—</span>
            <span class="modal-date" id="modal-date">—</span>
        </div>
        <div class="modal-body">
            <p id="modal-message">—</p>
        </div>
    </div>
</div>

<script src="{{ asset('js/adminFeedbacks.js') }}"></script>
</body>
</html>