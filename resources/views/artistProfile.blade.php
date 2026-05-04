@extends('layouts.app')

@section('title', 'Artist Profile')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistProfile.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <span class="cur">Studio</span>
    </div>
</div>

<main class="main">
    <div class="artist-header">
        <div class="artist-header-content">
            <div class="artist-avatar">
                @if($artist->user->profile_image)
                    <img src="{{ asset('storage/' . $artist->user->profile_image) }}?v={{ time() }}"
                         alt="{{ $artist->user->fullname }}">
                @else
                    <div class="avatar-placeholder">
                        {{ strtoupper(substr($artist->user->fullname, 0, 1)) }}
                    </div>
                @endif

                <div class="verification-badge" title="Verified Artist">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>

            <div class="artist-info">
                <h1 class="artist-name">{{ $artist->user->fullname }}</h1>

                <div class="artist-meta">
                    <div class="meta-item">
                        <i class="fas fa-star"></i>
                        <span>5.0 Rating</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-box"></i>
                        <span>{{ $artist->demoArtworks->count() }} Demo</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>{{ $artist->artworkSells->count() }} Artwork Sell</span>
                    </div>
                </div>

                @if($artist->specialization)
                <p class="artist-specialization">
                    <i class="fas fa-palette"></i>
                    {{ $artist->specialization }}
                </p>
                @endif

                <div class="artist-types">
                    @foreach($artist->artworkTypes as $type)
                    <span class="type-badge">{{ $type->name }}</span>
                    @endforeach
                </div>
            </div>

            <div class="artist-actions">
                <button class="btn-edit" onclick="window.location.href='{{ route('artist.profile.edit') }}'">
                    <i class="fas fa-edit"></i>
                    Edit Profile
                </button>
            </div>
        </div>
    </div>

    <div class="profile-section">
        <h2 class="section-title">
            <i class="fas fa-info-circle"></i>
            About Me
        </h2>
        <div class="bio-content">
            <p>{{ $artist->bio }}</p>
        </div>
    </div>

    <div class="profile-section">
        <h2 class="section-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h2>

        <div class="quick-actions-grid">
            <div class="action-card purple">
                <div class="card-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>Order Summary</h3>
                <p>View all your order data</p>
                <a href="{{ route('artist.order.summary') }}" class="card-link">View Orders <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="action-card blue">
                <div class="card-icon">
                    <i class="fas fa-list-alt"></i>
                </div>
                <h3>Order List</h3>
                <p>Manage pending and completed orders</p>
                <a href="{{ route('artist.orders') }}" class="card-link">Manage Orders <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="action-card orange">
                <div class="card-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <h3>Request List</h3>
                <p>View custom order requests from buyers</p>
                <a href="{{ route('artist.custom-orders.index') }}" class="card-link">View Requests <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="action-card green">
                <div class="card-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>Upload Class or Event</h3>
                <p>Create and share new tutorials and events</p>
                <a href="{{ route('class.event.index') }}" class="card-link">Create Class <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    {{-- DEMO SECTION --}}
    <div class="profile-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-images"></i>
                DEMO
            </h2>
            <a href="{{ route('artist.demo.upload.page') }}" class="btn-upload">
                <i class="fas fa-upload"></i>
                Upload
            </a>
        </div>

        @if($artist->demoArtworks->count() > 0)
            <div class="artworks-grid" id="demoArtworksGrid">
                @foreach($artist->demoArtworks as $demo)
                <div class="artwork-card" data-demo-id="{{ $demo->id }}">
                    <div class="artwork-image">
                        @php
                            $demoImages = array_filter(array_merge(
                                [$demo->image_url],
                                $demo->extra_images ? array_map(fn($p) => asset('storage/' . $p), $demo->extra_images) : []
                            ));
                            $demoImages = array_values($demoImages);
                        @endphp
                        <div class="card-slider" data-index="0">
                            @foreach($demoImages as $i => $imgUrl)
                                <img src="{{ $imgUrl }}" alt="{{ $demo->title }}" class="slider-img {{ $i === 0 ? 'active' : '' }}">
                            @endforeach
                            @if(count($demoImages) > 1)
                                <button class="slider-arrow slider-prev" onclick="slideCard(this, -1)"><i class="bi bi-chevron-left"></i></button>
                                <button class="slider-arrow slider-next" onclick="slideCard(this, 1)"><i class="bi bi-chevron-right"></i></button>
                                <div class="slider-dots">
                                    @foreach($demoImages as $i => $imgUrl)
                                        <span class="slider-dot {{ $i === 0 ? 'active' : '' }}"></span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="artwork-overlay">
                            <button class="btn-icon" title="Edit" onclick="openEditDemoModal({{ $demo->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Delete" onclick="deleteDemo({{ $demo->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="artwork-info">
                        <h4>{{ $demo->title }}</h4>
                        @if($demo->description)
                        <p class="artwork-description">{{ Str::limit($demo->description, 100) }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" id="demoEmptyState">
                <div class="empty-icon">
                    <i class="fas fa-image"></i>
                </div>
                <h3>No Demo Artworks Yet</h3>
                <p>Upload your demo artworks to showcase your creative process and work-in-progress pieces</p>
                <a href="{{ route('artist.demo.upload.page') }}" class="btn-primary-outline">
                    <i class="fas fa-plus"></i>
                    Upload Your First Demo
                </a>
            </div>
        @endif
    </div>

    {{-- ARTWORK SELL SECTION --}}
    <div class="profile-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fas fa-shopping-cart"></i>
                Artwork Sell
            </h2>
            <a href="{{ route('artist.artwork.sell.page') }}" class="btn-upload">
                <i class="fas fa-upload"></i>
                Upload
            </a>
        </div>

        @if($artist->artworkSells->count() > 0)
            <div class="artworks-grid" id="sellArtworksGrid">
                @foreach($artist->artworkSells as $artwork)
                <div class="artwork-card" data-artwork-id="{{ $artwork->id }}">
                    <div class="artwork-image">
                        @php
                            $sellImages = array_filter(array_merge(
                                [$artwork->image_url],
                                $artwork->extra_images ? array_map(fn($p) => asset('storage/' . $p), $artwork->extra_images) : []
                            ));
                            $sellImages = array_values($sellImages);
                        @endphp
                        <div class="card-slider" data-index="0">
                            @foreach($sellImages as $i => $imgUrl)
                                <img src="{{ $imgUrl }}" alt="{{ $artwork->product_name }}" class="slider-img {{ $i === 0 ? 'active' : '' }}">
                            @endforeach
                            @if(count($sellImages) > 1)
                                <button class="slider-arrow slider-prev" onclick="slideCard(this, -1)"><i class="bi bi-chevron-left"></i></button>
                                <button class="slider-arrow slider-next" onclick="slideCard(this, 1)"><i class="bi bi-chevron-right"></i></button>
                                <div class="slider-dots">
                                    @foreach($sellImages as $i => $imgUrl)
                                        <span class="slider-dot {{ $i === 0 ? 'active' : '' }}"></span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="artwork-overlay">
                            <button class="btn-icon" title="Edit" onclick="openEditArtworkModal({{ $artwork->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="Delete" onclick="deleteArtwork({{ $artwork->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="artwork-info">
                        <div class="artwork-price">{{ $artwork->formatted_price }}</div>
                        <h4>{{ $artwork->product_name }}</h4>
                        <span style="font-size: 0.75rem; color: #667eea; font-weight: bold; text-transform: uppercase;">
                            {{ $artwork->artwork_type }}
                        </span>
                        @if($artwork->shipping_fee > 0)
                        <div style="font-size: 0.78rem; color: #718096; margin-top: 2px;">
                            <i class="fas fa-truck" style="color:#667eea;"></i>
                            Shipping: RM {{ number_format($artwork->shipping_fee, 2) }}
                        </div>
                        @else
                        <div style="font-size: 0.78rem; color: #48bb78; margin-top: 2px;">
                            <i class="fas fa-truck" style="color:#48bb78;"></i>
                            Free Shipping
                        </div>
                        @endif
                        @if($artwork->bulk_sell_enabled)
                        <div style="font-size: 0.78rem; color: #764ba2; margin-top: 2px;">
                            <i class="fas fa-tags" style="color:#764ba2;"></i>
                            Bulk: {{ $artwork->bulk_sell_discount }}% off ≥{{ $artwork->bulk_sell_min_qty }} pcs
                        </div>
                        @endif
                        <div>
                            <span class="status-badge status-{{ $artwork->status }}">
                                @if($artwork->status === 'available')
                                    <i class="fas fa-check-circle"></i> Available
                                @else
                                    <i class="fas fa-times-circle"></i> Sold Out
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" id="sellEmptyState">
                <div class="empty-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3>No Artworks for Sale Yet</h3>
                <p>Start selling your artwork! Upload your completed pieces and set your prices</p>
                <a href="{{ route('artist.artwork.sell.page') }}" class="btn-primary-outline">
                    <i class="fas fa-plus"></i>
                    List Your First Artwork
                </a>
            </div>
        @endif
    </div>
</main>

{{-- EDIT DEMO MODAL --}}
<div class="modal" id="editDemoModal">
    <div class="modal-overlay" onclick="closeEditDemoModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-edit"></i> Edit Demo Artwork</h2>
            <button class="modal-close" onclick="closeEditDemoModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="editDemoForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="editDemoId" name="demo_id">

            <div class="modal-body">
                <div class="form-group">
                    <label>Current Image</label>
                    <div class="current-image-preview">
                        <img src="" alt="Current" id="editDemoCurrentImage" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="editDemoImage">Change Image (Optional)</label>
                    <div class="image-upload-area" id="editImageUploadArea">
                        <input type="file" id="editDemoImage" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload new image</p>
                        </div>
                        <div class="image-preview" id="editImagePreview" style="display: none;">
                            <img src="" alt="Preview" id="editPreviewImage">
                            <button type="button" class="remove-image" onclick="removeEditImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editDemoTitle">Title <span class="required">*</span></label>
                    <input type="text" id="editDemoTitle" name="title" required maxlength="255">
                </div>

                <div class="form-group">
                    <label for="editDemoDescription">Description (Optional)</label>
                    <textarea id="editDemoDescription" name="description" rows="4" maxlength="1000"></textarea>
                    <span class="char-count" id="editDescCount">0 / 1000 characters</span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditDemoModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="updateDemoBtn"><i class="fas fa-save"></i> Update Demo</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT SELL MODAL --}}
<div class="modal" id="editSellModal">
    <div class="modal-overlay" onclick="closeEditSellModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-edit"></i> Edit Artwork for Sale</h2>
            <button class="modal-close" onclick="closeEditSellModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="editSellForm" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="editArtworkId" name="artwork_id">

            <div class="modal-body">
                <div class="form-group">
                    <label>Current Image</label>
                    <div class="current-image-preview">
                        <img src="" alt="Current" id="editSellCurrentImage" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="editProductImage">Change Image (Optional)</label>
                    <div class="image-upload-area" id="editSellImageUploadArea">
                        <input type="file" id="editProductImage" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload new image</p>
                        </div>
                        <div class="image-preview" id="editSellImagePreview" style="display: none;">
                            <img src="" alt="Preview" id="editSellPreviewImage">
                            <button type="button" class="remove-image" onclick="removeEditSellImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editProductName">Product Name <span class="required">*</span></label>
                    <input type="text" id="editProductName" name="product_name" required maxlength="255">
                </div>

                <div class="form-group">
                    <label>Artwork Type <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" id="editTypePhysical" name="artwork_type" value="physical"> <span>Physical</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" id="editTypeDigital" name="artwork_type" value="digital"> <span>Digital</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editProductPrice">Price (RM) <span class="required">*</span></label>
                    <input type="number" id="editProductPrice" name="product_price" step="0.01" min="0.01" required>
                </div>

                <div class="form-group">
                    <label for="editProductShipping">
                        Shipping Fee (RM)
                        <span style="font-size:0.78rem; font-weight:400; color:#718096; margin-left:6px;">Enter 0 for free shipping</span>
                    </label>
                    <div style="position:relative;">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#718096;font-weight:600;font-size:0.9rem;pointer-events:none;">RM</span>
                        <input type="number" id="editProductShipping" name="shipping_fee"
                               placeholder="0.00" step="0.01" min="0" max="9999.99"
                               style="padding-left:40px;">
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:8px;">
                        <input type="checkbox" id="editFreeShippingCheck" onchange="toggleFreeShipping('editProductShipping', this)">
                        <label for="editFreeShippingCheck" style="margin:0;font-size:0.85rem;color:#48bb78;font-weight:600;cursor:pointer;">
                            <i class="fas fa-truck"></i> Free Shipping
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editProductMaterial">Material / Medium <span class="required">*</span></label>
                    <input type="text" id="editProductMaterial" name="material" required maxlength="255">
                </div>

                <div class="form-group">
                    <label>Dimensions <span class="required">*</span></label>
                    <div class="dimensions-row">
                        <div class="dim-col"><label>Height</label><input type="number" id="editProductHeight" name="height" step="0.1" required></div>
                        <span class="dim-separator">×</span>
                        <div class="dim-col"><label>Width</label><input type="number" id="editProductWidth" name="width" step="0.1" required></div>
                        <span class="dim-separator">×</span>
                        <div class="dim-col"><label>Depth</label><input type="number" id="editProductDepth" name="depth" step="0.1"></div>
                        <div class="dim-unit">
                            <label>Unit</label>
                            <select id="editProductUnit" name="unit">
                                <option value="cm">cm</option>
                                <option value="inch">in</option>
                                <option value="px">px</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Status <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" id="editStatusAvailable" name="status" value="available"> <span>Available</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" id="editStatusSoldOut" name="status" value="sold_out"> <span>Sold Out</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editProductDescription">Product Description (Optional)</label>
                    <textarea id="editProductDescription" name="product_description" rows="4" maxlength="2000"></textarea>
                    <span class="char-count" id="editSellDescCount">0 / 2000 characters</span>
                </div>

                {{-- BULK SELL --}}
                <div class="form-group bulk-sell-box">
                    <label class="radio-label" style="margin-bottom:0;">
                        <input type="checkbox" id="editBulkSellEnabled" name="bulk_sell_enabled" value="1"
                               onchange="toggleBulkSellFields('editBulkSellFields')">
                        <span class="bulk-sell-label"><i class="fas fa-tags"></i> Enable Bulk Sell Discount</span>
                    </label>
                    <small style="color:#718096;font-size:0.8rem;margin-top:4px;display:block;margin-left:23px;">
                        Offer a discount when buyers purchase above a certain quantity
                    </small>
                </div>

                <div id="editBulkSellFields" style="display:none; border:1px solid #ddd6fe; border-radius:8px; padding:16px; margin-top:-8px; background:#faf9ff;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label for="editBulkMinQty">Minimum Quantity <span class="required">*</span></label>
                            <input type="number" id="editBulkMinQty" name="bulk_sell_min_qty"
                                   placeholder="e.g. 50" min="2" step="1">
                            <small style="color:#718096;font-size:0.78rem;margin-top:4px;display:block;">
                                Discount applies when buyer orders this many or more
                            </small>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label for="editBulkDiscount">Discount (%) <span class="required">*</span></label>
                            <div style="position:relative;">
                                <input type="number" id="editBulkDiscount" name="bulk_sell_discount"
                                       placeholder="e.g. 10" min="1" max="99" step="0.1"
                                       style="padding-right:36px;">
                                <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#718096;font-weight:600;font-size:0.9rem;">%</span>
                            </div>
                            <small style="color:#718096;font-size:0.78rem;margin-top:4px;display:block;">
                                Percentage off the unit price
                            </small>
                        </div>
                    </div>
                    <div id="editBulkSellPreview" style="display:none; margin-top:12px; padding:10px 14px; background:#ede9fe; border-radius:6px; font-size:0.82rem; color:#5b21b6;">
                        <i class="fas fa-tag"></i> <span id="editBulkSellPreviewText"></span>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditSellModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="updateArtworkBtn"><i class="fas fa-save"></i> Update Artwork</button>
            </div>
        </form>
    </div>
</div>

{{-- SUCCESS POPUP --}}
<div class="success-popup" id="successPopup">
    <div class="success-content">
        <div class="success-icon"><i class="fas fa-check-circle"></i></div>
        <div><p id="successMessage">Success!</p></div>
    </div>
</div>

{{-- DELETE POPUP --}}
<div class="delete-popup" id="deletePopup">
    <div class="delete-content">
        <div class="delete-icon"><i class="fas fa-trash-alt"></i></div>
        <div><p id="deleteMessage">Deleted successfully!</p></div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function showSuccessPopup(message = 'Success!') {
    const popup = document.getElementById('successPopup');
    const messageEl = document.getElementById('successMessage');
    if (popup && messageEl) {
        messageEl.textContent = message;
        popup.classList.add('show');
        setTimeout(() => {
            popup.classList.add('hide');
            setTimeout(() => popup.classList.remove('show', 'hide'), 300);
        }, 3000);
    }
}

function showDeletePopup(message = 'Deleted successfully!') {
    const popup = document.getElementById('deletePopup');
    const messageEl = document.getElementById('deleteMessage');
    if (popup && messageEl) {
        messageEl.textContent = message;
        popup.classList.add('show');
        setTimeout(() => {
            popup.classList.add('hide');
            setTimeout(() => popup.classList.remove('show', 'hide'), 300);
        }, 3000);
    }
}

function toggleFreeShipping(inputId, checkbox) {
    const input = document.getElementById(inputId);
    if (checkbox.checked) {
        input.value = '0';
        input.disabled = true;
        input.style.background = '#f0fdf4';
        input.style.color = '#48bb78';
    } else {
        input.disabled = false;
        input.style.background = '';
        input.style.color = '';
    }
}

function syncFreeShippingCheckbox() {
    const val = parseFloat(document.getElementById('editProductShipping').value) || 0;
    const cb  = document.getElementById('editFreeShippingCheck');
    if (val === 0) {
        cb.checked = true;
        toggleFreeShipping('editProductShipping', cb);
    } else {
        cb.checked = false;
        toggleFreeShipping('editProductShipping', cb);
    }
}

function toggleBulkSellFields(fieldsId) {
    const checkbox = event.target;
    const fields   = document.getElementById(fieldsId);
    fields.style.display = checkbox.checked ? 'block' : 'none';
    if (!checkbox.checked) {
        fields.querySelectorAll('input[type="number"]').forEach(i => i.value = '');
        const preview = fields.querySelector('[id$="BulkSellPreview"]');
        if (preview) preview.style.display = 'none';
    }
}

function updateBulkPreview(qtyId, discountId, previewId, previewTextId) {
    const qty      = parseInt(document.getElementById(qtyId)?.value) || 0;
    const discount = parseFloat(document.getElementById(discountId)?.value) || 0;
    const preview  = document.getElementById(previewId);
    const text     = document.getElementById(previewTextId);
    if (qty >= 2 && discount > 0 && discount < 100 && preview && text) {
        text.textContent = `Buy ${qty} or more and get ${discount}% off each item`;
        preview.style.display = 'block';
    } else if (preview) {
        preview.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    ['editBulkMinQty', 'editBulkDiscount'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', () =>
            updateBulkPreview('editBulkMinQty', 'editBulkDiscount', 'editBulkSellPreview', 'editBulkSellPreviewText')
        );
    });

    @if(session('success'))
        showSuccessPopup('{{ session('success') }}');
    @endif

    @if(session('deleted'))
        showDeletePopup('{{ session('deleted') }}');
    @endif

    @if(session('error'))
        showDeletePopup('{{ session('error') }}');
    @endif
});
</script>

<script src="{{ asset('js/artistProfileModals.js') }}"></script>
<script src="{{ asset('js/artistProfile.js') }}"></script>
@endsection