@extends('layouts.app')

@section('title', 'Classes & Events - Craftistry')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/classEvent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/classEventOverlay.css') }}">
    <link rel="stylesheet" href="{{ asset('css/classEventParticipants.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <span class="cur">Classes & Events</span>
    </div>
</div>

<main class="main">

    {{-- Back button --}}
    <a href="javascript:history.back()" class="back-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back
    </a>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
    @endif

    {{-- Page header card --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">Classes & Events</div>
            <div class="page-subtitle">Manage and organize your creative workshops and events</div>
        </div>
        <button class="upload-btn" id="uploadBtn">
            <i class="fas fa-upload"></i>
            Upload
        </button>
    </div>

    {{-- Results card --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                My Classes & Events
            </div>
            @if($classEvents->total() > 0)
                <span class="section-count">{{ $classEvents->total() }} event{{ $classEvents->total() !== 1 ? 's' : '' }}</span>
            @endif
        </div>

        <div class="sp-card-body">

            <div class="class-grid" id="classGrid">
                @forelse($classEvents as $event)
                <div class="class-card" data-id="{{ $event->id }}">

                    {{-- Image with overlay --}}
                    <div class="class-image-wrapper">
                        <div class="class-image" style="background-image: url('{{ $event->poster_url }}');">
                            @if(!$event->poster_image)
                                <i class="fas fa-graduation-cap"></i>
                            @endif
                        </div>

                        <div class="action-buttons-overlay">
                            <button class="btn-action btn-participants"
                                    onclick="viewParticipants({{ $event->id }}, '{{ addslashes($event->title) }}')"
                                    title="View Participants">
                                <i class="fas fa-users"></i>
                            </button>
                            <button class="btn-action btn-edit"
                                    onclick="editClass({{ $event->id }})"
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-delete"
                                    onclick="confirmDelete({{ $event->id }}, '{{ addslashes($event->title) }}')"
                                    title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Card content --}}
                    <div class="class-content">
                        <h3 class="class-title">{{ $event->title }}</h3>

                        <div class="card-badges-row">
                            @if($event->is_paid)
                                <div class="fee-badge fee-badge--paid">
                                    <i class="fas fa-tag"></i>
                                    <span>RM {{ number_format($event->price, 2) }}</span>
                                </div>
                            @else
                                <div class="fee-badge fee-badge--free">
                                    <i class="fas fa-gift"></i>
                                    <span>Free</span>
                                </div>
                            @endif
                            <div class="participant-count-badge">
                                <i class="fas fa-users"></i>
                                <span>{{ $event->bookings_count }}/{{ $event->max_participants ?? '∞' }}</span>
                            </div>
                        </div>

                        <div class="class-meta">
                            <div class="class-meta-item">
                                <i class="fas {{ $event->media_type === 'online' ? 'fa-laptop' : 'fa-map-marker-alt' }}"></i>
                                <span class="meta-label">{{ ucfirst($event->media_type) }}</span>
                                <span class="meta-value">{{ $event->media_location }}</span>
                            </div>

                            <div class="class-meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="meta-label">Date</span>
                                <span class="meta-value">{{ $event->formatted_date_range }}</span>
                            </div>

                            <div class="class-meta-item">
                                <i class="fas fa-clock"></i>
                                <span class="meta-label">Time</span>
                                <span class="meta-value">
                                    {{ $event->formatted_time_range }}
                                    @if($event->duration_text)
                                        <span class="meta-duration">({{ $event->duration_text }})</span>
                                    @endif
                                </span>
                            </div>

                            @if($event->enrollment_deadline)
                            <div class="class-meta-item">
                                <i class="fas fa-hourglass-end"></i>
                                <span class="meta-label">Enroll by</span>
                                <span class="meta-value">{{ \Carbon\Carbon::parse($event->enrollment_deadline)->format('d M Y') }}</span>
                            </div>
                            @endif

                            @if($event->description)
                            <div class="class-meta-item class-meta-desc">
                                <i class="fas fa-align-left"></i>
                                <span class="meta-value meta-desc-text">{{ $event->description }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-graduation-cap"></i></div>
                    <h3 class="empty-title">No Classes or Events Yet</h3>
                    <p class="empty-description">Upload your classes and events to showcase your creative workshops and art events.</p>
                    <button class="btn-empty" onclick="document.getElementById('uploadBtn').click()">
                        <i class="fas fa-plus"></i>
                        Upload Your First Class/Event
                    </button>
                </div>
                @endforelse
            </div>

            @if($classEvents->hasPages())
            <div class="pagination-wrapper">
                {{ $classEvents->links() }}
            </div>
            @endif

        </div>
    </div>

</main>


{{-- ══ UPLOAD MODAL ══ --}}
<div class="modal" id="uploadModal">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-cloud-upload-alt"></i>
                Upload Class/Event
            </h2>
            <button class="modal-close" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="uploadForm" action="{{ route('class.event.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label">Image <span class="required">*</span></label>
                    <p class="allowed-formats">Allowed: JPEG, JPG, PNG, GIF, WEBP (Max 5MB)</p>
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="upload-text">Click to upload or drag and drop</div>
                        <div class="upload-hint">JPEG, JPG, PNG, GIF, WEBP (Max 5MB)</div>
                        <input type="file" id="posterImage" name="poster_image" class="file-input"
                               accept=".jpg,.jpeg,.png,.gif,.webp" required>
                    </div>
                    <div class="file-preview" id="filePreview">
                        <div class="file-name">
                            <i class="fas fa-image"></i>
                            <span id="fileNameText"></span>
                            <button type="button" class="remove-file" id="removeFile">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <img id="previewImage" class="preview-image" alt="Preview">
                    </div>
                    <span class="error-message" id="posterImageError"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="eventTitle">Title <span class="required">*</span></label>
                    <input type="text" id="eventTitle" name="title" class="form-input"
                           placeholder="Enter class or event title" required maxlength="255">
                    <span class="error-message" id="titleError"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Fee <span class="required">*</span></label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="feeFree" name="is_paid" value="0" checked>
                            <label for="feeFree"><i class="fas fa-gift"></i> Free</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="feePaid" name="is_paid" value="1">
                            <label for="feePaid"><i class="fas fa-tag"></i> Paid</label>
                        </div>
                    </div>
                    <div class="conditional-field" id="priceField" style="margin-top:10px;">
                        <label class="form-label" for="price">Price (RM) <span class="required">*</span></label>
                        <div class="price-input-wrapper">
                            <span class="price-prefix">RM</span>
                            <input type="number" id="price" name="price" class="form-input price-input"
                                   placeholder="0.00" min="0.01" max="99999.99" step="0.01">
                        </div>
                        <span class="error-message" id="priceError"></span>
                        <span class="char-count">Enter the price in Malaysian Ringgit (RM).</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Media <span class="required">*</span></label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="mediaOnline" name="media_type" value="online" checked>
                            <label for="mediaOnline"><i class="fas fa-laptop"></i> Online</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="mediaPhysical" name="media_type" value="physical">
                            <label for="mediaPhysical"><i class="fas fa-map-marker-alt"></i> Physical</label>
                        </div>
                    </div>
                    <div class="conditional-field active" id="platformField">
                        <label class="form-label" for="platform">Platform <span class="required">*</span></label>
                        <input type="text" id="platform" name="platform" class="form-input"
                               placeholder="e.g. Google Meet, Zoom" maxlength="255">
                        <span class="error-message" id="platformError"></span>
                    </div>
                    <div class="conditional-field" id="locationField">
                        <label class="form-label" for="location">Location <span class="required">*</span></label>
                        <input type="text" id="location" name="location" class="form-input"
                               placeholder="Enter physical location address" maxlength="255">
                        <span class="error-message" id="locationError"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Start Date <span class="required">*</span></label>
                    <input type="date" id="startDate" name="start_date" class="form-input" min="{{ date('Y-m-d') }}" required>
                    <span class="error-message" id="startDateError"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">End Date <span class="required">*</span></label>
                    <input type="date" id="endDate" name="end_date" class="form-input" min="{{ date('Y-m-d') }}" required>
                    <span class="error-message" id="endDateError"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Enrollment Deadline <span style="font-weight:400;font-size:0.85em;">(optional)</span></label>
                    <input type="date" id="enrollmentDeadline" name="enrollment_deadline" class="form-input" min="{{ date('Y-m-d') }}">
                    <span class="char-count">Last day users can enroll. Leave blank for no deadline.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Cancellation Deadline <span style="font-weight:400;font-size:0.85em;">(optional)</span></label>
                    <input type="date" id="cancellationDeadline" name="cancellation_deadline" class="form-input" min="{{ date('Y-m-d') }}">
                    <span class="char-count">Last day users can cancel enrollment. Leave blank to allow anytime.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Require Enrollment Form?</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="requireFormNo" name="require_form" value="0" checked>
                            <label for="requireFormNo"><i class="fas fa-times-circle"></i> No — direct enroll</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="requireFormYes" name="require_form" value="1">
                            <label for="requireFormYes"><i class="fas fa-wpforms"></i> Yes — Google Form</label>
                        </div>
                    </div>
                    <div class="conditional-field" id="formUrlField" style="margin-top:10px;">
                        <label class="form-label">Google Form URL <span class="required">*</span></label>
                        <input type="url" id="enrollmentFormUrl" name="enrollment_form_url" class="form-input"
                               placeholder="https://docs.google.com/forms/..." maxlength="2048">
                        <span class="error-message" id="enrollmentFormUrlError"></span>
                        <span class="char-count">Paste your Google Form link. Buyers will be sent here to enroll.</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Participant Limit <span style="font-weight:400;font-size:0.85em;">(optional)</span></label>
                    <input type="number" id="maxParticipants" name="max_participants" class="form-input"
                           placeholder="e.g. 20" min="1" max="99999">
                    <span class="char-count">Leave blank for unlimited.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Duration (Weeks)</label>
                    <input type="number" id="durationWeeks" name="duration_weeks" class="form-input"
                           placeholder="Auto-calculated" min="0" readonly>
                    <span class="char-count">Automatically calculated from start and end date.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Start Time <span class="required">*</span></label>
                    <input type="time" id="startTime" name="start_time" class="form-input" required>
                    <span class="error-message" id="startTimeError"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">End Time <span class="required">*</span></label>
                    <input type="time" id="endTime" name="end_time" class="form-input" required>
                    <span class="error-message" id="endTimeError"></span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Duration (Hours)</label>
                        <input type="number" id="durationHours" name="duration_hours" class="form-input"
                               placeholder="Auto-calculated" min="0" max="23" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (Minutes)</label>
                        <input type="number" id="durationMinutes" name="duration_minutes" class="form-input"
                               placeholder="Auto-calculated" min="0" max="59" readonly>
                    </div>
                </div>
                <span class="char-count">Automatically calculated from start and end time.</span>

                <div class="form-group">
                    <label class="form-label" for="eventDescription">Description (Optional)</label>
                    <textarea id="eventDescription" name="description" class="form-textarea"
                              rows="4" placeholder="Describe your class or event..." maxlength="1000"></textarea>
                    <span class="char-count" id="descCount">0 / 1000 characters</span>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-upload"></i> Upload Class/Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ══ EDIT MODAL ══ --}}
<div class="modal" id="editModal">
    <div class="modal-overlay" onclick="closeEditModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-edit"></i> Edit Class/Event
            </h2>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="editClassId" name="class_id">

                <div class="form-group">
                    <label class="form-label">Current Image</label>
                    <div class="current-image-preview">
                        <img id="currentImage" src="" alt="Current">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Update Image (Optional)</label>
                    <p class="allowed-formats">Leave blank to keep current image</p>
                    <div class="upload-area" id="editUploadArea">
                        <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <div class="upload-text">Click to upload new image</div>
                        <input type="file" id="editPosterImage" name="poster_image" class="file-input"
                               accept=".jpg,.jpeg,.png,.gif,.webp">
                    </div>
                    <div class="file-preview" id="editFilePreview" style="display:none;">
                        <div class="file-name">
                            <i class="fas fa-image"></i>
                            <span id="editFileNameText"></span>
                            <button type="button" class="remove-file" id="editRemoveFile">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <img id="editPreviewImage" class="preview-image" alt="Preview">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" id="editEventTitle" name="title" class="form-input" required maxlength="255">
                </div>

                <div class="form-group">
                    <label class="form-label">Fee <span class="required">*</span></label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="editFeeFree" name="is_paid" value="0">
                            <label for="editFeeFree"><i class="fas fa-gift"></i> Free</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="editFeePaid" name="is_paid" value="1">
                            <label for="editFeePaid"><i class="fas fa-tag"></i> Paid</label>
                        </div>
                    </div>
                    <div class="conditional-field" id="editPriceField" style="margin-top:10px;">
                        <label class="form-label" for="editPrice">Price (RM) <span class="required">*</span></label>
                        <div class="price-input-wrapper">
                            <span class="price-prefix">RM</span>
                            <input type="number" id="editPrice" name="price" class="form-input price-input"
                                   placeholder="0.00" min="0.01" max="99999.99" step="0.01">
                        </div>
                        <span class="error-message" id="editPriceError"></span>
                        <span class="char-count">Enter the price in Malaysian Ringgit (RM).</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Media <span class="required">*</span></label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="editMediaOnline" name="media_type" value="online">
                            <label for="editMediaOnline"><i class="fas fa-laptop"></i> Online</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="editMediaPhysical" name="media_type" value="physical">
                            <label for="editMediaPhysical"><i class="fas fa-map-marker-alt"></i> Physical</label>
                        </div>
                    </div>
                    <div class="conditional-field" id="editPlatformField">
                        <label class="form-label">Platform <span class="required">*</span></label>
                        <input type="text" id="editPlatform" name="platform" class="form-input" maxlength="255">
                    </div>
                    <div class="conditional-field" id="editLocationField">
                        <label class="form-label">Location <span class="required">*</span></label>
                        <input type="text" id="editLocation" name="location" class="form-input" maxlength="255">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Start Date <span class="required">*</span></label>
                    <input type="date" id="editStartDate" name="start_date" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">End Date <span class="required">*</span></label>
                    <input type="date" id="editEndDate" name="end_date" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Enrollment Deadline <span style="font-weight:400;font-size:0.85em;">(optional)</span></label>
                    <input type="date" id="editEnrollmentDeadline" name="enrollment_deadline" class="form-input">
                    <span class="char-count">Leave blank for no deadline.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Cancellation Deadline <span style="font-weight:400;font-size:0.85em;">(optional)</span></label>
                    <input type="date" id="editCancellationDeadline" name="cancellation_deadline" class="form-input">
                    <span class="char-count">Leave blank to allow cancellation anytime.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Require Enrollment Form?</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="editRequireFormNo" name="require_form" value="0">
                            <label for="editRequireFormNo"><i class="fas fa-times-circle"></i> No — direct enroll</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="editRequireFormYes" name="require_form" value="1">
                            <label for="editRequireFormYes"><i class="fas fa-wpforms"></i> Yes — Google Form</label>
                        </div>
                    </div>
                    <div class="conditional-field" id="editFormUrlField" style="margin-top:10px;">
                        <label class="form-label">Google Form URL</label>
                        <input type="url" id="editEnrollmentFormUrl" name="enrollment_form_url" class="form-input"
                               placeholder="https://docs.google.com/forms/..." maxlength="2048">
                        <span class="char-count">Paste your Google Form link.</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Participant Limit <span style="font-weight:400;font-size:0.85em;">(optional)</span></label>
                    <input type="number" id="editMaxParticipants" name="max_participants" class="form-input"
                           placeholder="e.g. 20" min="1" max="99999">
                    <span class="char-count">Leave blank for unlimited.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Start Time <span class="required">*</span></label>
                    <input type="time" id="editStartTime" name="start_time" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">End Time <span class="required">*</span></label>
                    <input type="time" id="editEndTime" name="end_time" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea id="editEventDescription" name="description" class="form-textarea"
                              rows="4" maxlength="1000"></textarea>
                    <span class="char-count" id="editDescCount">0 / 1000 characters</span>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Class/Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ══ DELETE MODAL ══ --}}
<div class="modal modal-sm" id="deleteModal">
    <div class="modal-overlay" onclick="closeDeleteModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fas fa-exclamation-triangle"></i> Confirm Delete
            </h2>
            <button class="modal-close" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" style="text-align:center;">
            <p>Are you sure you want to delete this class/event?</p>
            <p class="text-muted"><strong id="deleteClassName"></strong></p>
            <p class="text-danger"><small>This action cannot be undone.</small></p>
            <div class="form-actions">
                <button type="button" class="btn-cancel btn-delete-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn-delete-confirm" onclick="deleteClass()">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ══ PARTICIPANTS MODAL ══ --}}
