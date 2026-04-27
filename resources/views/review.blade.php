@extends('layouts.app')

@section('title', 'Rate Your Order')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/review.css') }}">
@endsection

@section('content')
<main class="review-main">
<div class="review-inner">

    {{-- Page Header --}}
    <div class="page-header">
        <a href="{{ route('orders.index') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> My Orders
        </a>
        <div class="header-center">
            <h1>Rate Your Order</h1>
        </div>
        <div class="header-right"></div>
    </div>

    {{-- ── Order Summary Card ── --}}
    @php
        $artistName    = $order->artist->user->fullname ?? $order->artist->name ?? 'Unknown Artist';
        $artistInitial = strtoupper(substr($artistName, 0, 1));
        $artistAvatar  = $order->artist->user->profile_image ?? null;
        $productName   = $order->artwork->product_name ?? $order->title ?? 'Artwork Order';
        $productPrice  = $order->total ?? $order->price ?? 0;
        $orderImage    = $order->artwork->image_path ?? null;
    @endphp

    <div class="order-summary-card">
        <div class="osc-thumb">
            @if($orderImage)
                <img src="{{ asset('storage/' . $orderImage) }}" alt="{{ $productName }}">
            @else
                <div class="thumb-placeholder">
                    <i class="fas fa-palette"></i>
                </div>
            @endif
        </div>
        <div class="osc-info">
            <h4>{{ $productName }}</h4>
            <p><i class="fas fa-user-circle"></i> {{ $artistName }}</p>
            <p><i class="fas fa-receipt"></i> Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }} &middot; {{ $order->created_at->format('d M Y') }}</p>
            <p><i class="fas fa-tag"></i> RM {{ number_format($productPrice, 2) }}</p>
        </div>
        <div class="osc-badge">
            <span class="status-badge status-completed">
                <span class="dot"></span> Completed
            </span>
        </div>
    </div>

    <div class="review-layout">

        {{-- ── LEFT: Review Form ── --}}
        <div class="review-form-col">

            {{-- Normal POST form — no AJAX, redirects to reviewComplete on success --}}
            <form id="reviewForm" action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="order_id"        value="{{ $order->id }}">
                <input type="hidden" name="artist_id"       value="{{ $order->artist_id }}">
                <input type="hidden" name="artwork_sell_id" value="{{ $artworkSellId ?? '' }}">
                <input type="hidden" name="rating"          id="ratingInput" value="0">

                {{-- Star Rating --}}
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fas fa-star"></i> Overall Rating
                        <span class="required-dot">*</span>
                    </div>
                    <div class="star-row">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" class="star-btn" data-value="{{ $i }}" aria-label="{{ $i }} star">
                            <i class="fas fa-star star-icon"></i>
                        </button>
                        @endfor
                        <span class="star-label" id="starLabel">Tap to rate</span>
                    </div>
                    <p class="error-msg" id="ratingError">
                        <i class="fas fa-exclamation-circle"></i> Please select a star rating.
                    </p>
                </div>

                {{-- Review Text --}}
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fas fa-comment-alt"></i> Your Review
                        <span class="optional-tag">optional</span>
                    </div>
                    <textarea
                        class="review-textarea"
                        name="description"
                        id="descriptionField"
                        maxlength="1000"
                        placeholder="Share your experience — artwork quality, packaging, communication with the artist..."></textarea>
                    <div class="char-count"><span id="charCount">0</span> / 1000</div>
                </div>

                {{-- Photo Upload --}}
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fas fa-camera"></i> Add Photo
                        <span class="optional-tag">optional</span>
                    </div>
                    <div class="upload-area" id="photoArea">
                        <input type="file" name="image" id="photoInput" accept="image/jpeg,image/png,image/webp" class="upload-input">
                        <div class="upload-placeholder" id="photoPlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload a photo</p>
                            <small>JPG, PNG, WEBP — max 5MB</small>
                        </div>
                        <div class="upload-preview" id="photoPreview" style="display:none;">
                            <img id="photoPreviewImg" src="" alt="Preview">
                            <button type="button" class="preview-remove" id="removePhoto">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <p class="error-msg" id="photoError"></p>
                </div>

                {{-- Video Upload --}}
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fas fa-video"></i> Add Video
                        <span class="optional-tag">optional</span>
                    </div>
                    <div class="upload-area" id="videoArea">
                        <input type="file" name="video" id="videoInput" accept="video/mp4,video/webm,video/quicktime" class="upload-input">
                        <div class="upload-placeholder" id="videoPlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload a video</p>
                            <small>MP4, MOV, WEBM — max 50MB</small>
                        </div>
                        <div class="upload-preview" id="videoPreview" style="display:none;">
                            <video id="videoPreviewEl" muted preload="metadata"></video>
                            <div class="video-overlay-icon"><i class="fas fa-play"></i></div>
                            <button type="button" class="preview-remove" id="removeVideo">
                                <i class="fas fa-times"></i>
                            </button>
                            <span class="video-name" id="videoName"></span>
                        </div>
                    </div>
                    <p class="error-msg" id="videoError"></p>
                </div>

                {{-- Anonymous Toggle --}}
                <div class="form-card anon-card">
                    <label class="anon-row" id="anonLabel">
                        <input type="checkbox" name="is_anonymous" id="anonCheck" value="1">
                        <div class="toggle-track">
                            <div class="toggle-thumb"></div>
                        </div>
                        <div class="anon-text">
                            <strong>Post Anonymously</strong>
                            <span>Your name will be hidden from this review</span>
                        </div>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
                <a href="{{ route('orders.index') }}" class="btn-skip">Skip for now</a>

            </form>
        </div>

        {{-- ── RIGHT: Sidebar ── --}}
        <div class="review-sidebar-col">

            <div class="sidebar-card">
                <div class="sidebar-card-title">
                    <i class="fas fa-user-circle"></i> About the Artist
                </div>
                <div class="artist-row">
                    @if($artistAvatar)
                        <img src="{{ asset('storage/' . $artistAvatar) }}" class="artist-avatar-img" alt="{{ $artistName }}">
                    @else
                        <div class="artist-avatar-placeholder">{{ $artistInitial }}</div>
                    @endif
                    <div>
                        <strong>{{ $artistName }}</strong>
                        <span>Craftistry Artist</span>
                    </div>
                </div>
            </div>

            <div class="sidebar-card">
                <div class="sidebar-card-title">
                    <i class="fas fa-receipt"></i> Order Details
                </div>
                <ul class="order-details-list">
                    <li>
                        <span class="od-label">Order ID</span>
                        <span class="od-value">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </li>
                    <li>
                        <span class="od-label">Product</span>
                        <span class="od-value">{{ $productName }}</span>
                    </li>
                    <li>
                        <span class="od-label">Amount Paid</span>
                        <span class="od-value od-price">RM {{ number_format($productPrice, 2) }}</span>
                    </li>
                    <li>
                        <span class="od-label">Order Date</span>
                        <span class="od-value">{{ $order->created_at->format('d M Y') }}</span>
                    </li>
                    <li>
                        <span class="od-label">Status</span>
                        <span class="od-value">
                            <span class="status-badge status-completed">
                                <span class="dot"></span> Completed
                            </span>
                        </span>
                    </li>
                </ul>
            </div>

            <div class="sidebar-card">
                <div class="sidebar-card-title">
                    <i class="fas fa-lightbulb"></i> Review Tips
                </div>
                <ul class="tips-list">
                    <li><i class="fas fa-check-circle"></i> Be specific about quality, packaging & communication</li>
                    <li><i class="fas fa-check-circle"></i> Adding a photo makes your review more helpful</li>
                    <li><i class="fas fa-check-circle"></i> Rate based on your full experience</li>
                    <li><i class="fas fa-check-circle"></i> Keep it honest and respectful</li>
                </ul>
            </div>

        </div>
    </div>

