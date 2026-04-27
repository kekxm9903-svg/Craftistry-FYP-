@extends('layouts.app')

@section('title', 'Request Custom Order')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/userRequestForm.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.browse.show', $seller->id) }}">
            {{ $seller->fullname ?? $seller->name }}
        </a>
        <span class="sep">/</span>
        <span class="cur">Custom Order Request</span>
    </div>
</div>

<div style="max-width:700px;margin:0 auto;padding:var(--sp-sm) var(--sp-lg) 0;">
    <a href="{{ route('artist.browse.show', $seller->id) }}" class="back-btn">← Back</a>
</div>

<div class="co-page-narrow">

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="page-header-card">
        <div class="page-header-left">
            <div class="page-title">Request Custom Order</div>
            <div class="page-subtitle">
                Sending to&nbsp;<strong>{{ $seller->fullname ?? $seller->name }}</strong>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="sp-card">
        <div class="sp-card-header">
            <div class="sp-card-header-left">
                <div class="hline"></div>
                Order Details
            </div>
        </div>
        <div class="sp-card-body">
            <form action="{{ route('custom-orders.store', $seller->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="form-grid">
                @csrf

                {{-- Title --}}
                <div class="form-group">
                    <label class="form-label">
                        Request Title <span class="req">*</span>
                    </label>
                    <input type="text"
                           name="title"
                           class="form-input"
                           placeholder="e.g. Portrait painting of my family"
                           value="{{ old('title') }}"
                           maxlength="120">
                    @error('title')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="form-group">
                    <label class="form-label">
                        Description <span class="req">*</span>
                    </label>
                    <textarea name="description"
                              class="form-textarea"
                              placeholder="Describe what you want — size, style, materials, colours, any specific details..."
                              maxlength="2000">{{ old('description') }}</textarea>
                    <span class="form-hint">Be as detailed as possible so the seller understands your vision.</span>
                    @error('description')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Product type --}}
                <div class="form-group">
                    <label class="form-label">
                        Product Type <span class="req">*</span>
                    </label>
                    <div class="radio-group">
                        <label class="radio-option {{ old('product_type', 'physical') === 'physical' ? 'active' : '' }}">
                            <input type="radio" name="product_type" value="physical"
                                   {{ old('product_type', 'physical') === 'physical' ? 'checked' : '' }}>
                            <span class="radio-icon"><i class="fas fa-box"></i></span>
                            <span class="radio-label-text">
                                <strong>Physical</strong>
                                <small>Artwork will be shipped via courier</small>
                            </span>
                        </label>
                        <label class="radio-option {{ old('product_type') === 'digital' ? 'active' : '' }}">
                            <input type="radio" name="product_type" value="digital"
                                   {{ old('product_type') === 'digital' ? 'checked' : '' }}>
                            <span class="radio-icon"><i class="fas fa-file-image"></i></span>
                            <span class="radio-label-text">
                                <strong>Digital</strong>
                                <small>Artwork delivered digitally, no shipping needed</small>
                            </span>
                        </label>
                    </div>
                    @error('product_type')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Expected price --}}
                <div class="form-group">
                    <label class="form-label">
                        Your Expected Price <span class="req">*</span>
                    </label>
                    <div class="price-wrap">
                        <span class="price-prefix">RM</span>
                        <input type="number"
                               name="buyer_price"
                               class="form-input"
                               placeholder="0.00"
                               min="1"
                               step="0.01"
                               value="{{ old('buyer_price') }}">
                    </div>
                    <span class="form-hint">The seller may accept, refuse, or suggest a different price.</span>
                    @error('buyer_price')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Reference image --}}
                <div class="form-group">
                    <label class="form-label">
                        Reference Image
                        <span style="font-weight:400; color:var(--muted);">(optional)</span>
                    </label>
                    <div class="upload-zone" id="upload-zone">
                        <input type="file"
                               name="reference_image"
                               id="ref-input"
                               accept="image/jpeg,image/png,image/webp">
                        <div class="upload-zone-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-zone-label">Click to upload or drag & drop</div>
                        <div class="upload-zone-sub">JPG, PNG, WEBP — max 4 MB</div>
                    </div>
                    <div class="img-preview" id="img-preview">
                        <img id="preview-img" src="" alt="Preview">
                    </div>
                    @error('reference_image')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="action-row" style="padding-top:var(--sp-xs);">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Request
                    </button>
                    <a href="{{ route('artist.browse.show', $seller->id) }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Image preview
    const input   = document.getElementById('ref-input');
    const preview = document.getElementById('img-preview');
    const img     = document.getElementById('preview-img');

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    // Radio button active state
    document.querySelectorAll('.radio-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.radio-option').forEach(opt => opt.classList.remove('active'));
            if (radio.checked) radio.closest('.radio-option').classList.add('active');
        });
    });
</script>
@endsection