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
        'seller_reason',
        'order_id',
        'stripe_session_id',
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

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // ── Status Helpers ───────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRefused(): bool
    {
        return $this->status === 'refused';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'  => 'Pending',
            'accepted' => 'Accepted',
            'refused'  => 'Refused',
            'paid'     => 'Paid',
            default    => 'Pending',
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'pending'  => 'orange',
            'accepted' => 'blue',
            'refused'  => 'red',
            'paid'     => 'green',
            default    => 'gray',
        };
    }

    // ── Computed Attributes ──────────────────────────────────────────────────

    public function getTotalPriceAttribute(): float
    {
        return round($this->discounted_price * $this->quantity, 2);
    }

    public function getIsDiscountedAttribute(): bool
    {
        return $this->discounted_price < $this->unit_price;
    }
}