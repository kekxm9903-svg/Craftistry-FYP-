{{-- ════════════════════════════════════════════════════
     CRAFTISTRY — ARTWORK PREFERENCE MODAL
     Only shown on dashboard, and only once (preference_shown = false)
════════════════════════════════════════════════════ --}}

@auth
@if(
    auth()->user()->shouldShowPreferenceModal()
    && auth()->user()->role !== 'admin'
    && request()->routeIs('dashboard')
)

@php
    $artworkTypes = \App\Models\ArtworkType::orderBy('name')->get();
@endphp

<link rel="stylesheet" href="{{ asset('css/preference.css') }}">

<div class="pref-overlay" id="pref-overlay" role="dialog" aria-modal="true" aria-labelledby="pref-title">
    <div class="pref-modal" id="pref-modal">

        {{-- Header --}}
        <div class="pref-header">
            <div class="pref-header-icon">
                <i class="fas fa-palette"></i>
            </div>
            <div>
                <h2 class="pref-title" id="pref-title">
                    Welcome, {{ auth()->user()->fullname ?? 'there' }}! 👋
                </h2>
                <p class="pref-subtitle">
                    What kind of artwork interests you most? We'll personalise what you see first.
                </p>
            </div>
        </div>

        {{-- Artwork type grid --}}
        <div class="pref-grid" id="pref-grid">
            @foreach($artworkTypes as $type)
            <button class="pref-option"
                    data-value="{{ $type->name }}"
                    onclick="selectPref(this)"
                    type="button">
                <div class="pref-option-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <div class="pref-option-name">{{ $type->name }}</div>
                <div class="pref-option-check"><i class="fas fa-check"></i></div>
            </button>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="pref-footer">
            <button class="pref-btn-skip" type="button" onclick="skipPref()">
                Skip for now
            </button>
            <button class="pref-btn-save" id="pref-save" type="button" onclick="savePref()" disabled>
                <i class="fas fa-check"></i> Save Preference
            </button>
        </div>

        <p class="pref-note">
            <i class="fas fa-info-circle"></i>
            You can change this anytime in your profile settings.
        </p>

    </div>
</div>

{{-- Pass route URLs to JS — CSRF is read from meta tag at runtime --}}
<script>
    window.PREF_STORE_URL = '{{ route('preference.store') }}';
    window.PREF_SKIP_URL  = '{{ route('preference.skip') }}';
</script>
<script src="{{ asset('js/preference.js') }}"></script>

@endif
@endauth