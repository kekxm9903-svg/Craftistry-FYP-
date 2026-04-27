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

    /**
     * Get the user that owns the artist profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the artwork types associated with this artist.
     */
    public function artworkTypes()
    {
        return $this->belongsToMany(ArtworkType::class, 'artist_artwork_type');
    }

    /**
     * Get all demo artworks for this artist.
     */
    public function demoArtworks()
    {
        return $this->hasMany(DemoArtwork::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all artworks for sale by this artist.
     */
    public function artworkSells()
    {
        return $this->hasMany(ArtworkSell::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the total sales revenue from sold artworks.
     * 
     * @return float
     */
    public function getTotalSalesRevenueAttribute()
    {
        return $this->artworkSells()
            ->where('status', 'sold')
            ->sum('product_price');
    }

    /**
     * Get the count of sold artworks.
     * 
     * @return int
     */
    public function getSoldArtworksCountAttribute()
    {
        return $this->artworkSells()
            ->where('status', 'sold')
            ->count();
    }

    /**
     * Get the count of available artworks for sale.
     * 
     * @return int
     */
    public function getAvailableArtworksCountAttribute()
    {
        return $this->artworkSells()
            ->where('status', 'available')
            ->count();
    }

    /**
     * Get the total count of all artworks (demo + sell).
     * 
     * @return int
     */
    public function getTotalArtworksCountAttribute()
    {
        return $this->demoArtworks()->count() + $this->artworkSells()->count();
    }
}