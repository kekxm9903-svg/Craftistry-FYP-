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
     * Artists this user has favorited
     * Usage: auth()->user()->favorites
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    /**
     * Get the actual favorited artist User models (many-to-many shortcut)
     * Usage: auth()->user()->favoriteArtists
     */
    public function favoriteArtists()
    {
        return $this->belongsToMany(
            User::class,        // related model
            'favorites',        // pivot table
            'user_id',          // foreign key on favorites (this user)
            'artist_id'         // foreign key on favorites (the artist)
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
     * Check if this user has favorited a specific artist
     * Usage: auth()->user()->hasFavorited($artistUser)
     */
    public function hasFavorited(User $artist): bool
    {
        return $this->favorites()->where('artist_id', $artist->id)->exists();
    }
}