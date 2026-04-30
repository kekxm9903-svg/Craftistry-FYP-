<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_image',
        'bio',
        'specialization',
        'allow_customization',
        'verification_status'
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function artworkTypes()
    {
        return $this->belongsToMany(ArtworkType::class, 'artist_artwork_type');
    }

    public function demoArtworks()
    {
        return $this->hasMany(DemoArtwork::class)->orderBy('created_at', 'desc');
    }

    public function artworkSells()
    {
        return $this->hasMany(ArtworkSell::class)->orderBy('created_at', 'desc');
    }

    /**
     * All reviews for this artist (across all their products)
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'artist_id');
    }

    // ── Computed Attributes ──────────────────────────────────────────────────

    /**
     * Average rating across all products.
     * Formula: average of each product's average rating.
     * e.g. product A=2.0, B=5.0, C=3.5 → (2.0+5.0+3.5)/3 = 3.5
     * Usage: $artist->seller_rating
     */
    public function getSellerRatingAttribute(): float
    {
        $perProduct = $this->reviews()
            ->selectRaw('artwork_sell_id, AVG(rating) as avg_rating')
            ->groupBy('artwork_sell_id')
            ->pluck('avg_rating');

        if ($perProduct->isEmpty()) {
            return 0.0;
        }

        return round($perProduct->avg(), 1);
    }

    /**
     * Total number of reviews across all products.
     * Usage: $artist->seller_review_count
     */
    public function getSellerReviewCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    /**
     * Number of unique products that have been reviewed.
     * Usage: $artist->reviewed_products_count
     */
    public function getReviewedProductsCountAttribute(): int
    {
        return $this->reviews()
            ->distinct('artwork_sell_id')
            ->count('artwork_sell_id');
    }

    /**
     * Get the total sales revenue from sold artworks.
     */
    public function getTotalSalesRevenueAttribute()
    {
        return $this->artworkSells()
            ->where('status', 'sold')
            ->sum('product_price');
    }

    /**
     * Get the count of sold artworks.
     */
    public function getSoldArtworksCountAttribute()
    {
        return $this->artworkSells()
            ->where('status', 'sold')
            ->count();
    }

    /**
     * Get the count of available artworks for sale.
     */
    public function getAvailableArtworksCountAttribute()
    {
        return $this->artworkSells()
            ->where('status', 'available')
            ->count();
    }

    /**
     * Get the total count of all artworks (demo + sell).
     */
    public function getTotalArtworksCountAttribute()
    {
        return $this->demoArtworks()->count() + $this->artworkSells()->count();
    }
}