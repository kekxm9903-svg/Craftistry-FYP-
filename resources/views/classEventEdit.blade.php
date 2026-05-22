@extends('layouts.app')

@section('title', 'Edit Class/Event - Craftistry')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/classEvent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/classEventForm.css') }}">
    <style>
        /* ── Social link input with prefix icon ── */
        .ce-social-wrap {
            display: flex;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            overflow: hidden;
            transition: border-color .15s, box-shadow .15s;
        }
        .ce-social-wrap:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102,126,234,.1);
        }
        .ce-social-prefix {
            display: flex; align-items: center; justify-content: center;
            width: 42px; flex-shrink: 0;
            background: var(--lavender);
            border-right: 1.5px solid var(--border);
            font-size: 1rem;
        }
        .ce-social-prefix.ig { color: #e1306c; }
        .ce-social-prefix.fb { color: #1877f2; }
        .ce-social-prefix.x  { color: #000; }
        .ce-social-input {
            border: none !important; border-radius: 0 !important; box-shadow: none !important;
            flex: 1; min-width: 0; padding: 9px 14px;
            font-family: 'Inter', sans-serif; font-size: var(--fs-base); color: var(--ink);
            background: var(--white);
        }
        .ce-social-input:focus { outline: none; }
        .ce-social-input::placeholder { color: #bbb; }
    </style>
@endsection

@section('content')

<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <a href="{{ route('class.event.index') }}">Classes & Events</a>
        <span class="sep">/</span>
        <span class="cur">Edit</span>
    </div>
</div>

<main class="main">

    <a href="{{ route('class.event.index') }}" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Classes & Events
    </a>

    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">Edit Class/Event</div>
            <div class="page-subtitle">Updating: <strong>{{ $classEvent->title }}</strong></div>
        </div>
    </div>

    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Class / Event Details
            </div>
        </div>
        <div class="sp-card-body">

            <form id="editForm" action="{{ route('class.event.update', $classEvent->id) }}"
                  method="POST" enctype="multipart/form-data" class="form-page">
                @csrf
                @method('PUT')

                {{-- Current Image --}}
                <div class="form-group">
                    <label class="form-label">Current Image</label>
                    <div class="current-image-preview">
                        <img src="{{ $classEvent->poster_url }}" alt="Current poster">
                    </div>
                </div>

                {{-- New Image --}}
                <div class="form-group">
                    <label class="form-label">Update Image <span class="form-label-opt">(optional)</span></label>
                    <p class="allowed-formats">Leave blank to keep the current image</p>
                    <div class="upload-area" id="editUploadArea">
                        <div class="upload-icon"><i class="bi bi-cloud-upload-fill"></i></div>
                        <div class="upload-text">Click to upload a new image</div>
                        <div class="upload-hint">JPEG, JPG, PNG, GIF, WEBP (Max 5MB)</div>
                        <input type="file" id="editPosterImage" name="poster_image" class="file-input"
                               accept=".jpg,.jpeg,.png,.gif,.webp">
                    </div>
                    <div class="file-preview" id="editFilePreview">
                        <div class="file-name">
                            <i class="bi bi-image-fill"></i>
                            <span id="editFileNameText"></span>
                            <button type="button" class="remove-file" id="editRemoveFile">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <img id="editPreviewImage" class="preview-image" alt="Preview">
                    </div>
                    @error('poster_image')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Title --}}
                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-input" required maxlength="255"
                           value="{{ old('title', $classEvent->title) }}">
                    @error('title')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Fee --}}
                <div class="form-group">
                    <label class="form-label">Fee <span class="required">*</span></label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="editFeeFree" name="is_paid" value="0"
                                   {{ old('is_paid', (int)$classEvent->is_paid) == '0' ? 'checked' : '' }}>
                            <label for="editFeeFree"><i class="bi bi-gift-fill"></i> Free</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="editFeePaid" name="is_paid" value="1"
                                   {{ old('is_paid', (int)$classEvent->is_paid) == '1' ? 'checked' : '' }}>
                            <label for="editFeePaid"><i class="bi bi-tag-fill"></i> Paid</label>
                        </div>
                    </div>
                    <div class="conditional-field {{ old('is_paid', (int)$classEvent->is_paid) == '1' ? 'active' : '' }}"
                         id="editPriceField" style="margin-top:10px;">
                        <label class="form-label">Price (RM) <span class="required">*</span></label>
                        <div class="price-input-wrapper">
                            <span class="price-prefix">RM</span>
                            <input type="number" id="editPrice" name="price" class="form-input price-input"
                                   placeholder="0.00" min="0.01" max="99999.99" step="0.01"
                                   value="{{ old('price', $classEvent->price ? number_format($classEvent->price, 2, '.', '') : '') }}">
                        </div>
                        @error('price')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="char-count">Enter the price in Malaysian Ringgit (RM).</span>
                    </div>
                </div>

                {{-- Media --}}
                <div class="form-group">
                    <label class="form-label">Media <span class="required">*</span></label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="editMediaOnline" name="media_type" value="online"
                                   {{ old('media_type', $classEvent->media_type) == 'online' ? 'checked' : '' }}>
                            <label for="editMediaOnline"><i class="bi bi-laptop"></i> Online</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="editMediaPhysical" name="media_type" value="physical"
                                   {{ old('media_type', $classEvent->media_type) == 'physical' ? 'checked' : '' }}>
                            <label for="editMediaPhysical"><i class="bi bi-geo-alt-fill"></i> Physical</label>
                        </div>
                    </div>
                    <div class="conditional-field {{ old('media_type', $classEvent->media_type) == 'online' ? 'active' : '' }}"
                         id="editPlatformField">
                        <label class="form-label">Platform <span class="required">*</span></label>
                        <input type="text" id="editPlatform" name="platform" class="form-input" maxlength="255"
                               value="{{ old('platform', $classEvent->platform) }}">
                        @error('platform')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="conditional-field {{ old('media_type', $classEvent->media_type) == 'physical' ? 'active' : '' }}"
                         id="editLocationField">
                        <label class="form-label">Location <span class="required">*</span></label>
                        <input type="text" id="editLocation" name="location" class="form-input" maxlength="255"
                               value="{{ old('location', $classEvent->location) }}">
                        @error('location')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Dates --}}
                @php
                    $startDate            = old('start_date',            $classEvent->start_date            ? $classEvent->start_date->format('Y-m-d')                                                        : '');
                    $endDate              = old('end_date',              $classEvent->end_date              ? $classEvent->end_date->format('Y-m-d')                                                          : '');
                    $enrollmentDeadline   = old('enrollment_deadline',   $classEvent->enrollment_deadline   ? $classEvent->enrollment_deadline->format('Y-m-d')                                               : '');
                    $cancellationDeadline = old('cancellation_deadline', $classEvent->cancellation_deadline ? $classEvent->cancellation_deadline->format('Y-m-d')                                             : '');
                    $startTime            = old('start_time',            $classEvent->start_time            ? \Carbon\Carbon::parse($classEvent->start_time)->format('H:i')                                  : '');
                    $endTime              = old('end_time',              $classEvent->end_time              ? \Carbon\Carbon::parse($classEvent->end_time)->format('H:i')                                    : '');
                @endphp

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date <span class="required">*</span></label>
                        <input type="date" name="start_date" class="form-input" required
                               value="{{ $startDate }}">
                        @error('start_date')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date <span class="required">*</span></label>
                        <input type="date" name="end_date" class="form-input" required
                               value="{{ $endDate }}">
                        @error('end_date')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Deadlines — required --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Enrollment Deadline <span class="required">*</span></label>
                        <input type="date" name="enrollment_deadline" class="form-input" required
                               value="{{ $enrollmentDeadline }}">
                        @error('enrollment_deadline')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cancellation Deadline <span class="required">*</span></label>
                        <input type="date" name="cancellation_deadline" class="form-input" required
                               value="{{ $cancellationDeadline }}">
                        @error('cancellation_deadline')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Enrollment Form --}}
                <div class="form-group">
                    <label class="form-label">Require Enrollment Form?</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="editRequireFormNo" name="require_form" value="0"
                                   {{ old('require_form', (int)$classEvent->require_form) == '0' ? 'checked' : '' }}>
                            <label for="editRequireFormNo"><i class="bi bi-x-circle-fill"></i> No — direct enroll</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="editRequireFormYes" name="require_form" value="1"
                                   {{ old('require_form', (int)$classEvent->require_form) == '1' ? 'checked' : '' }}>
                            <label for="editRequireFormYes"><i class="bi bi-file-earmark-text-fill"></i> Yes — Google Form</label>
                        </div>
                    </div>
                    <div class="conditional-field {{ old('require_form', (int)$classEvent->require_form) == '1' ? 'active' : '' }}"
                         id="editFormUrlField" style="margin-top:10px;">
                        <label class="form-label">Google Form URL</label>
                        <input type="url" name="enrollment_form_url" class="form-input"
                               placeholder="https://docs.google.com/forms/..." maxlength="2048"
                               value="{{ old('enrollment_form_url', $classEvent->enrollment_form_url) }}">
                        @error('enrollment_form_url')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="char-count">Paste your Google Form link.</span>
                    </div>
                </div>

                {{-- Participant Limit --}}
                <div class="form-group">
                    <label class="form-label">Participant Limit <span class="form-label-opt">(optional)</span></label>
                    <input type="number" name="max_participants" class="form-input"
                           placeholder="e.g. 20" min="1" max="99999"
                           value="{{ old('max_participants', $classEvent->max_participants) }}">
                    <span class="char-count">Leave blank for unlimited.</span>
                </div>

                {{-- Times --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Time <span class="required">*</span></label>
                        <input type="time" id="editStartTime" name="start_time" class="form-input" required
                               value="{{ $startTime }}">
                        @error('start_time')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time <span class="required">*</span></label>
                        <input type="time" id="editEndTime" name="end_time" class="form-input" required
                               value="{{ $endTime }}">
                        @error('end_time')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="form-group">
                    <label class="form-label">Description <span class="form-label-opt">(optional)</span></label>
                    <textarea id="editEventDescription" name="description" class="form-textarea"
                              rows="4" maxlength="1000">{{ old('description', $classEvent->description) }}</textarea>
                    <span class="char-count" id="editDescCount">
                        {{ strlen(old('description', $classEvent->description ?? '')) }} / 1000 characters
                    </span>
                </div>

                {{-- ── Social Links ── --}}
                <div class="form-group" style="border-top:1px solid var(--divider);padding-top:var(--sp-lg);margin-top:var(--sp-sm);">
                    <label class="form-label" style="font-size:var(--fs-base);font-weight:700;display:flex;align-items:center;gap:6px;margin-bottom:var(--sp-md);">
                        <i class="bi bi-share-fill" style="color:var(--primary);"></i>
                        Social Links <span class="form-label-opt">(optional)</span>
                    </label>

                    <div style="display:flex;flex-direction:column;gap:14px;">

                        <div>
                            <label class="form-label" style="font-size:.85rem;">Instagram</label>
                            <div class="ce-social-wrap">
                                <span class="ce-social-prefix ig"><i class="bi bi-instagram"></i></span>
                                <input type="url" name="instagram_url" class="ce-social-input"
                                       placeholder="https://instagram.com/yourpage"
                                       maxlength="2048"
                                       value="{{ old('instagram_url', $classEvent->instagram_url) }}">
                            </div>
                            @error('instagram_url')<span class="error-message">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="form-label" style="font-size:.85rem;">Facebook</label>
                            <div class="ce-social-wrap">
                                <span class="ce-social-prefix fb"><i class="bi bi-facebook"></i></span>
                                <input type="url" name="facebook_url" class="ce-social-input"
                                       placeholder="https://facebook.com/yourpage"
                                       maxlength="2048"
                                       value="{{ old('facebook_url', $classEvent->facebook_url) }}">
                            </div>
                            @error('facebook_url')<span class="error-message">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="form-label" style="font-size:.85rem;">X (Twitter)</label>
                            <div class="ce-social-wrap">
                                <span class="ce-social-prefix x"><i class="bi bi-twitter-x"></i></span>
                                <input type="url" name="x_url" class="ce-social-input"
                                       placeholder="https://x.com/yourhandle"
                                       maxlength="2048"
                                       value="{{ old('x_url', $classEvent->x_url) }}">
                            </div>
                            @error('x_url')<span class="error-message">{{ $message }}</span>@enderror
                        </div>

                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('class.event.index') }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-floppy-fill"></i> Update Class/Event
                    </button>
                </div>

            </form>
        </div>
    </div>

</main>
@endsection

@section('scripts')
<script>
    // Fee toggle
    document.querySelectorAll('input[name="is_paid"]').forEach(r => {
        r.addEventListener('change', function () {
            const show = this.value === '1';
            document.getElementById('editPriceField').classList.toggle('active', show);
            document.getElementById('editPrice').required = show;
            if (!show) document.getElementById('editPrice').value = '';
        });
    });

    // Media toggle
    document.querySelectorAll('input[name="media_type"]').forEach(r => {
        r.addEventListener('change', function () {
            const online = this.value === 'online';
            document.getElementById('editPlatformField').classList.toggle('active', online);
            document.getElementById('editLocationField').classList.toggle('active', !online);
            document.getElementById('editPlatform').required = online;
            document.getElementById('editLocation').required = !online;
        });
    });

    // Enrollment form toggle
    document.querySelectorAll('input[name="require_form"]').forEach(r => {
        r.addEventListener('change', function () {
            const show = this.value === '1';
            document.getElementById('editFormUrlField').classList.toggle('active', show);
        });
    });

    // Description counter
    const desc = document.getElementById('editEventDescription');
    desc.addEventListener('input', function () {
        document.getElementById('editDescCount').textContent = this.value.length + ' / 1000 characters';
    });

    // Image preview
    const fileInput   = document.getElementById('editPosterImage');
    const uploadArea  = document.getElementById('editUploadArea');
    const filePreview = document.getElementById('editFilePreview');

    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('dragover',  e => { e.preventDefault(); uploadArea.classList.add('dragover'); });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
    uploadArea.addEventListener('drop', e => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', function () {
        if (this.files[0]) handleFile(this.files[0]);
    });
    document.getElementById('editRemoveFile').addEventListener('click', () => {
        fileInput.value = '';
        filePreview.classList.remove('active');
        uploadArea.style.display = '';
    });

    function handleFile(file) {
        document.getElementById('editFileNameText').textContent = file.name;
        const reader = new FileReader();
        reader.onload = e => { document.getElementById('editPreviewImage').src = e.target.result; };
        reader.readAsDataURL(file);
        filePreview.classList.add('active');
        uploadArea.style.display = 'none';
    }
</script>
@endsection