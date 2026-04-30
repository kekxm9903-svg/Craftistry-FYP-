<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'artwork_sell_id',
        'buyer_id',
        'quantity',
        'last_ship_date',
        'description',
        'unit_price',
        'discounted_price',
        'status',
    ];

    protected $casts = [
        'last_ship_date'   => 'date',
        'unit_price'       => 'decimal:2',
        'discounted_price' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function artworkSell()
    {
        return $this->belongsTo(ArtworkSell::class, 'artwork_sell_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // ── Computed Attributes ──────────────────────────────────────────────────

    public function getTotalPriceAttribute(): float
    {
        return round($this->discounted_price * $this->quantity, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'Pending',
            'accepted' => 'Accepted',
            'refused'  => 'Refused',
            default    => 'Pending',
        };
    }

    public function getIsDiscountedAttribute(): bool
    {
        return $this->discounted_price < $this->unit_price;
    }
}