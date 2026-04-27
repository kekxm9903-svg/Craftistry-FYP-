@extends('layouts.app')

@section('title', 'Edit Your Review')

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
            <h1>Edit Your Review</h1>
        </div>
        <div class="header-right"></div>
    </div>

    {{-- Days remaining notice --}}
    @php
        $daysLeft = round(30 - $review->created_at->floatDiffInDays(now()), 1);
        $artistName   = $order->artist->user->fullname ?? 'Artist';
        $productName  = $order->artwork->product_name ?? $order->title ?? 'Artwork Order';
        $productPrice = $order->total ?? 0;
        $orderImage   = $order->artwork->image_path ?? null;
    @endphp

    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:13px;color:#92400e;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-clock"></i>
        You can edit or delete this review for <strong>{{ $daysLeft }} more day{{ $daysLeft !== 1 ? 's' : '' }}</strong>.
    </div>

    {{-- Order Summary Card --}}
    <div class="order-summary-card">
        <div class="osc-thumb">
            @if($orderImage)
                <img src="{{ asset('storage/' . $orderImage) }}" alt="{{ $productName }}">
            @else
                <div class="thumb-placeholder"><i class="fas fa-palette"></i></div>
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
        <div class="review-form-col">

            {{-- Edit Form --}}
            <form id="reviewForm" action="{{ route('reviews.update', $review->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="rating" id="ratingInput" value="{{ $review->rating }}">

                {{-- Star Rating --}}
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fas fa-star"></i> Overall Rating
                        <span class="required-dot">*</span>
                    </div>
                    <div class="star-row">
                        @for($i = 1; $i <= 5; $i++)
                        <button type="button" class="star-btn" data-value="{{ $i }}" aria-label="{{ $i }} star">
                            <i class="fas fa-star star-icon" style="color: {{ $i <= $review->rating ? '#f59e0b' : '#d1d5db' }}"></i>
                        </button>
                        @endfor
                        @php
                            $labels = ['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'];
                        @endphp
                        <span class="star-label rated" id="starLabel">{{ $labels[$review->rating] }}</span>
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
                    <textarea class="review-textarea" name="description" id="descriptionField"
                        maxlength="1000"
                        placeholder="Share your experience...">{{ old('description', $review->description) }}</textarea>
                    <div class="char-count"><span id="charCount">{{ strlen($review->description ?? '') }}</span> / 1000</div>
                </div>

                {{-- Current Photo --}}
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fas fa-camera"></i> Photo
                        <span class="optional-tag">optional</span>
                    </div>
                    @if($review->image_path)
                    <div style="margin-bottom:12px;">
                        <p style="font-size:12px;color:#6b7280;margin-bottom:8px;">Current photo:</p>
                        <div style="position:relative;display:inline-block;">
                            <img src="{{ asset('storage/' . $review->image_path) }}" style="width:120px;height:120px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb;">
                            <label style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;">
                                <input type="checkbox" name="remove_image" value="1" style="display:none;" onchange="this.parentElement.style.background=this.checked?'#dc2626':'rgba(220,38,38,0.85)'">
                                <i class="fas fa-times"></i>
                            </label>
                        </div>
                        <p style="font-size:11px;color:#9ca3af;margin-top:6px;">Check ✕ to remove, or upload a new one below.</p>
                    </div>
                    @endif
                    <div class="upload-area">
                        <input type="file" name="image" id="photoInput" accept="image/jpeg,image/png,image/webp" class="upload-input">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>{{ $review->image_path ? 'Upload a replacement photo' : 'Click to upload a photo' }}</p>
                            <small>JPG, PNG, WEBP — max 5MB</small>
                        </div>
                    </div>
                </div>

                {{-- Current Video --}}
                <div class="form-card">
                    <div class="form-card-title">
                        <i class="fas fa-video"></i> Video
                        <span class="optional-tag">optional</span>
                    </div>
                    @if($review->video_path)
                    <div style="margin-bottom:12px;">
                        <p style="font-size:12px;color:#6b7280;margin-bottom:8px;">Current video:</p>
                        <div style="position:relative;display:inline-block;">
                            <video style="width:180px;height:100px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb;" muted>
                                <source src="{{ asset('storage/' . $review->video_path) }}">
                            </video>
                            <label style="position:absolute;top:4px;right:4px;background:rgba(220,38,38,0.85);color:#fff;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;">
                                <input type="checkbox" name="remove_video" value="1" style="display:none;">
                                <i class="fas fa-times"></i>
                            </label>
                        </div>
                        <p style="font-size:11px;color:#9ca3af;margin-top:6px;">Check ✕ to remove, or upload a new one below.</p>
                    </div>
                    @endif
                    <div class="upload-area">
                        <input type="file" name="video" id="videoInput" accept="video/mp4,video/webm,video/quicktime" class="upload-input">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>{{ $review->video_path ? 'Upload a replacement video' : 'Click to upload a video' }}</p>
                            <small>MP4, MOV, WEBM — max 50MB</small>
                        </div>
                    </div>
                </div>

                {{-- Anonymous --}}
                <div class="form-card anon-card">
                    <label class="anon-row {{ $review->is_anonymous ? 'active' : '' }}" id="anonLabel">
                        <input type="checkbox" name="is_anonymous" id="anonCheck" value="1" {{ $review->is_anonymous ? 'checked' : '' }}>
                        <div class="toggle-track">
                            <div class="toggle-thumb"></div>
                        </div>
                        <div class="anon-text">
                            <strong>Post Anonymously</strong>
                            <span>Your name will be hidden from this review</span>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="{{ route('orders.index') }}" class="btn-skip">Cancel</a>
            </form>

            {{-- Delete Section --}}
            <div style="margin-top:24px;padding:20px;background:#fff5f5;border:1px solid #fecaca;border-radius:12px;">
                <h4 style="font-size:14px;font-weight:600;color:#dc2626;margin-bottom:6px;">
                    <i class="fas fa-trash"></i> Delete Review
                </h4>
                <p style="font-size:13px;color:#6b7280;margin-bottom:14px;">
                    Once deleted, you can submit a new review for this order.
                </p>
                <form action="{{ route('reviews.destroy', $review->id) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this review? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background:#dc2626;color:#fff;border:none;border-radius:8px;padding:9px 20px;font-size:13px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-trash"></i> Delete Review
                    </button>
                </form>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="review-sidebar-col">
            <div class="sidebar-card">
                <div class="sidebar-card-title"><i class="fas fa-info-circle"></i> Edit Policy</div>
                <ul class="tips-list">
                    <li><i class="fas fa-check-circle"></i> You can edit your review within <strong>30 days</strong> of submission.</li>
                    <li><i class="fas fa-check-circle"></i> You can also delete and re-submit a new review.</li>
                    <li><i class="fas fa-check-circle"></i> After 30 days, the review is locked permanently.</li>
                    <li><i class="fas fa-check-circle"></i> Keep your feedback honest and respectful.</li>
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
    const starBtns    = document.querySelectorAll('.star-btn');
    const starIcons   = document.querySelectorAll('.star-icon');
    const starLabel   = document.getElementById('starLabel');
    const ratingInput = document.getElementById('ratingInput');
    const labels      = ['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'];
    let currentRating = parseInt(ratingInput.value) || 0;

    function paintStars(val) {
        starIcons.forEach((icon, i) => {
            icon.style.color = i < val ? '#f59e0b' : '#d1d5db';
        });
    }

    paintStars(currentRating);

    starBtns.forEach(btn => {
        btn.addEventListener('mouseenter', () => paintStars(+btn.dataset.value));
        btn.addEventListener('mouseleave', () => paintStars(currentRating));
        btn.addEventListener('click', () => {
            currentRating         = +btn.dataset.value;
            ratingInput.value     = currentRating;
            paintStars(currentRating);
            starLabel.textContent = labels[currentRating];
            starLabel.className   = 'star-label rated';
            document.getElementById('ratingError').style.display = 'none';
        });
    });

    const desc = document.getElementById('descriptionField');
    desc.addEventListener('input', () => {
        document.getElementById('charCount').textContent = desc.value.length;
    });

    document.getElementById('anonCheck').addEventListener('change', function () {
        document.getElementById('anonLabel').classList.toggle('active', this.checked);
    });

    document.getElementById('reviewForm').addEventListener('submit', function (e) {
        if (currentRating === 0) {
            e.preventDefault();
            const err         = document.getElementById('ratingError');
            err.style.display = 'flex';
            err.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        const submitBtn     = document.getElementById('submitBtn');
        submitBtn.disabled  = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    });
});
</script>
@endsection