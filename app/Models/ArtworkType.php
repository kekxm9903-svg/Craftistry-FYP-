<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtworkType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get the artists that specialize in this artwork type
     */
    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'artist_artwork_type');
    }
}