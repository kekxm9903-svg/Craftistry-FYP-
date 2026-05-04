<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemoArtwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'title',
        'description',
        'image_path',
        'extra_images',
        'order',
        'display_order',
        'artwork_type',
        'material',
        'medium',
        'height',
        'width',
        'depth',
        'unit',
        'price',
        'is_cross_posted',
        'cross_posted_to_id',
    ];

    protected $casts = [
        'height'         => 'decimal:2',
        'width'          => 'decimal:2',
        'depth'          => 'decimal:2',
        'price'          => 'decimal:2',
        'is_cross_posted'=> 'boolean',
        'extra_images'   => 'array',
    ];

    protected $appends = ['image_url'];

    // Relationships

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    public function crossPostedTo()
    {
        return $this->belongsTo(ArtworkSell::class, 'cross_posted_to_id');
    }

    // Accessors

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getDisplayOrderAttribute()
    {
        return $this->attributes['order'] ?? $this->attributes['display_order'] ?? 0;
    }

    public function getMediumAttribute()
    {
        return $this->attributes['medium'] ?? $this->attributes['material'] ?? $this->attributes['artwork_type'] ?? null;
    }

    // Methods

    public function isCrossListed()
    {
        return $this->is_cross_posted === true;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}