<div class="modal" id="participantsModal">
    <div class="modal-overlay" onclick="closeParticipantsModal()"></div>
    <div class="modal-content participants-modal-content">
        <div class="modal-header">
            <div class="participants-modal-title-group">
                <h2 class="modal-title">
                    <i class="fas fa-users"></i> Participants
                </h2>
                <p class="participants-modal-subtitle" id="participantsEventTitle"></p>
            </div>
            <button class="modal-close" onclick="closeParticipantsModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="participants-summary">
                <div class="participants-summary-stat">
                    <span class="participants-summary-number" id="participantsTotalCount">0</span>
                    <span class="participants-summary-label">Total Participants</span>
                </div>
            </div>
            <div class="participants-loading" id="participantsLoading">
                <div class="participants-spinner"></div>
                <p>Loading participants...</p>
            </div>
            <div class="participants-empty" id="participantsEmpty" style="display:none;">
                <div class="participants-empty-icon"><i class="fas fa-user-slash"></i></div>
                <h3>No Participants Yet</h3>
                <p id="participantsEmptyMsg">No one has booked this class/event yet.</p>
            </div>
            <div class="participants-table-wrapper" id="participantsTableWrapper" style="display:none;">
                <table class="participants-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Booking Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="participantsTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- ══ POPUPS ══ --}}
