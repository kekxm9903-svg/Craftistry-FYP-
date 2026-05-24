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

    public function reviews()
    {
        return $this->hasMany(Review::class, 'artist_id', 'user_id');
    }

    // ── Computed Attributes ──────────────────────────────────────────────────

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

    public function getSellerReviewCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function getReviewedProductsCountAttribute(): int
    {
        return $this->reviews()
            ->distinct('artwork_sell_id')
            ->count('artwork_sell_id');
    }

    public function getTotalSalesRevenueAttribute()
    {
        return $this->artworkSells()
            ->where('status', 'sold')
            ->sum('product_price');
    }

    public function getSoldArtworksCountAttribute()
    {
        return $this->artworkSells()
            ->where('status', 'sold')
            ->count();
    }

    public function getAvailableArtworksCountAttribute()
    {
        return $this->artworkSells()
            ->where('status', 'available')
            ->count();
    }

    public function getTotalArtworksCountAttribute()
    {
        return $this->demoArtworks()->count() + $this->artworkSells()->count();
    }
}