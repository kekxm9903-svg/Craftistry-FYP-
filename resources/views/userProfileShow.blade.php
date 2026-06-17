@extends('layouts.app')

@section('title', 'My Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/userProfileShow.css') }}">

<style>
/* ── Override success-toast to match the light pastel green style used across the app ── */
.success-toast {
    position: fixed;
    top: 80px;
    right: var(--sp-lg, 20px);
    z-index: 999;
    background: #d1fae5;
    border: 1px solid #6ee7b7;
    border-radius: 10px;
    padding: 16px 20px;
    box-shadow: 0 4px 12px rgba(16,185,129,.15);
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 280px;
    color: #065f46;
    font-size: 13px;
    font-weight: 600;
    animation: slideInRight .3s ease-out;
}
.success-toast i {
    font-size: 15px;
    color: #065f46;
    flex-shrink: 0;
}
@keyframes slideInRight  { from { opacity:0; transform:translateX(360px); } to { opacity:1; transform:translateX(0); } }
@keyframes slideOutRight { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(360px); } }
@media (max-width: 768px) {
    .success-toast { top:10px; right:10px; left:10px; min-width:auto; }
}
</style>
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">My Profile</span>
    </div>
</div>

<div class="profile-page">

    {{-- Success toast --}}
    @if(session('success'))
        <div class="success-toast" id="successAlert">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ══ PROFILE HEADER CARD ══ --}}
    <div class="sp-card profile-header-card">
        <div class="profile-top">
            <div class="profile-avatar-wrap">
                @if($user->profile_image && $user->profile_image !== 'images/Profile.png')
                    <img src="{{ asset('storage/' . $user->profile_image) }}?v={{ time() }}"
                         alt="{{ $user->fullname }}"
                         class="profile-avatar-img"
                         onerror="this.style.display='none'; document.getElementById('avatar-placeholder').style.display='flex';">
                    <div class="profile-avatar-letter" id="avatar-placeholder" style="display:none;">
                        {{ strtoupper(substr($user->fullname, 0, 1)) }}
                    </div>
                @else
                    <div class="profile-avatar-letter">
                        {{ strtoupper(substr($user->fullname, 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="profile-identity">
                <div class="profile-name">{{ $user->fullname }}</div>
                <div class="profile-email">{{ $user->email }}</div>
            </div>

            <a href="{{ route('user.profile.edit') }}" class="btn-edit-profile">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
        </div>
    </div>

    {{-- ══ PERSONAL INFORMATION CARD ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Personal Information
            </div>
        </div>
        <div class="sp-card-body">
            <div class="info-grid">

                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">{{ $user->fullname }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $user->email }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">{{ $user->phone ?? '—' }}</div>
                </div>

                <div class="info-item info-full">
                    <div class="info-label">Street Address</div>
                    <div class="info-value">{{ $user->address ?? '—' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">City</div>
                    <div class="info-value">{{ $user->city ?? '—' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">State</div>
                    <div class="info-value">{{ $user->state ?? '—' }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Postcode</div>
                    <div class="info-value">{{ $user->postcode ?? '—' }}</div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══ ARTWORK PREFERENCE CARD ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Artwork Preference
            </div>
        </div>
        <div class="sp-card-body">

            {{-- Current preference display --}}
            <div class="pref-current-row">
                <div>
                    <div class="pref-current-label">Your preferred artwork type</div>
                    @if($user->preferred_artwork_type)
                        <div class="pref-current-value">
                            <span class="pref-badge">
                                <i class="fas fa-palette"></i>
                                {{ $user->preferred_artwork_type }}
                            </span>
                            <span class="pref-current-hint">
                                Artworks of this type appear first in your gallery and dashboard.
                            </span>
                        </div>
                    @else
                        <div class="pref-current-value pref-none">
                            No preference set — all artwork types shown equally.
                        </div>
                    @endif
                </div>
                <button class="btn-change-pref" id="btn-open-pref" type="button" onclick="openPrefPanel()">
                    <i class="fas fa-sliders-h"></i>
                    {{ $user->preferred_artwork_type ? 'Change' : 'Set Preference' }}
                </button>
            </div>

            {{-- Inline type picker (hidden by default) --}}
            <div class="pref-panel" id="pref-panel" style="display:none;">
                <div class="pref-panel-label">Select your preferred artwork type:</div>

                @php $artworkTypes = \App\Models\ArtworkType::orderBy('name')->get(); @endphp

                <div class="pref-panel-grid" id="pref-panel-grid">
                    @foreach($artworkTypes as $type)
                    <button class="pref-panel-option {{ $user->preferred_artwork_type === $type->name ? 'selected' : '' }}"
                            data-value="{{ $type->name }}"
                            type="button"
                            onclick="selectPrefPanel(this)">
                        <div class="pref-panel-icon"><i class="fas fa-palette"></i></div>
                        <div class="pref-panel-name">{{ $type->name }}</div>
                        <div class="pref-panel-check"><i class="fas fa-check"></i></div>
                    </button>
                    @endforeach
                </div>

                <div class="pref-panel-actions">
                    <button class="pref-panel-clear" type="button" onclick="clearPrefPanel()">
                        <i class="fas fa-times"></i> Clear preference
                    </button>
                    <button class="pref-panel-cancel" type="button" onclick="closePrefPanel()">
                        Cancel
                    </button>
                    <button class="pref-panel-save" id="pref-panel-save" type="button" onclick="savePrefPanel()" disabled>
                        <i class="fas fa-check"></i> Save
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ SECURITY CARD ══ --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Security
            </div>
        </div>
        <div class="sp-card-body">
            <div class="security-row">
                <div class="security-info">
                    <div class="security-title">Password</div>
                    <div class="security-desc">Manage your account password</div>
                </div>
                <a href="{{ route('user.profile.change-password') }}" class="btn-change-pw">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="{{ asset('js/userProfileShow.js') }}"></script>
<script>
    // ── Auto-hide success toast ──
    const toast = document.getElementById('successAlert');
    if (toast) {
        setTimeout(() => {
            toast.style.animation = 'slideOutRight .4s ease-in forwards';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    }

    // ── Inline preference panel ──
    const PREF_UPDATE_URL = '{{ route('preference.update') }}';
    const CSRF            = '{{ csrf_token() }}';
    let   panelSelected   = null;

    function openPrefPanel() {
        document.getElementById('pref-panel').style.display = 'block';
        document.getElementById('btn-open-pref').style.display = 'none';
        // Pre-select current preference if set
        const current = @json($user->preferred_artwork_type);
        if (current) {
            document.querySelectorAll('.pref-panel-option').forEach(btn => {
                if (btn.dataset.value === current) {
                    btn.classList.add('selected');
                    panelSelected = current;
                    document.getElementById('pref-panel-save').disabled = false;
                }
            });
        }
    }

    function closePrefPanel() {
        document.getElementById('pref-panel').style.display = 'none';
        document.getElementById('btn-open-pref').style.display = 'inline-flex';
        panelSelected = null;
        document.querySelectorAll('.pref-panel-option').forEach(b => b.classList.remove('selected'));
        document.getElementById('pref-panel-save').disabled = true;
    }

    function selectPrefPanel(btn) {
        document.querySelectorAll('.pref-panel-option').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        panelSelected = btn.dataset.value;
        document.getElementById('pref-panel-save').disabled = false;
    }

    function clearPrefPanel() {
        document.querySelectorAll('.pref-panel-option').forEach(b => b.classList.remove('selected'));
        panelSelected = '';
        document.getElementById('pref-panel-save').disabled = false;
    }

    async function savePrefPanel() {
        const saveBtn = document.getElementById('pref-panel-save');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const res = await fetch(PREF_UPDATE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ preferred_artwork_type: panelSelected }),
            });

            if (!res.ok) throw new Error('Failed');
            // Reload to reflect changes
            window.location.reload();

        } catch (err) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-check"></i> Save';
            alert('Something went wrong. Please try again.');
        }
    }
</script>
@endsection