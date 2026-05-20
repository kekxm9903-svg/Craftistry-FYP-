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
                Craftistry
                <em>Admin Panel</em>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="snav-item">
                <i class="fas fa-th-large"></i><span>Dashboard</span>
            </a>

            @if(auth()->user()->canAccessAdminModule('users'))
            <a href="{{ route('admin.users') }}" class="snav-item">
                <i class="fas fa-users"></i><span>Users</span>
            </a>
            @endif

            @if(auth()->user()->canAccessAdminModule('feedbacks'))
            <a href="{{ route('admin.feedbacks') }}" class="snav-item">
                <i class="fas fa-comment-alt"></i><span>Feedbacks</span>
            </a>
            @endif

            @if(auth()->user()->canAccessAdminModule('reports'))
            <a href="{{ route('admin.reports') }}" class="snav-item">
                <i class="fas fa-flag"></i><span>Reports</span>
            </a>
            @endif

            @if(auth()->user()->canAccessAdminModule('admins'))
            <a href="{{ route('admin.admins') }}" class="snav-item active">
                <i class="fas fa-user-shield"></i><span>Admins</span>
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="sf-user">
                <div class="sf-avatar">
                    {{ strtoupper(substr(auth()->user()->fullname ?? 'A', 0, 1)) }}
                </div>
                <div class="sf-info">
                    <p class="sf-name">{{ auth()->user()->fullname }}</p>
                    <p class="sf-role">{{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Administrator' }}</p>
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

            <div class="page-header">
                <h1>Admins</h1>
                <p>Manage administrator accounts and their module access permissions</p>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div class="admin-alert alert-success" id="admin-alert">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                    <button class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="admin-alert alert-error" id="admin-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                    <button class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
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

                        {{-- Email --}}
                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address <span class="required">*</span>
                            </label>
                            <input type="email" name="email" id="email" class="form-input"
                                placeholder="admin@craftistry.com"
                                value="{{ old('email') }}"
                                required autocomplete="off">
                            @error('email')
                                <span class="field-error">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                            <span class="field-hint">If this email already exists as a user, they will be promoted to admin.</span>
                        </div>

                        <div class="form-divider"><span>New account only (optional if email exists)</span></div>

                        {{-- Full Name --}}
                        <div class="form-group {{ $errors->has('fullname') ? 'has-error' : '' }}">
                            <label for="fullname">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input type="text" name="fullname" id="fullname" class="form-input"
                                placeholder="e.g. John Doe"
                                value="{{ old('fullname') }}"
                                autocomplete="off">
                            @error('fullname')
                                <span class="field-error">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label for="password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="password-wrap">
                                <input type="password" name="password" id="password" class="form-input"
                                    placeholder="Minimum 8 characters"
                                    autocomplete="new-password">
                                <button type="button" class="pw-toggle" id="pw-toggle" title="Show/hide password">
                                    <i class="fas fa-eye" id="pw-icon"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="field-error">
                                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        {{-- Module Permissions --}}
                        <div class="form-divider"><span>Module access permissions</span></div>

                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Allow Access To</label>
                            <div class="perm-grid">
                                @foreach($modules as $key => $label)
                                <label class="perm-toggle {{ in_array($key, old('permissions', [])) ? 'is-checked' : '' }}">
                                    <input type="checkbox"
                                        name="permissions[]"
                                        value="{{ $key }}"
                                        class="perm-cb"
                                        {{ in_array($key, old('permissions', [])) ? 'checked' : '' }}>
                                    <span class="perm-tile">
                                        <i class="fas {{ $key === 'users' ? 'fa-users' : ($key === 'feedbacks' ? 'fa-comment-alt' : 'fa-flag') }}"></i>
                                        <span>{{ $label }}</span>
                                    </span>
                                </label>
                                @endforeach
                            </div>
                            <span class="field-hint">Select which sections this admin can access in the panel.</span>
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
                            <div class="admin-row {{ $admin->id === auth()->id() ? 'is-you' : '' }} {{ $admin->isSuperAdmin() ? 'is-super' : '' }}">

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
                                            <span class="badge-you">You</span>
                                        @endif
                                        @if($admin->isSuperAdmin())
                                            <span class="badge-super"><i class="fas fa-crown"></i> Super Admin</span>
                                        @endif
                                    </p>
                                    <p class="admin-email">{{ $admin->email }}</p>
                                    <p class="admin-since">Admin since {{ $admin->created_at->format('d M Y') }}</p>

                                    {{-- Permission pills --}}
                                    <div class="perm-pills">
                                        @if($admin->isSuperAdmin())
                                            <span class="pill pill-all">
                                                <i class="fas fa-infinity"></i> Full Access
                                            </span>
                                        @else
                                            @php $perms = $admin->admin_permissions ?? []; @endphp
                                            @forelse($perms as $perm)
                                                <span class="pill pill-{{ $perm }}">
                                                    <i class="fas {{ $perm === 'users' ? 'fa-users' : ($perm === 'feedbacks' ? 'fa-comment-alt' : 'fa-flag') }}"></i>
                                                    {{ ucfirst($perm) }}
                                                </span>
                                            @empty
                                                <span class="pill pill-none">
                                                    <i class="fas fa-ban"></i> No Access
                                                </span>
                                            @endforelse
                                        @endif
                                    </div>
                                </div>

                                <div class="admin-actions">
                                    @if($admin->id !== auth()->id() && ! $admin->isSuperAdmin())

                                        <button type="button" class="action-btn btn-edit-perm"
                                            title="Edit permissions"
                                            data-id="{{ $admin->id }}"
                                            data-name="{{ $admin->fullname }}"
                                            data-perms="{{ json_encode($admin->admin_permissions ?? []) }}"
                                            data-action="{{ route('admin.admins.updatePermissions', $admin) }}">
                                            <i class="fas fa-sliders-h"></i>
                                        </button>

                                        <button type="button" class="action-btn btn-remove"
                                            title="Remove admin"
                                            data-id="{{ $admin->id }}"
                                            data-name="{{ $admin->fullname }}"
                                            data-action="{{ route('admin.admins.remove', $admin) }}">
                                            <i class="fas fa-user-minus"></i>
                                        </button>

                                    @elseif($admin->id === auth()->id())
                                        <span class="action-lock" title="You cannot modify yourself">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @else
                                        <span class="action-lock" title="Super admin cannot be modified">
                                            <i class="fas fa-crown"></i>
                                        </span>
                                    @endif
                                </div>

                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div><!-- /.two-col -->

        </main>
    </div>

</div><!-- /.admin-wrapper -->

{{-- ══════════════ EDIT PERMISSIONS MODAL ══════════════ --}}
<div class="modal-backdrop" id="editPermModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-sliders-h"></i> Edit Permissions</h3>
            <button class="modal-x" id="editPermClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p class="modal-desc">Select which modules <strong id="editPermName"></strong> can access:</p>
            <form method="POST" id="editPermForm">
                @csrf
                <div class="perm-grid modal-perm-grid">
                    @foreach($modules as $key => $label)
                    <label class="perm-toggle" id="modal-perm-wrap-{{ $key }}">
                        <input type="checkbox"
                            name="permissions[]"
                            value="{{ $key }}"
                            class="perm-cb modal-perm-cb"
                            data-module="{{ $key }}">
                        <span class="perm-tile">
                            <i class="fas {{ $key === 'users' ? 'fa-users' : ($key === 'feedbacks' ? 'fa-comment-alt' : 'fa-flag') }}"></i>
                            <span>{{ $label }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="editPermCancel">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════ REMOVE CONFIRM MODAL ══════════════ --}}
<div class="modal-backdrop" id="removeModal">
    <div class="modal-box modal-box--sm">
        <div class="modal-head">
            <h3><i class="fas fa-user-minus"></i> Remove Admin</h3>
            <button class="modal-x" id="removeClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p class="modal-desc">Remove admin privileges from <strong id="removeName"></strong>?</p>
            <p class="modal-warn"><i class="fas fa-exclamation-triangle"></i> They will become a regular user and lose all panel access.</p>
            <div class="modal-foot">
                <button type="button" class="btn-ghost" id="removeCancel">Cancel</button>
                <form method="POST" id="removeForm" style="display:contents">
                    @csrf
                    <button type="submit" class="btn-danger">
                        <i class="fas fa-user-minus"></i> Yes, Remove
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/adminAdmins.js') }}"></script>
</body>
</html>