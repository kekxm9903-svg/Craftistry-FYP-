<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'artist_id',
        'artwork_sell_id',
        'rating',
        'description',
        'is_anonymous',
        'image_path',
        'video_path',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'rating'       => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    public function artworkSell()
    {
        return $this->belongsTo(ArtworkSell::class, 'artwork_sell_id');
    }
}