<div class="success-popup" id="successPopup">
    <div class="success-content">
        <div class="success-icon"><i class="fas fa-check"></i></div>
        <p>Success message</p>
    </div>
</div>

<div class="error-popup" id="errorNotification">
    <div class="error-content">
        <div class="error-icon"><i class="fas fa-exclamation-circle"></i></div>
        <p>Error message</p>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        const storeRoute              = "{{ route('class.event.store') }}";
        const indexRoute              = "{{ route('class.event.index') }}";
        const csrfToken               = "{{ csrf_token() }}";
        const getDataRoute            = "{{ url('class-event') }}";
        const participantsBaseRoute   = "{{ url('class-event') }}";
    </script>
    <script src="{{ asset('js/classEvent.js') }}"></script>
    <script src="{{ asset('js/classEventEditDelete.js') }}"></script>
    <script src="{{ asset('js/classEventParticipants.js') }}"></script>

    <script>
        // Fee toggle — upload modal
        document.querySelectorAll('input[name="is_paid"]').forEach(function(radio) {
            if (radio.id === 'feeFree' || radio.id === 'feePaid') {
                radio.addEventListener('change', function() {
                    togglePriceField('priceField', 'price', this.value === '1');
                });
            }
        });

        // Fee toggle — edit modal
        document.getElementById('editFeeFree')?.addEventListener('change', function() {
            togglePriceField('editPriceField', 'editPrice', false);
        });
        document.getElementById('editFeePaid')?.addEventListener('change', function() {
            togglePriceField('editPriceField', 'editPrice', true);
        });

        function togglePriceField(fieldId, inputId, show) {
            const field = document.getElementById(fieldId);
            const input = document.getElementById(inputId);
            if (!field || !input) return;
            if (show) {
                field.classList.add('active');
                input.required = true;
            } else {
                field.classList.remove('active');
                input.required = false;
                input.value = '';
                const err = document.getElementById(inputId === 'price' ? 'priceError' : 'editPriceError');
                if (err) err.textContent = '';
            }
        }

        document.addEventListener('classEventDataLoaded', function(e) {
            fillFeeFields(e.detail, false);
        });

        function fillFeeFields(data, isUpload) {
            if (isUpload) return;
            const isPaid = parseInt(data.is_paid) === 1;
            document.getElementById('editFeeFree').checked = !isPaid;
            document.getElementById('editFeePaid').checked = isPaid;
            togglePriceField('editPriceField', 'editPrice', isPaid);
            if (isPaid && data.price) {
                document.getElementById('editPrice').value = parseFloat(data.price).toFixed(2);
            }
        }
    </script>
@endsection