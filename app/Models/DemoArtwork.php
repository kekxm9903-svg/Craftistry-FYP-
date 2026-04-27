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
        'order',
        'display_order',  // Added for compatibility
        'artwork_type',
        'material',
        'medium',  // Added for compatibility
        'height',
        'width',
        'depth',
        'unit',
        'price',
        'is_cross_posted',
        'cross_posted_to_id',
    ];

    protected $casts = [
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'depth' => 'decimal:2',
        'price' => 'decimal:2',
        'is_cross_posted' => 'boolean',
    ];

    protected $appends = ['image_url'];

    // Relationships
    
    /**
     * Get the artist profile that owns the demo artwork
     */
    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * Get the user that owns the demo artwork
     * Uses artist_id as the foreign key to link to users table
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    /**
     * Get the artwork sell that this demo is cross-posted to
     */
    public function crossPostedTo()
    {
        return $this->belongsTo(ArtworkSell::class, 'cross_posted_to_id');
    }

    // Accessors
    
    /**
     * Get the full URL for the artwork image
     */
    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /**
     * Get the display order (alias for order)
     */
    public function getDisplayOrderAttribute()
    {
        return $this->attributes['order'] ?? $this->attributes['display_order'] ?? 0;
    }

    /**
     * Get the medium (alias for material/artwork_type)
     */
    public function getMediumAttribute()
    {
        return $this->attributes['medium'] ?? $this->attributes['material'] ?? $this->attributes['artwork_type'] ?? null;
    }

    // Methods
    
    /**
     * Check if the artwork is cross-listed to sell
     */
    public function isCrossListed()
    {
        return $this->is_cross_posted === true;
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}