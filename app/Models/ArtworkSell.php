<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtworkSell extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'product_name',
        'product_description',
        'product_price',
        'shipping_fee',
        'image_path',
        'extra_images',
        'artwork_type',
        'product_category',
        'material',
        'height',
        'width',
        'depth',
        'unit',
        'status',
        'available_stock',
        'is_cross_posted',
        'cross_posted_from_id',
        'bulk_sell_enabled',
        'bulk_sell_min_qty',
        'bulk_sell_discount',
        'promotion_enabled',
        'promotion_discount',
        'promotion_starts_at',
        'promotion_ends_at',
    ];

    protected $casts = [
        'product_price'       => 'decimal:2',
        'shipping_fee'        => 'decimal:2',
        'height'              => 'decimal:2',
        'width'               => 'decimal:2',
        'depth'               => 'decimal:2',
        'available_stock'     => 'integer',
        'is_cross_posted'     => 'boolean',
        'bulk_sell_enabled'   => 'boolean',
        'promotion_enabled'   => 'boolean',
        'extra_images'        => 'array',
        'promotion_starts_at' => 'datetime',
        'promotion_ends_at'   => 'datetime',
    ];

    protected $appends = [
        'image_url',
        'formatted_price',
        'status_label',
        'is_on_promotion',
        'promotion_price',
        'formatted_promotion_price',
        'effective_price',
    ];

    // ── Relationships ──────────────────────────

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function crossPostedFrom()
    {
        return $this->belongsTo(DemoArtwork::class, 'cross_posted_from_id');
    }

    // ── Accessors ──────────────────────────────

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getFormattedPriceAttribute()
    {
        return 'RM ' . number_format((float) $this->attributes['product_price'], 2);
    }

    public function getStatusLabelAttribute()
    {
        return $this->status === 'available' ? 'Available' : 'Sold Out';
    }

    public function getIsOnPromotionAttribute(): bool
    {
        $enabled  = $this->attributes['promotion_enabled']  ?? null;
        $discount = $this->attributes['promotion_discount'] ?? null;
        $price    = $this->attributes['product_price']      ?? null;

        if (!$enabled || !$discount || !$price) return false;
        if ((float)$price <= 0 || (float)$discount <= 0) return false;

        $today    = date('Y-m-d');
        $startsAt = $this->attributes['promotion_starts_at'] ?? null;
        $endsAt   = $this->attributes['promotion_ends_at']   ?? null;

        if ($startsAt) {
            $startDate = substr($startsAt, 0, 10);
            if ($today < $startDate) return false;
        }

        if ($endsAt) {
            $endDate = substr($endsAt, 0, 10);
            if ($today > $endDate) return false;
        }

        return true;
    }

    public function getPromotionPriceAttribute(): ?float
    {
        $enabled  = $this->attributes['promotion_enabled']  ?? null;
        $discount = $this->attributes['promotion_discount'] ?? null;
        $price    = $this->attributes['product_price']      ?? null;

        if (!$enabled || !$discount || !$price) return null;
        if ((float)$price <= 0 || (float)$discount <= 0) return null;

        $today    = date('Y-m-d');
        $startsAt = $this->attributes['promotion_starts_at'] ?? null;
        $endsAt   = $this->attributes['promotion_ends_at']   ?? null;

        if ($startsAt && $today < substr($startsAt, 0, 10)) return null;
        if ($endsAt   && $today > substr($endsAt,   0, 10)) return null;

        return round((float)$price * (1 - (float)$discount / 100), 2);
    }

    public function getFormattedPromotionPriceAttribute(): ?string
    {
        $price = $this->promotion_price;
        return $price !== null ? 'RM ' . number_format($price, 2) : null;
    }

    /**
     * Returns the price the buyer actually pays:
     * promotion price if active, otherwise the regular product_price.
     */
    public function getEffectivePriceAttribute(): float
    {
        $promotionPrice = $this->promotion_price;
        if ($promotionPrice !== null && $promotionPrice > 0) {
            return (float) $promotionPrice;
        }
        return (float) ($this->attributes['product_price'] ?? 0);
    }

    /**
     * Compute the per-unit price for a given quantity.
     * Applies promo discount first (via effective_price),
     * then bulk discount on top if qty meets the threshold.
     */
    public function resolveUnitPrice(int $qty = 1): float
    {
        $unit = $this->effective_price;

        if (
            $this->bulk_sell_enabled &&
            $this->bulk_sell_min_qty > 0 &&
            $qty >= $this->bulk_sell_min_qty &&
            $this->bulk_sell_discount > 0
        ) {
            $unit = round($unit * (1 - $this->bulk_sell_discount / 100), 2);
        }

        return (float) $unit;
    }

    // ── Methods ────────────────────────────────

    public function isCrossListed(): bool
    {
        return $this->is_cross_posted === true;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && ($this->attributes['available_stock'] ?? 0) > 0;
    }

    public function isSoldOut(): bool
    {
        return $this->status === 'sold_out' || ($this->attributes['available_stock'] ?? 0) <= 0;
    }

    public function markAsSoldOut(): void
    {
        $this->status = 'sold_out';
        $this->save();
    }

    public function markAsAvailable(): void
    {
        $this->status = 'available';
        $this->save();
    }

    /**
     * Deduct stock after a successful order.
     * Auto-marks as sold_out when stock reaches 0.
     */
    public function deductStock(int $qty): void
    {
        $newStock = max(0, ($this->attributes['available_stock'] ?? 0) - $qty);
        $this->update([
            'available_stock' => $newStock,
            'status'          => $newStock === 0 ? 'sold_out' : $this->status,
        ]);
    }
}