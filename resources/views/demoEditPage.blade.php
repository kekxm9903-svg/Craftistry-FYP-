@extends('layouts.app')

@section('title', 'Edit Demo Artwork')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistProfile.css') }}">
<link rel="stylesheet" href="{{ asset('css/uploadForm.css') }}">
@endsection

@section('content')

<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <span class="cur">Edit Demo</span>
    </div>
</div>

<main class="upload-page-main">

    <div class="upload-page-header">
        <div class="upload-page-header-inner">
            <div class="upload-page-header-icon demo-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <h1 class="upload-page-title">Edit Demo Artwork</h1>
                <p class="upload-page-subtitle">Update your demo details and images</p>
            </div>
        </div>
        <a href="{{ route('artist.profile') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Studio
        </a>
    </div>

    <form id="demoEditForm" action="{{ route('artist.demo.update', $demo->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="upload-form-layout">

            {{-- LEFT: Image --}}
            <div class="upload-form-left">
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-image"></i>
                        <h2>Artwork Images</h2>
                        <span class="optional-badge">Change Optional</span>
                    </div>
                    <div class="form-card-body">

                        {{-- Unified Image Manager --}}
                        <p style="font-size:var(--fs-sm);color:var(--muted);margin-bottom:10px;font-weight:600;">
                            Images <span style="font-weight:400;">(click ✕ to remove, click + to add more)</span>
                        </p>

                        {{-- Hidden input to track deleted paths --}}
                        <div id="demoDeletedInputs"></div>

                        {{-- Hidden file input --}}
                        <input type="file" id="demoEditImage" name="new_images[]"
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               multiple style="display:none;">

                        <div class="img-manager-grid" id="demoImgManagerGrid">
                            @if($demo->image_path)
                                <div class="img-manager-item" data-path="{{ $demo->image_path }}" data-type="existing">
                                    <img src="{{ $demo->image_url }}" alt="Main">
                                    <span class="img-manager-badge main-badge"><i class="fas fa-star"></i> Main</span>
                                    <button type="button" class="img-manager-remove"
                                            onclick="removeExistingImage(this, 'demo')" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                            @if($demo->extra_images)
                                @foreach($demo->extra_images as $extraPath)
                                <div class="img-manager-item" data-path="{{ $extraPath }}" data-type="existing">
                                    <img src="{{ asset('storage/' . $extraPath) }}" alt="Extra">
                                    <button type="button" class="img-manager-remove"
                                            onclick="removeExistingImage(this, 'demo')" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                @endforeach
                            @endif
                            {{-- Add more tile --}}
                            <div class="img-manager-add" onclick="document.getElementById('demoEditImage').click()">
                                <i class="fas fa-plus"></i>
                                <span>Add</span>
                            </div>
                        </div>

                        <div class="image-tips" style="margin-top:10px;">
                            <div class="tip-item"><i class="bi bi-lightbulb"></i> First image is the main cover. Max 5MB each.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Fields --}}
            <div class="upload-form-right">

                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-pencil-alt"></i>
                        <h2>Demo Details</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="field-group">
                            <label for="editDemoTitle" class="field-label">Title <span class="required">*</span></label>
                            <input type="text" id="editDemoTitle" name="title"
                                   value="{{ old('title', $demo->title) }}"
                                   required maxlength="255" class="field-input">
                            <span class="field-counter" id="editTitleCounter">0 / 255</span>
                        </div>
                        <div class="field-group">
                            <label for="editDemoDesc" class="field-label">Description <span class="field-optional">(Optional)</span></label>
                            <textarea id="editDemoDesc" name="description" rows="5" maxlength="1000"
                                      class="field-textarea">{{ old('description', $demo->description) }}</textarea>
                            <span class="field-counter" id="editDescCounter">0 / 1000</span>
                        </div>
                    </div>
                </div>

                {{-- Show sell fields only if cross-posted --}}
                @if($demo->is_cross_posted)
                <div class="form-card" style="border-left: 3px solid #22c55e;">
                    <div class="form-card-header">
                        <i class="fas fa-shopping-bag"></i>
                        <h2>Linked Sale Listing</h2>
                        <span class="sale-chip">Synced</span>
                    </div>
                    <div class="form-card-body">
                        <div class="field-hint" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:10px 12px;margin-bottom:16px;font-size:var(--fs-sm);color:#166534;">
                            <i class="fas fa-link"></i> Title, description and images sync automatically to the linked sell listing.
                            Sale-specific fields (price, shipping, dimensions) must be edited separately in Artwork Sell.
                        </div>
                    </div>
                </div>
                @endif

                <div class="form-submit-bar">
                    <a href="{{ route('artist.profile') }}" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-submit" id="demoEditSubmitBtn">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>

            </div>
        </div>
    </form>
</main>

@endsection

@section('scripts')
<script src="{{ asset('js/uploadForm.js') }}"></script>
<script>
// New files to add
const demoEditNewFiles = [];

document.addEventListener('DOMContentLoaded', function () {
    setupCounter('editDemoTitle', 'editTitleCounter', 255);
    setupCounter('editDemoDesc',  'editDescCounter',  1000);

    // File input change — add new images to grid
    const fileInput = document.getElementById('demoEditImage');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            addNewImagesToGrid(Array.from(this.files), 'demoImgManagerGrid', demoEditNewFiles, 'demoEditImage', 'demo');
            this.value = '';
        });
    }

    // Submit
    const form = document.getElementById('demoEditForm');
    const btn  = document.getElementById('demoEditSubmitBtn');
    if (form && btn) {
        form.addEventListener('submit', function () {
            syncEditFiles(demoEditNewFiles, 'demoEditImage');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Saving...';
        });
    }
});
</script>
@endsection