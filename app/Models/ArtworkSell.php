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
        'artwork_type',
        'material',
        'height',
        'width',
        'depth',
        'unit',
        'status',
        'is_cross_posted',
        'cross_posted_from_id',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'shipping_fee'  => 'decimal:2',
        'height'        => 'decimal:2',
        'width'         => 'decimal:2',
        'depth'         => 'decimal:2',
        'is_cross_posted' => 'boolean',
    ];

    protected $appends = ['image_url', 'formatted_price', 'status_label'];

    // Relationships
    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function crossPostedFrom()
    {
        return $this->belongsTo(DemoArtwork::class, 'cross_posted_from_id');
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getFormattedPriceAttribute()
    {
        return 'RM ' . number_format($this->product_price, 2);
    }

    public function getStatusLabelAttribute()
    {
        return $this->status === 'available' ? 'Available' : 'Sold Out';
    }

    // Methods
    public function isCrossListed()
    {
        return $this->is_cross_posted === true;
    }

    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function isSoldOut()
    {
        return $this->status === 'sold_out';
    }

    public function markAsSoldOut()
    {
        $this->status = 'sold_out';
        $this->save();
    }

    public function markAsAvailable()
    {
        $this->status = 'available';
        $this->save();
    }
}