</div>
</main>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Stars ──
    const starBtns    = document.querySelectorAll('.star-btn');
    const starIcons   = document.querySelectorAll('.star-icon');
    const starLabel   = document.getElementById('starLabel');
    const ratingInput = document.getElementById('ratingInput');
    const labels      = ['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'];
    let currentRating = 0;

    function paintStars(val) {
        starIcons.forEach((icon, i) => {
            icon.style.color = i < val ? '#f59e0b' : '#d1d5db';
        });
    }

    starBtns.forEach(btn => {
        btn.addEventListener('mouseenter', () => paintStars(+btn.dataset.value));
        btn.addEventListener('mouseleave', () => paintStars(currentRating));
        btn.addEventListener('click', () => {
            currentRating     = +btn.dataset.value;
            ratingInput.value = currentRating;
            paintStars(currentRating);
            starLabel.textContent = labels[currentRating];
            starLabel.className   = 'star-label rated';
            document.getElementById('ratingError').style.display = 'none';
        });
    });

    // ── Char count ──
    const desc = document.getElementById('descriptionField');
    desc.addEventListener('input', () => {
        document.getElementById('charCount').textContent = desc.value.length;
    });

    // ── Photo upload ──
    const photoInput       = document.getElementById('photoInput');
    const photoPreview     = document.getElementById('photoPreview');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const photoPreviewImg  = document.getElementById('photoPreviewImg');
    const photoError       = document.getElementById('photoError');

    photoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            photoError.textContent   = 'File exceeds 5MB limit.';
            photoError.style.display = 'flex';
            this.value = '';
            return;
        }
        photoError.style.display       = 'none';
        photoPreviewImg.src            = URL.createObjectURL(file);
        photoPlaceholder.style.display = 'none';
        photoPreview.style.display     = 'flex';
    });

    document.getElementById('removePhoto').addEventListener('click', function () {
        photoInput.value               = '';
        photoPreviewImg.src            = '';
        photoPreview.style.display     = 'none';
        photoPlaceholder.style.display = 'flex';
    });

    // ── Video upload ──
    const videoInput       = document.getElementById('videoInput');
    const videoPreview     = document.getElementById('videoPreview');
    const videoPlaceholder = document.getElementById('videoPlaceholder');
    const videoPreviewEl   = document.getElementById('videoPreviewEl');
    const videoName        = document.getElementById('videoName');
    const videoError       = document.getElementById('videoError');

    videoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 50 * 1024 * 1024) {
            videoError.textContent   = 'File exceeds 50MB limit.';
            videoError.style.display = 'flex';
            this.value = '';
            return;
        }
        videoError.style.display       = 'none';
        videoPreviewEl.src             = URL.createObjectURL(file);
        videoName.textContent          = file.name;
        videoPlaceholder.style.display = 'none';
        videoPreview.style.display     = 'flex';
    });

    document.getElementById('removeVideo').addEventListener('click', function () {
        videoInput.value               = '';
        videoPreviewEl.src             = '';
        videoPreview.style.display     = 'none';
        videoPlaceholder.style.display = 'flex';
    });

    // ── Anonymous toggle ──
    document.getElementById('anonCheck').addEventListener('change', function () {
        document.getElementById('anonLabel').classList.toggle('active', this.checked);
    });

    // ── Form submit — validate rating then allow normal POST ──
    document.getElementById('reviewForm').addEventListener('submit', function (e) {
        if (currentRating === 0) {
            e.preventDefault(); // only block if no rating selected
            const err         = document.getElementById('ratingError');
            err.style.display = 'flex';
            err.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        // rating is set — let the normal POST through, show loading state
        const submitBtn     = document.getElementById('submitBtn');
        submitBtn.disabled  = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    });

});
</script>
@endsection