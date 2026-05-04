@extends('layouts.app')

@section('title', 'Edit Artwork for Sale')

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
        <span class="cur">Edit Artwork for Sale</span>
    </div>
</div>

<main class="upload-page-main">

    <div class="upload-page-header">
        <div class="upload-page-header-inner">
            <div class="upload-page-header-icon sell-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <h1 class="upload-page-title">Edit Artwork for Sale</h1>
                <p class="upload-page-subtitle">Update your listing details</p>
            </div>
        </div>
        <a href="{{ route('artist.profile') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Studio
        </a>
    </div>

    <form id="sellEditForm" action="{{ route('artist.artwork.update', $artwork->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="upload-form-layout">

            {{-- LEFT: Image + Pricing Summary --}}
            <div class="upload-form-left">
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-image"></i>
                        <h2>Product Images</h2>
                        <span class="optional-badge">Change Optional</span>
                    </div>
                    <div class="form-card-body">

                        <p style="font-size:var(--fs-sm);color:var(--muted);margin-bottom:10px;font-weight:600;">
                            Images <span style="font-weight:400;">(click ✕ to remove, click + to add more)</span>
                        </p>

                        <div id="sellDeletedInputs"></div>

                        <input type="file" id="sellEditImage" name="new_images[]"
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               multiple style="display:none;">

                        <div class="img-manager-grid" id="sellImgManagerGrid">
                            @if($artwork->image_path)
                                <div class="img-manager-item" data-path="{{ $artwork->image_path }}" data-type="existing">
                                    <img src="{{ $artwork->image_url }}" alt="Main">
                                    <span class="img-manager-badge main-badge"><i class="fas fa-star"></i> Main</span>
                                    <button type="button" class="img-manager-remove"
                                            onclick="removeExistingImage(this, 'sell')" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                            @if($artwork->extra_images)
                                @foreach($artwork->extra_images as $extraPath)
                                <div class="img-manager-item" data-path="{{ $extraPath }}" data-type="existing">
                                    <img src="{{ asset('storage/' . $extraPath) }}" alt="Extra">
                                    <button type="button" class="img-manager-remove"
                                            onclick="removeExistingImage(this, 'sell')" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                @endforeach
                            @endif
                            <div class="img-manager-add" onclick="document.getElementById('sellEditImage').click()">
                                <i class="fas fa-plus"></i>
                                <span>Add</span>
                            </div>
                        </div>

                        <div class="image-tips" style="margin-top:10px;">
                            <div class="tip-item"><i class="bi bi-lightbulb"></i> First image is the main cover. Max 5MB each.</div>
                        </div>
                    </div>
                </div>

                {{-- Pricing preview --}}
                <div class="form-card pricing-preview-card" id="pricingPreviewCard" style="display:block;">
                    <div class="form-card-header">
                        <i class="fas fa-receipt"></i>
                        <h2>Pricing Summary</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="pricing-row">
                            <span>Base Price</span>
                            <span id="previewBasePrice">RM {{ number_format($artwork->product_price, 2) }}</span>
                        </div>
                        <div class="pricing-row">
                            <span>Shipping</span>
                            <span id="previewShipping">{{ $artwork->shipping_fee > 0 ? 'RM ' . number_format($artwork->shipping_fee, 2) : 'Free' }}</span>
                        </div>
                        <div class="pricing-divider"></div>
                        <div class="pricing-row total-row">
                            <span>Buyer Pays</span>
                            <span id="previewTotal">RM {{ number_format($artwork->product_price + $artwork->shipping_fee, 2) }}</span>
                        </div>
                        <div class="bulk-preview-row" id="bulkPreviewRow" style="{{ $artwork->bulk_sell_enabled ? 'display:flex;' : 'display:none;' }}">
                            <i class="fas fa-tags"></i>
                            <span id="bulkPreviewLabel">{{ $artwork->bulk_sell_enabled ? 'Bulk: ' . $artwork->bulk_sell_discount . '% off ≥' . $artwork->bulk_sell_min_qty . ' pcs' : '' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: All fields --}}
            <div class="upload-form-right">

                {{-- Product Info --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-pencil-alt"></i>
                        <h2>Product Info</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="field-group">
                            <label for="editSellName" class="field-label">Product Name <span class="required">*</span></label>
                            <input type="text" id="editSellName" name="product_name"
                                   value="{{ old('product_name', $artwork->product_name) }}"
                                   required maxlength="255" class="field-input">
                            <span class="field-counter" id="editNameCounter">0 / 255</span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Artwork Type <span class="required">*</span></label>
                            <div class="type-selector">
                                <label class="type-option" for="editSellTypePhysical">
                                    <input type="radio" id="editSellTypePhysical" name="artwork_type" value="physical"
                                           {{ old('artwork_type', $artwork->artwork_type) === 'physical' ? 'checked' : '' }}>
                                    <div class="type-option-inner"><i class="fas fa-box-open"></i><span>Physical</span></div>
                                </label>
                                <label class="type-option" for="editSellTypeDigital">
                                    <input type="radio" id="editSellTypeDigital" name="artwork_type" value="digital"
                                           {{ old('artwork_type', $artwork->artwork_type) === 'digital' ? 'checked' : '' }}>
                                    <div class="type-option-inner"><i class="fas fa-file-image"></i><span>Digital</span></div>
                                </label>
                            </div>
                        </div>
                        <div class="field-group">
                            <label for="editSellDesc" class="field-label">Description <span class="field-optional">(Optional)</span></label>
                            <textarea id="editSellDesc" name="product_description" rows="4" maxlength="2000"
                                      class="field-textarea">{{ old('product_description', $artwork->product_description) }}</textarea>
                            <span class="field-counter" id="editSellDescCounter">0 / 2000</span>
                        </div>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-dollar-sign"></i>
                        <h2>Pricing & Shipping</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="field-row">
                            <div class="field-group">
                                <label for="editSellPrice" class="field-label">Price (RM) <span class="required">*</span></label>
                                <div class="field-prefix-wrap">
                                    <span class="field-prefix">RM</span>
                                    <input type="number" id="editSellPrice" name="product_price"
                                           value="{{ old('product_price', $artwork->product_price) }}"
                                           placeholder="0.00" step="0.01" min="0.01" required
                                           class="field-input with-prefix" oninput="updatePricingPreview()">
                                </div>
                            </div>
                            <div class="field-group">
                                <label for="editSellShipping" class="field-label">Shipping Fee <span class="field-optional">0 = Free</span></label>
                                <div class="field-prefix-wrap">
                                    <span class="field-prefix">RM</span>
                                    <input type="number" id="editSellShipping" name="shipping_fee"
                                           value="{{ old('shipping_fee', $artwork->shipping_fee) }}"
                                           placeholder="0.00" step="0.01" min="0"
                                           class="field-input with-prefix" oninput="updatePricingPreview()">
                                </div>
                                <label class="free-ship-toggle" for="editFreeShipCheck">
                                    <input type="checkbox" id="editFreeShipCheck"
                                           onchange="toggleSellFreeShipping(this)"
                                           {{ old('shipping_fee', $artwork->shipping_fee) == 0 ? 'checked' : '' }}>
                                    <i class="fas fa-truck"></i> Free Shipping
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Specifications --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-ruler-combined"></i>
                        <h2>Artwork Specifications</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="field-group">
                            <label for="editSellMaterial" class="field-label">Material / Medium <span class="required">*</span></label>
                            <input type="text" id="editSellMaterial" name="material"
                                   value="{{ old('material', $artwork->material) }}"
                                   required maxlength="255" class="field-input">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Dimensions <span class="required">*</span></label>
                            <div class="dimensions-grid">
                                <div class="dim-field">
                                    <label>Height</label>
                                    <input type="number" name="height" step="0.1" required class="field-input"
                                           value="{{ old('height', $artwork->height) }}">
                                </div>
                                <span class="dim-x">×</span>
                                <div class="dim-field">
                                    <label>Width</label>
                                    <input type="number" name="width" step="0.1" required class="field-input"
                                           value="{{ old('width', $artwork->width) }}">
                                </div>
                                <span class="dim-x">×</span>
                                <div class="dim-field">
                                    <label>Depth <span class="field-optional">opt.</span></label>
                                    <input type="number" name="depth" step="0.1" class="field-input"
                                           value="{{ old('depth', $artwork->depth) }}">
                                </div>
                                <div class="dim-field unit-field">
                                    <label>Unit</label>
                                    <select name="unit" class="field-input">
                                        <option value="cm" {{ old('unit', $artwork->unit) === 'cm' ? 'selected' : '' }}>cm</option>
                                        <option value="inch" {{ old('unit', $artwork->unit) === 'inch' ? 'selected' : '' }}>inch</option>
                                        <option value="px" {{ old('unit', $artwork->unit) === 'px' ? 'selected' : '' }}>px</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Status <span class="required">*</span></label>
                            <div class="status-selector">
                                <label class="status-option available-opt" for="editSellStatusAvailable">
                                    <input type="radio" id="editSellStatusAvailable" name="status" value="available"
                                           {{ old('status', $artwork->status) === 'available' ? 'checked' : '' }}>
                                    <div class="status-option-inner"><i class="fas fa-check-circle"></i><span>Available</span></div>
                                </label>
                                <label class="status-option soldout-opt" for="editSellStatusSoldOut">
                                    <input type="radio" id="editSellStatusSoldOut" name="status" value="sold_out"
                                           {{ old('status', $artwork->status) === 'sold_out' ? 'checked' : '' }}>
                                    <div class="status-option-inner"><i class="fas fa-times-circle"></i><span>Sold Out</span></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bulk Sell --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-tags"></i>
                        <h2>Bulk Sell Discount</h2>
                        <span class="optional-badge">Optional</span>
                    </div>
                    <div class="form-card-body">
                        <label class="toggle-row" for="editBulkEnabled">
                            <div class="toggle-info">
                                <span class="toggle-title"><i class="fas fa-percentage"></i> Enable Bulk Sell Discount</span>
                                <span class="toggle-desc">Offer a discount when buyers purchase above a certain quantity</span>
                            </div>
                            <div class="toggle-switch-wrap">
                                <input type="checkbox" id="editBulkEnabled" name="bulk_sell_enabled" value="1"
                                       onchange="toggleBulkFields(this)"
                                       {{ old('bulk_sell_enabled', $artwork->bulk_sell_enabled) ? 'checked' : '' }}>
                                <span class="toggle-switch"></span>
                            </div>
                        </label>
                        <div id="bulkSellFields" style="display:{{ old('bulk_sell_enabled', $artwork->bulk_sell_enabled) ? 'block' : 'none' }}; margin-top:16px;">
                            <div class="field-row">
                                <div class="field-group">
                                    <label for="bulkMinQty" class="field-label">Minimum Quantity <span class="required">*</span></label>
                                    <input type="number" id="bulkMinQty" name="bulk_sell_min_qty"
                                           value="{{ old('bulk_sell_min_qty', $artwork->bulk_sell_min_qty) }}"
                                           placeholder="e.g. 50" min="2" step="1" class="field-input"
                                           oninput="updateBulkPreview()">
                                </div>
                                <div class="field-group">
                                    <label for="bulkDiscount" class="field-label">Discount (%) <span class="required">*</span></label>
                                    <div class="field-suffix-wrap">
                                        <input type="number" id="bulkDiscount" name="bulk_sell_discount"
                                               value="{{ old('bulk_sell_discount', $artwork->bulk_sell_discount) }}"
                                               placeholder="e.g. 10" min="1" max="99" step="0.1"
                                               class="field-input with-suffix" oninput="updateBulkPreview()">
                                        <span class="field-suffix">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bulk-preview-strip" id="bulkPreviewStrip" style="{{ old('bulk_sell_enabled', $artwork->bulk_sell_enabled) && $artwork->bulk_sell_min_qty ? 'display:flex;' : 'display:none;' }}">
                                <i class="fas fa-tag"></i>
                                <span id="bulkPreviewText">{{ $artwork->bulk_sell_enabled ? 'Buy ' . $artwork->bulk_sell_min_qty . ' or more and get ' . $artwork->bulk_sell_discount . '% off each item' : '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- Promotion --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-fire"></i>
                        <h2>Promotion</h2>
                        <span class="optional-badge">Optional</span>
                    </div>
                    <div class="form-card-body">
                        <label class="toggle-row" for="promotionEnabled">
                            <div class="toggle-info">
                                <span class="toggle-title">
                                    <i class="fas fa-tag"></i>
                                    Enable Promotion
                                </span>
                                <span class="toggle-desc">Set a promotional discount with an optional time period</span>
                            </div>
                            <div class="toggle-switch-wrap">
                                <input type="checkbox" id="promotionEnabled" name="promotion_enabled" value="1"
                                       onchange="togglePromoFields(this)"
                                       {{ old('promotion_enabled', $artwork->promotion_enabled) ? 'checked' : '' }}>
                                <span class="toggle-switch"></span>
                            </div>
                        </label>

                        <div id="promoFields" style="display:{{ old('promotion_enabled', $artwork->promotion_enabled) ? 'block' : 'none' }}; margin-top:16px;">
                            <div class="field-group">
                                <label for="promoDiscount" class="field-label">
                                    Promotion Discount (%) <span class="required">*</span>
                                </label>
                                <div class="field-suffix-wrap">
                                    <input type="number" id="promoDiscount" name="promotion_discount"
                                           value="{{ old('promotion_discount', $artwork->promotion_discount) }}"
                                           placeholder="e.g. 20" min="1" max="99" step="0.1"
                                           class="field-input with-suffix"
                                           oninput="updatePromoPreview()">
                                    <span class="field-suffix">%</span>
                                </div>
                            </div>

                            <div class="promo-price-preview" id="promoPricePreview"
                                 style="{{ $artwork->promotion_enabled && $artwork->promotion_discount ? 'display:block;' : 'display:none;' }}">
                                <div class="promo-preview-inner">
                                    <span class="promo-original" id="promoOriginalPrice">
                                        RM {{ number_format($artwork->product_price, 2) }}
                                    </span>
                                    <i class="fas fa-arrow-right" style="color:var(--muted);font-size:11px;"></i>
                                    <span class="promo-final" id="promoFinalPrice">
                                        @if($artwork->promotion_enabled && $artwork->promotion_discount)
                                            RM {{ number_format($artwork->product_price * (1 - $artwork->promotion_discount / 100), 2) }}
                                        @endif
                                    </span>
                                    <span class="promo-saving" id="promoSaving">
                                        @if($artwork->promotion_enabled && $artwork->promotion_discount)
                                            Save {{ $artwork->promotion_discount }}%
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="field-row" style="margin-top:12px;">
                                <div class="field-group">
                                    <label for="promoStartsAt" class="field-label">
                                        Start Date <span class="field-optional">(Optional)</span>
                                    </label>
                                    <input type="datetime-local" id="promoStartsAt" name="promotion_starts_at"
                                           value="{{ old('promotion_starts_at', $artwork->promotion_starts_at ? $artwork->promotion_starts_at->format('Y-m-d\TH:i') : '') }}"
                                           class="field-input">
                                    <span class="field-hint">Leave blank to start immediately</span>
                                </div>
                                <div class="field-group">
                                    <label for="promoEndsAt" class="field-label">
                                        End Date <span class="field-optional">(Optional)</span>
                                    </label>
                                    <input type="datetime-local" id="promoEndsAt" name="promotion_ends_at"
                                           value="{{ old('promotion_ends_at', $artwork->promotion_ends_at ? $artwork->promotion_ends_at->format('Y-m-d\TH:i') : '') }}"
                                           class="field-input">
                                    <span class="field-hint">Leave blank for no expiry</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sync notice if cross-posted --}}
                @if($artwork->is_cross_posted)
                <div class="form-card" style="border-left:3px solid var(--primary);">
                    <div class="form-card-header">
                        <i class="fas fa-images"></i>
                        <h2>Linked Demo Gallery</h2>
                        <span class="type-badge">Synced</span>
                    </div>
                    <div class="form-card-body">
                        <div class="field-hint" style="background:var(--lavender);border:1px solid #ddd6fe;border-radius:6px;padding:10px 12px;font-size:var(--fs-sm);color:var(--primary-2);">
                            <i class="fas fa-link"></i> Product name, description and images sync automatically to the linked demo gallery item.
                        </div>
                    </div>
                </div>
                @endif

                <div class="form-submit-bar">
                    <a href="{{ route('artist.profile') }}" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-submit sell-submit" id="sellEditSubmitBtn">
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
const sellEditNewFiles = [];

document.addEventListener('DOMContentLoaded', function () {
    setupCounter('editSellName', 'editNameCounter', 255);
    setupCounter('editSellDesc', 'editSellDescCounter', 2000);

    // Init free shipping checkbox state
    const shipInput = document.getElementById('editSellShipping');
    const shipCheck = document.getElementById('editFreeShipCheck');
    if (shipInput && shipCheck && parseFloat(shipInput.value) == 0) {
        shipCheck.checked = true;
        shipInput.disabled = true;
        shipInput.style.background = '#f0fdf4';
        shipInput.style.color = '#16a34a';
    }

    const fileInput = document.getElementById('sellEditImage');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            addNewImagesToGrid(Array.from(this.files), 'sellImgManagerGrid', sellEditNewFiles, 'sellEditImage', 'sell');
            this.value = '';
        });
    }

    const form = document.getElementById('sellEditForm');
    const btn  = document.getElementById('sellEditSubmitBtn');
    if (form && btn) {
        form.addEventListener('submit', function () {
            syncEditFiles(sellEditNewFiles, 'sellEditImage');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Saving...';
        });
    }

    // Override sell functions to use correct IDs
    document.getElementById('editSellPrice')?.addEventListener('input', updatePricingPreview);
    document.getElementById('editSellShipping')?.addEventListener('input', updatePricingPreview);
    document.getElementById('editSellPrice')?.addEventListener('input', updatePromoPreview);

    // Init promo state on page load
    if (document.getElementById('promotionEnabled')?.checked) {
        updatePromoPreview();
    }
});

// Override for edit page field IDs
function updatePricingPreview() {
    const price    = parseFloat(document.getElementById('editSellPrice')?.value || document.getElementById('sellPrice')?.value) || 0;
    const shipping = parseFloat(document.getElementById('editSellShipping')?.value || document.getElementById('sellShipping')?.value) || 0;
    const card     = document.getElementById('pricingPreviewCard');
    if (!card) return;
    if (price > 0) {
        card.style.display = 'block';
        document.getElementById('previewBasePrice').textContent = `RM ${price.toFixed(2)}`;
        document.getElementById('previewShipping').textContent  = shipping > 0 ? `RM ${shipping.toFixed(2)}` : 'Free';
        document.getElementById('previewTotal').textContent     = `RM ${(price + shipping).toFixed(2)}`;
    }
    updateBulkPreview();
}

function toggleSellFreeShipping(checkbox) {
    const input = document.getElementById('editSellShipping');
    if (!input) return;
    if (checkbox.checked) {
        input.value = '0'; input.disabled = true;
        input.style.background = '#f0fdf4'; input.style.color = '#16a34a';
    } else {
        input.disabled = false; input.style.background = ''; input.style.color = '';
    }
    updatePricingPreview();
}
</script>
@endsection