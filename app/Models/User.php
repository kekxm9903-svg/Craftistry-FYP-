<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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
        'preferred_artwork_type',
        'preference_shown',
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
        'preference_shown'  => 'boolean',
    ];

    // ── Computed Attributes ──────────────────────────────────────────────────

    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        return null;
    }

    public function getSellerRatingAttribute(): float
    {
        return $this->artist?->seller_rating ?? 0.0;
    }

    public function getSellerReviewCountAttribute(): int
    {
        return $this->artist?->seller_review_count ?? 0;
    }

    public function shouldShowPreferenceModal(): bool
    {
        return !$this->preference_shown;
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

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    public function favoriteArtists()
    {
        return $this->belongsToMany(
            User::class,
            'favorites',
            'user_id',
            'artist_id'
        )->withTimestamps();
    }

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

    public function hasFavorited(User $artist): bool
    {
        return $this->favorites()->where('artist_id', $artist->id)->exists();
    }

    public function hasFavoritedProduct(ArtworkSell $product): bool
    {
        return $this->favoriteProducts()->where('artwork_sell_id', $product->id)->exists();
    }
}