@extends('layouts.app')

@section('title', 'Notifications')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/notification.css') }}">
<style>
    /* ── Breadcrumb ── */
    .bc-bar {
        background: #ffffff;
        border-bottom: 1px solid #e0e0ee;
        padding: 6px 0;
        font-size: 12px;
    }
    .bc-inner {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .bc-inner a { color: #6b6b8a; text-decoration: none; transition: color .15s; }
    .bc-inner a:hover { color: #667eea; }
    .bc-inner .sep { color: #ccc; }
    .bc-inner .cur { color: #1a1a2e; font-weight: 500; }
</style>
@endsection

@section('content')

<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">Notifications</span>
    </div>
</div>

<div style="max-width:800px;margin:0 auto;padding:10px 20px 0;">
    <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:6px;color:#6b6b8a;text-decoration:none;font-size:14px;font-weight:500;">← Back</a>
</div>

<div class="noti-page">

    {{-- Header --}}
    <div class="noti-page-header">
        <div>
            <div class="noti-page-title">Notifications</div>
            <div class="noti-page-sub">
                @php $unread = $notifications->where('read_at', null)->count(); @endphp
                {{ $unread > 0 ? $unread . ' unread' : 'All caught up!' }}
            </div>
        </div>
        @if($unread > 0)
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="noti-mark-all" style="font-size:13px;padding:8px 14px;border-radius:8px;border:1.5px solid #667eea;">
                <i class="fas fa-check-double"></i> Mark all as read
            </button>
        </form>
        @endif
    </div>

    {{-- List --}}
    <div class="noti-page-card">
        @forelse($notifications as $noti)
        <a href="{{ route('notifications.read', $noti->id) }}"
           class="noti-page-item {{ $noti->isUnread() ? 'unread' : '' }}">

            <div class="noti-page-icon" style="background:{{ $noti->color }}">
                <i class="{{ $noti->icon }}"></i>
            </div>

            <div class="noti-page-content">
                <div class="noti-page-title-text">{{ $noti->title }}</div>
                <div class="noti-page-message">{{ $noti->message }}</div>
                <div class="noti-page-time">
                    <i class="fas fa-clock" style="font-size:10px;"></i>
                    {{ $noti->created_at->diffForHumans() }}
                </div>
            </div>

        </a>
        @empty
        <div class="noti-page-empty">
            <i class="fas fa-bell-slash"></i>
            <h3>No notifications yet</h3>
            <p>You'll see order updates, class reminders, and more here.</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div style="display:flex;justify-content:center;padding-top:8px;">
        {{ $notifications->links() }}
    </div>
    @endif

</div>

@endsection