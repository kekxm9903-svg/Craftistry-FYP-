@extends('layouts.app')

@section('title', 'Upload Class/Event - Craftistry')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/classEvent.css') }}">
<style>
.ce-form-wrap { max-width: 700px; margin: 0 auto; }

.ce-section-title {
    font-size: var(--fs-base);
    font-weight: 700;
    color: var(--ink);
    padding-bottom: var(--sp-sm);
    border-bottom: 1px solid var(--divider);
    margin-bottom: var(--sp-lg);
    display: flex;
    align-items: center;
    gap: var(--sp-xs);
}
.ce-section-title i { color: var(--primary); }

.ce-label {
    display: block;
    font-size: var(--fs-base);
    font-weight: 600;
    color: var(--ink);
    margin-bottom: var(--sp-xs);
}
.ce-label .opt { font-weight: 400; color: var(--muted); font-size: .85em; }
.ce-input, .ce-textarea {
    width: 100%;
    padding: 9px var(--sp-md);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: 'Inter', sans-serif;
    font-size: var(--fs-base);
    color: var(--ink);
    background: var(--white);
    transition: border-color .15s;
    box-sizing: border-box;
}
.ce-input:focus, .ce-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102,126,234,.1);
}
.ce-input::placeholder, .ce-textarea::placeholder { color: #bbb; }
.ce-textarea { resize: vertical; min-height: 90px; line-height: 1.6; }
.ce-hint { font-size: var(--fs-sm); color: var(--muted); margin-top: 4px; display: block; }
.ce-err  { font-size: var(--fs-sm); color: var(--danger, #ef4444); margin-top: 4px; display: block; }

.ce-price-wrap {
    display: flex;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
}
.ce-price-wrap:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(102,126,234,.1); }
.ce-price-prefix {
    display: flex; align-items: center; padding: 0 var(--sp-md);
    background: var(--lavender); color: var(--primary);
    font-size: var(--fs-sm); font-weight: 700;
    border-right: 1.5px solid var(--border); white-space: nowrap; user-select: none;
}
.ce-price-input {
    border: none !important; border-radius: 0 !important; box-shadow: none !important;
    flex: 1; min-width: 0; padding: 9px var(--sp-md);
    font-family: 'Inter', sans-serif; font-size: var(--fs-base); color: var(--ink);
}
.ce-price-input:focus { outline: none; box-shadow: none !important; }
.ce-price-input::-webkit-outer-spin-button,
.ce-price-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.ce-price-input[type=number] { -moz-appearance: textfield; appearance: textfield; }

.ce-radio-group { display: flex; gap: var(--sp-lg); flex-wrap: wrap; }
.ce-radio-opt { display: flex; align-items: center; gap: var(--sp-xs); cursor: pointer; }
.ce-radio-opt input { width: 15px; height: 15px; accent-color: var(--primary); cursor: pointer; }
.ce-radio-opt label {
    cursor: pointer; font-size: var(--fs-base); font-weight: 500; color: var(--ink);
    display: flex; align-items: center; gap: 5px; margin: 0;
}

.ce-conditional { display: none; margin-top: var(--sp-md); }
.ce-conditional.active { display: block; }

.ce-upload-area {
    border: 2px dashed var(--border); border-radius: var(--radius-md);
    padding: 32px var(--sp-xl); text-align: center;
    background: var(--divider); cursor: pointer; transition: all .2s;
}
.ce-upload-area:hover, .ce-upload-area.dragover { background: var(--lavender); border-color: var(--primary); }
.ce-upload-area .up-icon { font-size: 2rem; color: var(--primary); margin-bottom: var(--sp-sm); }
.ce-upload-area .up-text { font-size: var(--fs-base); font-weight: 600; color: var(--ink); }
.ce-upload-area .up-hint { font-size: var(--fs-sm); color: var(--muted); margin-top: 4px; }

.ce-file-preview { display: none; margin-top: var(--sp-sm); }
.ce-file-preview.active { display: block; }
.ce-file-name {
    display: flex; align-items: center; gap: var(--sp-sm);
    padding: var(--sp-sm) var(--sp-md);
    background: var(--divider); border-radius: var(--radius-sm);
    font-size: var(--fs-base); color: var(--ink);
}
.ce-remove-file {
    margin-left: auto; background: none; border: none;
    color: var(--danger); cursor: pointer; font-size: var(--fs-md);
    padding: 4px; display: flex; align-items: center;
}
.ce-preview-img { max-width: 100%; max-height: 200px; border-radius: var(--radius-md); margin-top: var(--sp-sm); }

.ce-cur-img { padding: var(--sp-sm); background: var(--divider); border-radius: var(--radius-md); text-align: center; margin-bottom: var(--sp-sm); }
.ce-cur-img img { max-width: 100%; max-height: 200px; border-radius: var(--radius-md); }

.ce-divider { border: none; border-top: 1px solid var(--divider); margin: var(--sp-lg) 0; }

.ce-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-md); }
@media (max-width: 600px) { .ce-two-col { grid-template-columns: 1fr; } }

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
.ce-social-prefix.ig  { color: #e1306c; }
.ce-social-prefix.fb  { color: #1877f2; }
.ce-social-prefix.x   { color: #000; }
.ce-social-input {
    border: none !important; border-radius: 0 !important; box-shadow: none !important;
    flex: 1; min-width: 0; padding: 9px var(--sp-md);
    font-family: 'Inter', sans-serif; font-size: var(--fs-base); color: var(--ink);
    background: var(--white);
}
.ce-social-input:focus { outline: none; box-shadow: none !important; }
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
        <span class="cur">Upload</span>
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
            <div class="page-title">Upload Class/Event</div>
            <div class="page-subtitle">Add a new workshop, class, or event to your studio</div>
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
            <form action="{{ route('class.event.store') }}" method="POST" enctype="multipart/form-data" class="ce-form-wrap">
                @csrf

                {{-- Image --}}
                <div class="ce-section-title"><i class="bi bi-image-fill"></i> Poster Image</div>
                <div style="margin-bottom:var(--sp-lg);">
                    <label class="ce-label">Image <span class="required">*</span></label>
                    <p class="ce-hint" style="margin-bottom:var(--sp-sm);">Allowed: JPEG, JPG, PNG, GIF, WEBP · Max 5MB</p>
                    <div class="ce-upload-area" id="uploadArea">
                        <div class="up-icon"><i class="bi bi-cloud-upload-fill"></i></div>
                        <div class="up-text">Click to upload or drag and drop</div>
                        <div class="up-hint">JPEG, JPG, PNG, GIF, WEBP (Max 5MB)</div>
                        <input type="file" id="posterImage" name="poster_image" style="display:none;"
                               accept=".jpg,.jpeg,.png,.gif,.webp" required>
                    </div>
                    <div class="ce-file-preview" id="filePreview">
                        <div class="ce-file-name">
                            <i class="bi bi-image-fill" style="color:var(--primary);"></i>
                            <span id="fileNameText"></span>
                            <button type="button" class="ce-remove-file" id="removeFile"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <img id="previewImage" class="ce-preview-img" alt="Preview">
                    </div>
                    @error('poster_image')<span class="ce-err">{{ $message }}</span>@enderror
                </div>

                <hr class="ce-divider">

                {{-- Basic Info --}}
                <div class="ce-section-title"><i class="bi bi-info-circle-fill"></i> Basic Info</div>

                <div style="margin-bottom:var(--sp-lg);">
                    <label class="ce-label" for="eventTitle">Title <span class="required">*</span></label>
                    <input type="text" id="eventTitle" name="title" class="ce-input"
                           placeholder="Enter class or event title" required maxlength="255"
                           value="{{ old('title') }}">
                    @error('title')<span class="ce-err">{{ $message }}</span>@enderror
                </div>

                <div style="margin-bottom:var(--sp-lg);">
                    <label class="ce-label" for="eventDescription">Description <span class="opt">(optional)</span></label>
                    <textarea id="eventDescription" name="description" class="ce-textarea"
                              rows="4" placeholder="Describe your class or event..."
                              maxlength="1000">{{ old('description') }}</textarea>
                    <span class="ce-hint" id="descCount" style="text-align:right;">0 / 1000 characters</span>
                </div>

                <hr class="ce-divider">

                {{-- Fee --}}
                <div class="ce-section-title"><i class="bi bi-cash-coin"></i> Fee</div>

                <div style="margin-bottom:var(--sp-lg);">
                    <div class="ce-radio-group">
                        <div class="ce-radio-opt">
                            <input type="radio" id="feeFree" name="is_paid" value="0"
                                   {{ old('is_paid','0') == '0' ? 'checked' : '' }}>
                            <label for="feeFree"><i class="bi bi-gift-fill" style="color:#059669;"></i> Free</label>
                        </div>
                        <div class="ce-radio-opt">
                            <input type="radio" id="feePaid" name="is_paid" value="1"
                                   {{ old('is_paid') == '1' ? 'checked' : '' }}>
                            <label for="feePaid"><i class="bi bi-tag-fill" style="color:#b45309;"></i> Paid</label>
                        </div>
                    </div>
                    <div class="ce-conditional {{ old('is_paid') == '1' ? 'active' : '' }}" id="priceField">
                        <label class="ce-label" style="margin-top:var(--sp-sm);">Price (RM) <span class="required">*</span></label>
                        <div class="ce-price-wrap">
                            <span class="ce-price-prefix">RM</span>
                            <input type="number" id="price" name="price" class="ce-price-input"
                                   placeholder="0.00" min="0.01" max="99999.99" step="0.01"
                                   value="{{ old('price') }}">
                        </div>
                        @error('price')<span class="ce-err">{{ $message }}</span>@enderror
                    </div>
                </div>

                <hr class="ce-divider">

                {{-- Media --}}
                <div class="ce-section-title"><i class="bi bi-display-fill"></i> Media</div>

                <div style="margin-bottom:var(--sp-lg);">
                    <div class="ce-radio-group">
                        <div class="ce-radio-opt">
                            <input type="radio" id="mediaOnline" name="media_type" value="online"
                                   {{ old('media_type','online') == 'online' ? 'checked' : '' }}>
                            <label for="mediaOnline"><i class="bi bi-laptop" style="color:var(--primary);"></i> Online</label>
                        </div>
                        <div class="ce-radio-opt">
                            <input type="radio" id="mediaPhysical" name="media_type" value="physical"
                                   {{ old('media_type') == 'physical' ? 'checked' : '' }}>
                            <label for="mediaPhysical"><i class="bi bi-geo-alt-fill" style="color:#ef4444;"></i> Physical</label>
                        </div>
                    </div>
                    <div class="ce-conditional {{ old('media_type','online') == 'online' ? 'active' : '' }}" id="platformField">
                        <label class="ce-label" style="margin-top:var(--sp-sm);">Platform <span class="required">*</span></label>
                        <input type="text" id="platform" name="platform" class="ce-input"
                               placeholder="e.g. Google Meet, Zoom" maxlength="255"
                               value="{{ old('platform') }}">
                        @error('platform')<span class="ce-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="ce-conditional {{ old('media_type') == 'physical' ? 'active' : '' }}" id="locationField">
                        <label class="ce-label" style="margin-top:var(--sp-sm);">Location <span class="required">*</span></label>
                        <input type="text" id="location" name="location" class="ce-input"
                               placeholder="Enter physical location address" maxlength="255"
                               value="{{ old('location') }}">
                        @error('location')<span class="ce-err">{{ $message }}</span>@enderror
                    </div>
                </div>

                <hr class="ce-divider">

                {{-- Schedule --}}
                <div class="ce-section-title"><i class="bi bi-calendar-event-fill"></i> Schedule</div>

                <div class="ce-two-col" style="margin-bottom:var(--sp-lg);">
                    <div>
                        <label class="ce-label">Start Date <span class="required">*</span></label>
                        <input type="date" id="startDate" name="start_date" class="ce-input"
                               min="{{ date('Y-m-d') }}" required value="{{ old('start_date') }}">
                        @error('start_date')<span class="ce-err">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="ce-label">End Date <span class="required">*</span></label>
                        {{-- min = start date, set dynamically --}}
                        <input type="date" id="endDate" name="end_date" class="ce-input"
                               min="{{ old('start_date', date('Y-m-d')) }}" required value="{{ old('end_date') }}">
                        @error('end_date')<span class="ce-err">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="ce-two-col" style="margin-bottom:var(--sp-lg);">
                    <div>
                        <label class="ce-label">Start Time <span class="required">*</span></label>
                        <input type="time" id="startTime" name="start_time" class="ce-input"
                               required value="{{ old('start_time') }}">
                        @error('start_time')<span class="ce-err">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="ce-label">End Time <span class="required">*</span></label>
                        <input type="time" id="endTime" name="end_time" class="ce-input"
                               required value="{{ old('end_time') }}">
                        @error('end_time')<span class="ce-err">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="ce-two-col" style="margin-bottom:var(--sp-lg);">
                    <div>
                        <label class="ce-label">Enrollment Deadline <span class="required">*</span></label>
                        {{--
                            min = today
                            max = start date (can't enroll after event starts)
                            set dynamically
                        --}}
                        <input type="date" id="enrollmentDeadline" name="enrollment_deadline" class="ce-input"
                               min="{{ date('Y-m-d') }}"
                               max="{{ old('start_date') ?: '' }}"
                               required value="{{ old('enrollment_deadline') }}">
                        <span class="ce-hint">Must be on or before the start date.</span>
                        @error('enrollment_deadline')<span class="ce-err">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="ce-label">Cancellation Deadline <span class="required">*</span></label>
                        {{--
                            min = enrollment deadline (must be AFTER enrollment)
                            max = start date (must be BEFORE start)
                            set dynamically
                        --}}
                        <input type="date" id="cancellationDeadline" name="cancellation_deadline" class="ce-input"
                               min="{{ old('enrollment_deadline') ?: date('Y-m-d') }}"
                               max="{{ old('start_date') ?: '' }}"
                               required value="{{ old('cancellation_deadline') }}">
                        <span class="ce-hint">Must be after the enrollment deadline and before the start date.</span>
                        @error('cancellation_deadline')<span class="ce-err">{{ $message }}</span>@enderror
                    </div>
                </div>

                <input type="hidden" id="durationWeeks"   name="duration_weeks">
                <input type="hidden" id="durationHours"   name="duration_hours">
                <input type="hidden" id="durationMinutes" name="duration_minutes">

                <hr class="ce-divider">

                {{-- Settings --}}
                <div class="ce-section-title"><i class="bi bi-gear-fill"></i> Settings</div>

                <div style="margin-bottom:var(--sp-lg);">
                    <label class="ce-label">Participant Limit <span class="opt">(optional)</span></label>
                    <input type="number" name="max_participants" class="ce-input"
                           placeholder="Leave blank for unlimited" min="1" max="99999"
                           value="{{ old('max_participants') }}">
                </div>

                <div style="margin-bottom:var(--sp-lg);">
                    <label class="ce-label">Require Enrollment Form?</label>
                    <div class="ce-radio-group">
                        <div class="ce-radio-opt">
                            <input type="radio" id="requireFormNo" name="require_form" value="0"
                                   {{ old('require_form','0') == '0' ? 'checked' : '' }}>
                            <label for="requireFormNo"><i class="bi bi-x-circle-fill" style="color:var(--muted);"></i> No — direct enroll</label>
                        </div>
                        <div class="ce-radio-opt">
                            <input type="radio" id="requireFormYes" name="require_form" value="1"
                                   {{ old('require_form') == '1' ? 'checked' : '' }}>
                            <label for="requireFormYes"><i class="bi bi-file-earmark-text-fill" style="color:var(--primary);"></i> Yes — Google Form</label>
                        </div>
                    </div>
                    <div class="ce-conditional {{ old('require_form') == '1' ? 'active' : '' }}" id="formUrlField">
                        <label class="ce-label" style="margin-top:var(--sp-sm);">Google Form URL <span class="required">*</span></label>
                        <input type="url" id="enrollmentFormUrl" name="enrollment_form_url" class="ce-input"
                               placeholder="https://docs.google.com/forms/..." maxlength="2048"
                               value="{{ old('enrollment_form_url') }}">
                        @error('enrollment_form_url')<span class="ce-err">{{ $message }}</span>@enderror
                        <span class="ce-hint">Buyers will be redirected here to enroll.</span>
                    </div>
                </div>

                <hr class="ce-divider">

                {{-- Social Links --}}
                <div class="ce-section-title"><i class="bi bi-share-fill"></i> Social Links <span style="font-weight:400; color:var(--muted); font-size:.85em;">(optional)</span></div>

                <div style="margin-bottom:var(--sp-lg);">
                    <label class="ce-label">Instagram</label>
                    <div class="ce-social-wrap">
                        <span class="ce-social-prefix ig"><i class="bi bi-instagram"></i></span>
                        <input type="url" name="instagram_url" class="ce-social-input"
                               placeholder="https://instagram.com/yourpage"
                               maxlength="2048" value="{{ old('instagram_url') }}">
                    </div>
                    @error('instagram_url')<span class="ce-err">{{ $message }}</span>@enderror
                </div>

                <div style="margin-bottom:var(--sp-lg);">
                    <label class="ce-label">Facebook</label>
                    <div class="ce-social-wrap">
                        <span class="ce-social-prefix fb"><i class="bi bi-facebook"></i></span>
                        <input type="url" name="facebook_url" class="ce-social-input"
                               placeholder="https://facebook.com/yourpage"
                               maxlength="2048" value="{{ old('facebook_url') }}">
                    </div>
                    @error('facebook_url')<span class="ce-err">{{ $message }}</span>@enderror
                </div>

                <div style="margin-bottom:var(--sp-lg);">
                    <label class="ce-label">X (Twitter)</label>
                    <div class="ce-social-wrap">
                        <span class="ce-social-prefix x"><i class="bi bi-twitter-x"></i></span>
                        <input type="url" name="x_url" class="ce-social-input"
                               placeholder="https://x.com/yourhandle"
                               maxlength="2048" value="{{ old('x_url') }}">
                    </div>
                    @error('x_url')<span class="ce-err">{{ $message }}</span>@enderror
                </div>

                <div class="form-actions">
                    <a href="{{ route('class.event.index') }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-upload"></i> Upload Class/Event
                    </button>
                </div>

            </form>
        </div>
    </div>

</main>
@endsection

@section('scripts')
<script>
    // ── Fee toggle ──
    document.querySelectorAll('input[name="is_paid"]').forEach(r => {
        r.addEventListener('change', function () {
            const show = this.value === '1';
            document.getElementById('priceField').classList.toggle('active', show);
            document.getElementById('price').required = show;
            if (!show) document.getElementById('price').value = '';
        });
    });

    // ── Media toggle ──
    document.querySelectorAll('input[name="media_type"]').forEach(r => {
        r.addEventListener('change', function () {
            const online = this.value === 'online';
            document.getElementById('platformField').classList.toggle('active', online);
            document.getElementById('locationField').classList.toggle('active', !online);
            document.getElementById('platform').required = online;
            document.getElementById('location').required = !online;
        });
    });

    // ── Enrollment form toggle ──
    document.querySelectorAll('input[name="require_form"]').forEach(r => {
        r.addEventListener('change', function () {
            const show = this.value === '1';
            document.getElementById('formUrlField').classList.toggle('active', show);
            document.getElementById('enrollmentFormUrl').required = show;
        });
    });

    // ── Auto-calc duration weeks ──
    function calcWeeks() {
        const s = document.getElementById('startDate').value;
        const e = document.getElementById('endDate').value;
        if (s && e) {
            const diff = (new Date(e) - new Date(s)) / (1000*60*60*24*7);
            document.getElementById('durationWeeks').value = diff >= 0 ? Math.round(diff) : '';
        }
    }

    // ── Auto-calc duration time ──
    function calcTime() {
        const s = document.getElementById('startTime').value;
        const e = document.getElementById('endTime').value;
        if (s && e) {
            const [sh, sm] = s.split(':').map(Number);
            const [eh, em] = e.split(':').map(Number);
            let mins = (eh*60+em) - (sh*60+sm);
            if (mins < 0) mins += 24*60;
            document.getElementById('durationHours').value   = Math.floor(mins/60);
            document.getElementById('durationMinutes').value = mins%60;
        }
    }

    // ── Description counter ──
    document.getElementById('eventDescription').addEventListener('input', function () {
        document.getElementById('descCount').textContent = this.value.length + ' / 1000 characters';
    });

    // ── Image preview ──
    const fileInput   = document.getElementById('posterImage');
    const uploadArea  = document.getElementById('uploadArea');
    const filePreview = document.getElementById('filePreview');

    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('dragover',  e => { e.preventDefault(); uploadArea.classList.add('dragover'); });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
    uploadArea.addEventListener('drop', e => {
        e.preventDefault(); uploadArea.classList.remove('dragover');
        if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', function () { if (this.files[0]) handleFile(this.files[0]); });
    document.getElementById('removeFile').addEventListener('click', () => {
        fileInput.value = '';
        filePreview.classList.remove('active');
        uploadArea.style.display = '';
    });

    function handleFile(file) {
        document.getElementById('fileNameText').textContent = file.name;
        const reader = new FileReader();
        reader.onload = e => { document.getElementById('previewImage').src = e.target.result; };
        reader.readAsDataURL(file);
        filePreview.classList.add('active');
        uploadArea.style.display = 'none';
    }

    // ════════════════════════════════════════════
    // DYNAMIC min/max constraints
    //
    // Timeline:  enrollment deadline < cancellation deadline < start date <= end date
    //
    // start date change:
    //   → end date min            = start date
    //   → enrollment deadline max = start date
    //   → cancellation max        = start date
    //   → clear values that fall outside new range
    //
    // enrollment deadline change:
    //   → cancellation min        = enrollment deadline  (must be AFTER)
    //   → clear cancellation if now before new min
    // ════════════════════════════════════════════

    const startDate            = document.getElementById('startDate');
    const endDate              = document.getElementById('endDate');
    const enrollmentDeadline   = document.getElementById('enrollmentDeadline');
    const cancellationDeadline = document.getElementById('cancellationDeadline');

    startDate.addEventListener('change', function () {
        const val = this.value;

        // End date: must be >= start date
        endDate.min = val;
        if (endDate.value && endDate.value < val) endDate.value = '';

        // Enrollment deadline: must be <= start date
        enrollmentDeadline.max = val;
        if (enrollmentDeadline.value && enrollmentDeadline.value > val) {
            enrollmentDeadline.value    = '';
            cancellationDeadline.min    = '';
            cancellationDeadline.value  = '';
        }

        // Cancellation deadline: must also be <= start date
        cancellationDeadline.max = val;
        if (cancellationDeadline.value && cancellationDeadline.value > val) {
            cancellationDeadline.value = '';
        }

        calcWeeks();
    });

    endDate.addEventListener('change', calcWeeks);

    enrollmentDeadline.addEventListener('change', function () {
        const val = this.value;

        // Cancellation deadline min = enrollment deadline (must be AFTER enrollment)
        cancellationDeadline.min = val;
        if (cancellationDeadline.value && cancellationDeadline.value < val) {
            cancellationDeadline.value = '';
        }
    });

    document.getElementById('startTime').addEventListener('change', calcTime);
    document.getElementById('endTime').addEventListener('change', calcTime);

    // ── Init: apply constraints on page load (handles old() repopulation) ──
    (function init() {
        const s = startDate.value;
        const e = enrollmentDeadline.value;

        if (s) {
            endDate.min              = s;
            enrollmentDeadline.max   = s;
            cancellationDeadline.max = s;
        }
        if (e) {
            cancellationDeadline.min = e;
        }

        calcWeeks();
        calcTime();
    })();
</script>
@endsection