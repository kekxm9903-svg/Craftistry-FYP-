<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'artwork_sell_id',
        'name',
        'quantity',
        'price',
        'variant',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function artwork()
    {
        return $this->belongsTo(ArtworkSell::class, 'artwork_sell_id');
    }
}