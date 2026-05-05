<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'fullname',
        'email',
        'phone',
        'password',
        'role',
        'is_artist',
        'profile_image',
        'artist_type',
        'artist_status',
        'address',
        'city',
        'state',
        'postcode',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_artist'         => 'boolean',
    ];

    // ── Computed Attributes ──────────────────────────────────────────────────

    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        return null;
    }

    /**
     * Passthrough: average seller rating via the artist profile.
     * Usage: $user->seller_rating
     */
    public function getSellerRatingAttribute(): float
    {
        return $this->artist?->seller_rating ?? 0.0;
    }

    /**
     * Passthrough: total review count via the artist profile.
     * Usage: $user->seller_review_count
     */
    public function getSellerReviewCountAttribute(): int
    {
        return $this->artist?->seller_review_count ?? 0;
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function artist()
    {
        return $this->hasOne(Artist::class, 'user_id');
    }

    public function demoArtworks()
    {
        return $this->hasManyThrough(
            DemoArtwork::class,
            Artist::class,
            'user_id',
            'artist_id',
            'id',
            'id'
        );
    }

    public function artworkSells()
    {
        return $this->hasManyThrough(
            ArtworkSell::class,
            Artist::class,
            'user_id',
            'artist_id',
            'id',
            'id'
        );
    }

    public function classEvents()
    {
        return $this->hasMany(ClassEvent::class, 'user_id');
    }

    /**
     * Raw Favorite records for this user (artist favourites).
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    /**
     * Get the actual favourited artist User models (many-to-many shortcut).
     */
    public function favoriteArtists()
    {
        return $this->belongsToMany(
            User::class,
            'favorites',
            'user_id',
            'artist_id'
        )->withTimestamps();
    }

    /**
     * Artworks this user has saved to favourites.
     * Pivot table: user_favorite_products (user_id, artwork_sell_id)
     */
    public function favoriteProducts()
    {
        return $this->belongsToMany(
            ArtworkSell::class,
            'user_favorite_products',
            'user_id',
            'artwork_sell_id'
        )->withTimestamps();
    }

    // ── Helper Methods ───────────────────────────────────────────────────────

    public function isArtist()
    {
        return $this->is_artist === true;
    }

    public function isBuyer()
    {
        return in_array($this->artist_type, ['buyer', 'both']);
    }

    /**
     * Check if this user has favourited a specific artist.
     */
    public function hasFavorited(User $artist): bool
    {
        return $this->favorites()->where('artist_id', $artist->id)->exists();
    }

    /**
     * Check if this user has favourited a specific product.
     */
    public function hasFavoritedProduct(ArtworkSell $product): bool
    {
        return $this->favoriteProducts()->where('artwork_sell_id', $product->id)->exists();
    }
}