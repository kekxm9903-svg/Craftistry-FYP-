@extends('layouts.app')

@section('title', 'List Artwork for Sale')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/artistProfile.css') }}">
<link rel="stylesheet" href="{{ asset('css/uploadForm.css') }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="bc-bar">
    <div class="bc-inner">
        <a href="{{ route('dashboard') }}">Home</a>
        <span class="sep">/</span>
        <a href="{{ route('artist.profile') }}">Studio</a>
        <span class="sep">/</span>
        <span class="cur">List Artwork for Sale</span>
    </div>
</div>

<main class="upload-page-main">

    {{-- Page Header --}}
    <div class="upload-page-header">
        <div class="upload-page-header-inner">
            <div class="upload-page-header-icon sell-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div>
                <h1 class="upload-page-title">List Artwork for Sale</h1>
                <p class="upload-page-subtitle">Set up your listing and start selling your artwork</p>
            </div>
        </div>
        <a href="{{ route('artist.profile') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Studio
        </a>
    </div>

    <form id="sellUploadForm" action="{{ route('artist.artwork.sell') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="upload-form-layout">

            {{-- LEFT: Image Upload --}}
            <div class="upload-form-left">
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-image"></i>
                        <h2>Product Image</h2>
                        <span class="required-badge">Required</span>
                    </div>
                    <div class="form-card-body">
                        {{-- Drop Zone --}}
                        <div class="image-drop-zone" id="sellDropZone">
                            <input type="file" id="sellImage" name="images[]"
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                   multiple>
                            <div class="drop-zone-inner" id="sellDropInner">
                                <div class="drop-zone-icon sell-drop-icon">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                </div>
                                <p class="drop-zone-title">Drop images here</p>
                                <p class="drop-zone-sub">or <span class="drop-browse">browse files</span></p>
                                <div class="drop-zone-hints">
                                    <span><i class="bi bi-check-circle"></i> JPG, PNG, GIF, WEBP</span>
                                    <span><i class="bi bi-check-circle"></i> Max 5MB each</span>
                                    <span><i class="bi bi-images"></i> Multiple allowed</span>
                                </div>
                            </div>
                        </div>

                        {{-- Multi Preview Grid --}}
                        <div class="multi-preview-grid" id="sellPreviewGrid"></div>

                        <div class="image-tips" style="margin-top:10px;">
                            <div class="tip-item"><i class="bi bi-lightbulb"></i> Clear, well-lit photos attract more buyers</div>
                            <div class="tip-item"><i class="bi bi-aspect-ratio"></i> Show the full artwork in the main image</div>
                        </div>
                    </div>
                </div>

                {{-- Pricing Preview Card --}}
                <div class="form-card pricing-preview-card" id="pricingPreviewCard" style="display:none;">
                    <div class="form-card-header">
                        <i class="fas fa-receipt"></i>
                        <h2>Pricing Summary</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="pricing-row">
                            <span>Base Price</span>
                            <span id="previewBasePrice">RM 0.00</span>
                        </div>
                        <div class="pricing-row">
                            <span>Shipping</span>
                            <span id="previewShipping">Free</span>
                        </div>
                        <div class="pricing-divider"></div>
                        <div class="pricing-row total-row">
                            <span>Buyer Pays</span>
                            <span id="previewTotal">RM 0.00</span>
                        </div>
                        <div class="bulk-preview-row" id="bulkPreviewRow" style="display:none;">
                            <i class="fas fa-tags"></i>
                            <span id="bulkPreviewLabel"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Form Fields --}}
            <div class="upload-form-right">

                {{-- Basic Info --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-pencil-alt"></i>
                        <h2>Product Info</h2>
                    </div>
                    <div class="form-card-body">

                        <div class="field-group">
                            <label for="sellProductName" class="field-label">
                                Product Name <span class="required">*</span>
                            </label>
                            <input type="text" id="sellProductName" name="product_name"
                                   placeholder="e.g. Sunset Over Petaling Jaya — Original Watercolour"
                                   required maxlength="255" class="field-input"
                                   value="{{ old('product_name') }}">
                            <span class="field-counter" id="nameCounter">0 / 255</span>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Artwork Type <span class="required">*</span></label>
                            <div class="type-selector">
                                <label class="type-option" for="sellTypePhysical">
                                    <input type="radio" id="sellTypePhysical" name="artwork_type" value="physical"
                                           {{ old('artwork_type', 'physical') === 'physical' ? 'checked' : '' }}>
                                    <div class="type-option-inner">
                                        <i class="fas fa-box-open"></i>
                                        <span>Physical</span>
                                    </div>
                                </label>
                                <label class="type-option" for="sellTypeDigital">
                                    <input type="radio" id="sellTypeDigital" name="artwork_type" value="digital"
                                           {{ old('artwork_type') === 'digital' ? 'checked' : '' }}>
                                    <div class="type-option-inner">
                                        <i class="fas fa-file-image"></i>
                                        <span>Digital</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="field-group">
                            <label for="sellProductDesc" class="field-label">
                                Description <span class="field-optional">(Optional)</span>
                            </label>
                            <textarea id="sellProductDesc" name="product_description"
                                      rows="4" maxlength="2000"
                                      placeholder="Tell buyers about your artwork — story, technique, inspiration..."
                                      class="field-textarea">{{ old('product_description') }}</textarea>
                            <span class="field-counter" id="sellDescCounter">0 / 2000</span>
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
                                <label for="sellPrice" class="field-label">Price (RM) <span class="required">*</span></label>
                                <div class="field-prefix-wrap">
                                    <span class="field-prefix">RM</span>
                                    <input type="number" id="sellPrice" name="product_price"
                                           placeholder="0.00" step="0.01" min="0.01" max="999999.99"
                                           required class="field-input with-prefix"
                                           value="{{ old('product_price') }}"
                                           oninput="updatePricingPreview()">
                                </div>
                            </div>
                            <div class="field-group">
                                <label for="sellShipping" class="field-label">
                                    Shipping Fee
                                    <span class="field-optional">0 = Free</span>
                                </label>
                                <div class="field-prefix-wrap">
                                    <span class="field-prefix">RM</span>
                                    <input type="number" id="sellShipping" name="shipping_fee"
                                           placeholder="0.00" step="0.01" min="0" max="9999.99"
                                           value="{{ old('shipping_fee', '0') }}"
                                           class="field-input with-prefix"
                                           oninput="updatePricingPreview()">
                                </div>
                                <label class="free-ship-toggle" for="freeShipCheck">
                                    <input type="checkbox" id="freeShipCheck" onchange="toggleSellFreeShipping(this)">
                                    <i class="fas fa-truck"></i> Mark as Free Shipping
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Artwork Details --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-ruler-combined"></i>
                        <h2>Artwork Specifications</h2>
                    </div>
                    <div class="form-card-body">

                        <div class="field-group">
                            <label for="sellMaterial" class="field-label">Material / Medium <span class="required">*</span></label>
                            <input type="text" id="sellMaterial" name="material"
                                   placeholder="e.g. Oil on Canvas, Watercolour on 300gsm, Procreate Digital"
                                   required maxlength="255" class="field-input"
                                   value="{{ old('material') }}">
                        </div>

                        <div class="field-group">
                            <label class="field-label">Dimensions <span class="required">*</span></label>
                            <div class="dimensions-grid">
                                <div class="dim-field">
                                    <label>Height</label>
                                    <input type="number" name="height" step="0.1" required class="field-input"
                                           value="{{ old('height') }}">
                                </div>
                                <span class="dim-x">×</span>
                                <div class="dim-field">
                                    <label>Width</label>
                                    <input type="number" name="width" step="0.1" required class="field-input"
                                           value="{{ old('width') }}">
                                </div>
                                <span class="dim-x">×</span>
                                <div class="dim-field">
                                    <label>Depth <span class="field-optional">opt.</span></label>
                                    <input type="number" name="depth" step="0.1" class="field-input"
                                           value="{{ old('depth') }}">
                                </div>
                                <div class="dim-field unit-field">
                                    <label>Unit</label>
                                    <select name="unit" class="field-input">
                                        <option value="cm" {{ old('unit') === 'cm' ? 'selected' : '' }}>cm</option>
                                        <option value="inch" {{ old('unit') === 'inch' ? 'selected' : '' }}>inch</option>
                                        <option value="px" {{ old('unit') === 'px' ? 'selected' : '' }}>px</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Availability Status <span class="required">*</span></label>
                            <div class="status-selector">
                                <label class="status-option available-opt" for="sellStatusAvailable">
                                    <input type="radio" id="sellStatusAvailable" name="status" value="available"
                                           {{ old('status', 'available') === 'available' ? 'checked' : '' }}>
                                    <div class="status-option-inner">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Available</span>
                                    </div>
                                </label>
                                <label class="status-option soldout-opt" for="sellStatusSoldOut">
                                    <input type="radio" id="sellStatusSoldOut" name="status" value="sold_out"
                                           {{ old('status') === 'sold_out' ? 'checked' : '' }}>
                                    <div class="status-option-inner">
                                        <i class="fas fa-times-circle"></i>
                                        <span>Sold Out</span>
                                    </div>
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
                        <label class="toggle-row" for="bulkSellEnabled">
                            <div class="toggle-info">
                                <span class="toggle-title">
                                    <i class="fas fa-percentage"></i>
                                    Enable Bulk Sell Discount
                                </span>
                                <span class="toggle-desc">Offer a discount when buyers purchase above a certain quantity</span>
                            </div>
                            <div class="toggle-switch-wrap">
                                <input type="checkbox" id="bulkSellEnabled" name="bulk_sell_enabled" value="1"
                                       onchange="toggleBulkFields(this)" {{ old('bulk_sell_enabled') ? 'checked' : '' }}>
                                <span class="toggle-switch"></span>
                            </div>
                        </label>

                        <div id="bulkSellFields" style="display:{{ old('bulk_sell_enabled') ? 'block' : 'none' }}; margin-top: 16px;">
                            <div class="field-row">
                                <div class="field-group">
                                    <label for="bulkMinQty" class="field-label">Minimum Quantity <span class="required">*</span></label>
                                    <input type="number" id="bulkMinQty" name="bulk_sell_min_qty"
                                           placeholder="e.g. 50" min="2" step="1" class="field-input"
                                           value="{{ old('bulk_sell_min_qty') }}"
                                           oninput="updateBulkPreview()">
                                    <span class="field-hint">Discount activates at this quantity</span>
                                </div>
                                <div class="field-group">
                                    <label for="bulkDiscount" class="field-label">Discount (%) <span class="required">*</span></label>
                                    <div class="field-suffix-wrap">
                                        <input type="number" id="bulkDiscount" name="bulk_sell_discount"
                                               placeholder="e.g. 10" min="1" max="99" step="0.1"
                                               class="field-input with-suffix"
                                               value="{{ old('bulk_sell_discount') }}"
                                               oninput="updateBulkPreview()">
                                        <span class="field-suffix">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bulk-preview-strip" id="bulkPreviewStrip" style="display:none;">
                                <i class="fas fa-tag"></i>
                                <span id="bulkPreviewText"></span>
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
                                       onchange="togglePromoFields(this)" {{ old('promotion_enabled') ? 'checked' : '' }}>
                                <span class="toggle-switch"></span>
                            </div>
                        </label>

                        <div id="promoFields" style="display:{{ old('promotion_enabled') ? 'block' : 'none' }}; margin-top:16px;">
                            <div class="field-group">
                                <label for="promoDiscount" class="field-label">
                                    Promotion Discount (%) <span class="required">*</span>
                                </label>
                                <div class="field-suffix-wrap">
                                    <input type="number" id="promoDiscount" name="promotion_discount"
                                           value="{{ old('promotion_discount') }}"
                                           placeholder="e.g. 20" min="1" max="99" step="0.1"
                                           class="field-input with-suffix"
                                           oninput="updatePromoPreview()">
                                    <span class="field-suffix">%</span>
                                </div>
                            </div>

                            {{-- Live promo price preview --}}
                            <div class="promo-price-preview" id="promoPricePreview" style="display:none;">
                                <div class="promo-preview-inner">
                                    <span class="promo-original" id="promoOriginalPrice"></span>
                                    <i class="fas fa-arrow-right" style="color:var(--muted);font-size:11px;"></i>
                                    <span class="promo-final" id="promoFinalPrice"></span>
                                    <span class="promo-saving" id="promoSaving"></span>
                                </div>
                            </div>

                            <div class="field-row" style="margin-top:12px;">
                                <div class="field-group">
                                    <label for="promoStartsAt" class="field-label">
                                        Start Date <span class="field-optional">(Optional)</span>
                                    </label>
                                    <input type="datetime-local" id="promoStartsAt" name="promotion_starts_at"
                                           value="{{ old('promotion_starts_at') }}"
                                           class="field-input">
                                    <span class="field-hint">Leave blank to start immediately</span>
                                </div>
                                <div class="field-group">
                                    <label for="promoEndsAt" class="field-label">
                                        End Date <span class="field-optional">(Optional)</span>
                                    </label>
                                    <input type="datetime-local" id="promoEndsAt" name="promotion_ends_at"
                                           value="{{ old('promotion_ends_at') }}"
                                           class="field-input">
                                    <span class="field-hint">Leave blank for no expiry</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Portfolio Cross-post --}}
                <div class="form-card option-card">
                    <div class="option-card-inner">
                        <label class="toggle-row" for="alsoDemoCheck">
                            <div class="toggle-info">
                                <span class="toggle-title">
                                    <i class="fas fa-images"></i>
                                    Also show in Demo / Portfolio Gallery?
                                </span>
                                <span class="toggle-desc">Display this artwork in your public portfolio alongside other demos</span>
                            </div>
                            <div class="toggle-switch-wrap">
                                <input type="checkbox" id="alsoDemoCheck" name="also_demo" value="1"
                                       {{ old('also_demo', '1') ? 'checked' : '' }}>
                                <span class="toggle-switch"></span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="form-submit-bar">
                    <a href="{{ route('artist.profile') }}" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-submit sell-submit" id="sellSubmitBtn">
                        <i class="fas fa-shopping-bag"></i>
                        List Artwork for Sale
                    </button>
                </div>

            </div>
        </div>
    </form>
</main>

@endsection

@section('scripts')
<script src="{{ asset('js/uploadForm.js') }}"></script>
@endsection