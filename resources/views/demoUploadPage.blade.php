@extends('layouts.app')

@section('title', 'Upload Demo Artwork')

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
        <span class="cur">Upload Demo</span>
    </div>
</div>

<main class="upload-page-main">

    {{-- Page Header --}}
    <div class="upload-page-header">
        <div class="upload-page-header-inner">
            <div class="upload-page-header-icon demo-icon">
                <i class="fas fa-images"></i>
            </div>
            <div>
                <h1 class="upload-page-title">Upload Demo Artwork</h1>
                <p class="upload-page-subtitle">Showcase your creative process and portfolio pieces</p>
            </div>
        </div>
        <a href="{{ route('artist.profile') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Back to Studio
        </a>
    </div>

    @if($errors->any())
        <div style="max-width:1100px;margin:0 auto var(--sp-md);padding:var(--sp-md) var(--sp-lg);border-radius:var(--radius-md);background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;">
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @php $artworkTypes = \App\Models\ArtworkType::orderBy('name')->get(); @endphp

    <form id="demoUploadForm" action="{{ route('artist.demo.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="upload-form-layout">

            {{-- LEFT: Image Upload --}}
            <div class="upload-form-left">
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-image"></i>
                        <h2>Artwork Image</h2>
                        <span class="required-badge">Required</span>
                    </div>
                    <div class="form-card-body">
                        <div class="image-drop-zone" id="demoDropZone">
                            <input type="file" id="demoImage" name="images[]"
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                   multiple>
                            <div class="drop-zone-inner" id="demoDropInner">
                                <div class="drop-zone-icon">
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

                        <div class="multi-preview-grid" id="demoPreviewGrid"></div>

                        <div class="image-tips" style="margin-top:10px;">
                            <div class="tip-item"><i class="bi bi-lightbulb"></i> Use high resolution images for better showcase</div>
                            <div class="tip-item"><i class="bi bi-aspect-ratio"></i> Square images (1:1) display best in the gallery</div>
                        </div>
                    </div>
                </div>

                {{-- Pricing Preview Card (shown when also_sell is on) --}}
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

                {{-- ══ DEMO DETAILS ══ --}}
                <div class="form-card">
                    <div class="form-card-header">
                        <i class="fas fa-pencil-alt"></i>
                        <h2>Demo Details</h2>
                    </div>
                    <div class="form-card-body">
                        <div class="field-group">
                            <label for="demoTitle" class="field-label">
                                Title <span class="required">*</span>
                            </label>
                            <input type="text" id="demoTitle" name="title"
                                   placeholder="Give your demo a compelling title..."
                                   required maxlength="255" class="field-input"
                                   value="{{ old('title') }}">
                            <span class="field-counter" id="titleCounter">0 / 255</span>
                        </div>

                        <div class="field-group">
                            <label for="demoDescription" class="field-label">
                                Description <span class="field-optional">(Optional)</span>
                            </label>
                            <textarea id="demoDescription" name="description"
                                      rows="5" maxlength="1000"
                                      placeholder="Describe your demo artwork — materials used, inspiration, process..."
                                      class="field-textarea">{{ old('description') }}</textarea>
                            <span class="field-counter" id="descCounter">0 / 1000</span>
                        </div>
                    </div>
                </div>

                {{-- ══ ALSO SELL TOGGLE ══ --}}
                <div class="form-card option-card">
                    <div class="option-card-inner">
                        <label class="toggle-row" for="alsoSellCheckbox">
                            <div class="toggle-info">
                                <span class="toggle-title">
                                    <i class="fas fa-shopping-bag"></i>
                                    Also list this artwork for Sale?
                                </span>
                                <span class="toggle-desc">Upload once and show in both Demo Gallery and Artwork Sell listings</span>
                            </div>
                            <div class="toggle-switch-wrap">
                                <input type="checkbox" id="alsoSellCheckbox" name="also_sell" value="1"
                                       onchange="toggleDemoSellFields()" {{ old('also_sell') ? 'checked' : '' }}>
                                <span class="toggle-switch"></span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- ══ SALE FIELDS (hidden by default) ══ --}}
                <div id="demoSellSection" style="display:{{ old('also_sell') ? 'flex' : 'none' }};flex-direction:column;gap:var(--sp-md);">

                    {{-- Product Info --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-tag"></i>
                            <h2>Sale Details</h2>
                            <span class="sale-chip">Artwork Sell</span>
                        </div>
                        <div class="form-card-body">

                            {{-- Product Name --}}
                            <div class="field-group">
                                <label for="productName" class="field-label">
                                    Product Name <span class="required">*</span>
                                    <span class="field-optional" style="text-transform:none;font-weight:400;">&nbsp;— defaults to demo title if blank</span>
                                </label>
                                <input type="text" id="productName" name="product_name"
                                       placeholder="Leave blank to use demo title..."
                                       maxlength="255" class="field-input"
                                       value="{{ old('product_name') }}">
                            </div>

                            {{-- Artwork Type --}}
                            <div class="field-group">
                                <label class="field-label">Artwork Type <span class="required">*</span></label>
                                <div class="type-selector">
                                    <label class="type-option" for="demoTypePhysical">
                                        <input type="radio" id="demoTypePhysical" name="artwork_type" value="physical" class="sell-req"
                                               {{ old('artwork_type', 'physical') === 'physical' ? 'checked' : '' }}>
                                        <div class="type-option-inner">
                                            <i class="fas fa-box-open"></i>
                                            <span>Physical</span>
                                        </div>
                                    </label>
                                    <label class="type-option" for="demoTypeDigital">
                                        <input type="radio" id="demoTypeDigital" name="artwork_type" value="digital" class="sell-req"
                                               {{ old('artwork_type') === 'digital' ? 'checked' : '' }}>
                                        <div class="type-option-inner">
                                            <i class="fas fa-file-image"></i>
                                            <span>Digital</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Product Category --}}
                            <div class="field-group">
                                <label class="field-label">
                                    Product Category <span class="required">*</span>
                                </label>
                                <p class="field-hint" style="margin-bottom:10px;">
                                    Select the category that best describes your artwork
                                </p>
                                @if($artworkTypes->isNotEmpty())
                                    <div class="category-grid" style="grid-template-columns: repeat(3, 1fr);">
                                        @foreach($artworkTypes as $type)
                                        <label class="category-option" for="demo_cat_{{ Str::slug($type->name) }}">
                                            <input type="radio"
                                                   id="demo_cat_{{ Str::slug($type->name) }}"
                                                   name="product_category"
                                                   value="{{ $type->name }}"
                                                   class="sell-req"
                                                   {{ old('product_category') === $type->name ? 'checked' : '' }}>
                                            <div class="category-option-inner">
                                                <div class="category-icon"><i class="fas fa-palette"></i></div>
                                                <span class="category-name">{{ $type->name }}</span>
                                                <div class="category-check"><i class="fas fa-check"></i></div>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="field-hint" style="color:#dc2626;">No artwork categories found.</p>
                                @endif
                            </div>

                            {{-- Product Description --}}
                            <div class="field-group">
                                <label for="demoProductDesc" class="field-label">
                                    Product Description <span class="field-optional">(Optional)</span>
                                </label>
                                <textarea id="demoProductDesc" name="product_description"
                                          rows="4" maxlength="2000" class="field-textarea"
                                          placeholder="Describe your artwork for buyers — condition, framing, delivery notes...">{{ old('product_description') }}</textarea>
                                <span class="field-counter" id="demoProductDescCounter">0 / 2000</span>
                            </div>

                        </div>
                    </div>

                    {{-- Pricing & Shipping --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-dollar-sign"></i>
                            <h2>Pricing & Shipping</h2>
                        </div>
                        <div class="form-card-body">
                            <div class="field-row">
                                <div class="field-group">
                                    <label for="demoPrice" class="field-label">Price (RM) <span class="required">*</span></label>
                                    <div class="field-prefix-wrap">
                                        <span class="field-prefix">RM</span>
                                        <input type="number" id="demoPrice" name="product_price"
                                               placeholder="0.00" step="0.01" min="0.01" max="999999.99"
                                               class="field-input with-prefix sell-req"
                                               value="{{ old('product_price') }}"
                                               oninput="updatePricingPreview()">
                                    </div>
                                </div>
                                <div class="field-group">
                                    <label for="demoShipping" class="field-label">
                                        Shipping Fee
                                        <span class="field-optional">0 = Free</span>
                                    </label>
                                    <div class="field-prefix-wrap">
                                        <span class="field-prefix">RM</span>
                                        <input type="number" id="demoShipping" name="shipping_fee"
                                               placeholder="0.00" step="0.01" min="0"
                                               value="{{ old('shipping_fee', '0') }}"
                                               class="field-input with-prefix"
                                               oninput="updatePricingPreview()">
                                    </div>
                                    <label class="free-ship-toggle" for="demoFreeShipCheck">
                                        <input type="checkbox" id="demoFreeShipCheck" onchange="toggleDemoFreeShipping(this)">
                                        <i class="fas fa-truck"></i> Mark as Free Shipping
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Artwork Specifications --}}
                    <div class="form-card">
                        <div class="form-card-header">
                            <i class="fas fa-ruler-combined"></i>
                            <h2>Artwork Specifications</h2>
                        </div>
                        <div class="form-card-body">

                            <div class="field-group">
                                <label for="demoMaterial" class="field-label">Material / Medium <span class="required">*</span></label>
                                <input type="text" id="demoMaterial" name="material"
                                       placeholder="e.g. Oil on Canvas, Watercolour, Digital Illustration"
                                       class="field-input sell-req"
                                       value="{{ old('material') }}" maxlength="255">
                            </div>

                            <div class="field-group">
                                <label class="field-label">Dimensions <span class="required">*</span></label>
                                <div class="dimensions-grid">
                                    <div class="dim-field">
                                        <label>Height</label>
                                        <input type="number" name="height" step="0.1"
                                               class="field-input sell-req" value="{{ old('height') }}">
                                    </div>
                                    <span class="dim-x">×</span>
                                    <div class="dim-field">
                                        <label>Width</label>
                                        <input type="number" name="width" step="0.1"
                                               class="field-input sell-req" value="{{ old('width') }}">
                                    </div>
                                    <span class="dim-x">×</span>
                                    <div class="dim-field">
                                        <label>Depth <span class="field-optional">opt.</span></label>
                                        <input type="number" name="depth" step="0.1"
                                               class="field-input" value="{{ old('depth') }}">
                                    </div>
                                    <div class="dim-field unit-field">
                                        <label>Unit</label>
                                        <select name="unit" class="field-input">
                                            <option value="cm"   {{ old('unit') === 'cm'   ? 'selected' : '' }}>cm</option>
                                            <option value="inch" {{ old('unit') === 'inch' ? 'selected' : '' }}>inch</option>
                                            <option value="px"   {{ old('unit') === 'px'   ? 'selected' : '' }}>px</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Availability Status <span class="required">*</span></label>
                                <div class="status-selector">
                                    <label class="status-option available-opt" for="demoStatusAvailable">
                                        <input type="radio" id="demoStatusAvailable" name="status" value="available" class="sell-req"
                                               {{ old('status', 'available') === 'available' ? 'checked' : '' }}>
                                        <div class="status-option-inner">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Available</span>
                                        </div>
                                    </label>
                                    <label class="status-option soldout-opt" for="demoStatusSoldOut">
                                        <input type="radio" id="demoStatusSoldOut" name="status" value="sold_out" class="sell-req"
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
                            <label class="toggle-row" for="demoBulkEnabled">
                                <div class="toggle-info">
                                    <span class="toggle-title"><i class="fas fa-percentage"></i> Enable Bulk Sell Discount</span>
                                    <span class="toggle-desc">Offer a discount when buyers purchase above a certain quantity</span>
                                </div>
                                <div class="toggle-switch-wrap">
                                    <input type="checkbox" id="demoBulkEnabled" name="bulk_sell_enabled" value="1"
                                           onchange="toggleBulkFields(this)" {{ old('bulk_sell_enabled') ? 'checked' : '' }}>
                                    <span class="toggle-switch"></span>
                                </div>
                            </label>
                            <div id="bulkSellFields" style="display:{{ old('bulk_sell_enabled') ? 'block' : 'none' }};margin-top:16px;">
                                <div class="field-row">
                                    <div class="field-group">
                                        <label for="bulkMinQty" class="field-label">Minimum Quantity <span class="required">*</span></label>
                                        <input type="number" id="bulkMinQty" name="bulk_sell_min_qty"
                                               value="{{ old('bulk_sell_min_qty') }}"
                                               placeholder="e.g. 50" min="2" step="1" class="field-input"
                                               oninput="updateBulkPreview()">
                                        <span class="field-hint">Discount activates at this quantity</span>
                                    </div>
                                    <div class="field-group">
                                        <label for="bulkDiscount" class="field-label">Discount (%) <span class="required">*</span></label>
                                        <div class="field-suffix-wrap">
                                            <input type="number" id="bulkDiscount" name="bulk_sell_discount"
                                                   value="{{ old('bulk_sell_discount') }}"
                                                   placeholder="e.g. 10" min="1" max="99" step="0.1"
                                                   class="field-input with-suffix" oninput="updateBulkPreview()">
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
                            <label class="toggle-row" for="demoPromotionEnabled">
                                <div class="toggle-info">
                                    <span class="toggle-title">
                                        <i class="fas fa-tag"></i> Enable Promotion
                                    </span>
                                    <span class="toggle-desc">Set a promotional discount with an optional time period</span>
                                </div>
                                <div class="toggle-switch-wrap">
                                    <input type="checkbox" id="demoPromotionEnabled" name="promotion_enabled" value="1"
                                           onchange="togglePromoFields(this)" {{ old('promotion_enabled') ? 'checked' : '' }}>
                                    <span class="toggle-switch"></span>
                                </div>
                            </label>

                            <div id="promoFields" style="display:{{ old('promotion_enabled') ? 'block' : 'none' }};margin-top:16px;">
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
                                               value="{{ old('promotion_starts_at') }}" class="field-input">
                                        <span class="field-hint">Leave blank to start immediately</span>
                                    </div>
                                    <div class="field-group">
                                        <label for="promoEndsAt" class="field-label">
                                            End Date <span class="field-optional">(Optional)</span>
                                        </label>
                                        <input type="datetime-local" id="promoEndsAt" name="promotion_ends_at"
                                               value="{{ old('promotion_ends_at') }}" class="field-input">
                                        <span class="field-hint">Leave blank for no expiry</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                {{-- end #demoSellSection --}}

                {{-- Submit --}}
                <div class="form-submit-bar">
                    <a href="{{ route('artist.profile') }}" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-submit" id="demoSubmitBtn">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Upload Demo Artwork
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
document.addEventListener('DOMContentLoaded', function () {
    setupCounter('demoProductDesc', 'demoProductDescCounter', 2000);

    // Auto-fill product name placeholder from demo title
    const titleInput   = document.getElementById('demoTitle');
    const productInput = document.getElementById('productName');
    if (titleInput && productInput) {
        titleInput.addEventListener('input', function () {
            if (productInput.value.trim() === '') {
                productInput.placeholder = this.value || 'Leave blank to use demo title...';
            }
        });
    }

    // Initialise required state on page load (for old() repopulation)
    const checkbox = document.getElementById('alsoSellCheckbox');
    if (checkbox && checkbox.checked) {
        document.querySelectorAll('.sell-req').forEach(el => el.setAttribute('required', 'required'));
        document.getElementById('pricingPreviewCard').style.display = 'block';
    }
});

function toggleDemoSellFields() {
    const checkbox        = document.getElementById('alsoSellCheckbox');
    const section         = document.getElementById('demoSellSection');
    const pricingCard     = document.getElementById('pricingPreviewCard');
    const sellReqs        = document.querySelectorAll('.sell-req');

    if (checkbox.checked) {
        section.style.display     = 'flex';
        pricingCard.style.display = 'block';
        sellReqs.forEach(el => el.setAttribute('required', 'required'));
    } else {
        section.style.display     = 'none';
        pricingCard.style.display = 'none';
        sellReqs.forEach(el => el.removeAttribute('required'));
    }
}

function toggleDemoFreeShipping(checkbox) {
    const shippingInput = document.getElementById('demoShipping');
    if (checkbox.checked) {
        shippingInput.value    = '0';
        shippingInput.disabled = true;
    } else {
        shippingInput.disabled = false;
    }
    updatePricingPreview();
}
</script>
@endsection