<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'artwork_id', 'quantity'];

    public function artwork()
    {
        return $this->belongsTo(ArtworkSell::class, 'artwork_id');
    }
}