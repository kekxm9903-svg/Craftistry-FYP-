<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomOrderRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id', 'seller_id',
        'title', 'description', 'reference_image', 'buyer_price',
        'product_type',
        'status', 'seller_reason', 'counter_price', 'buyer_response',
        'order_id', 'stripe_session_id',
    ];

    protected $casts = [
        'buyer_price'   => 'decimal:2',
        'counter_price' => 'decimal:2',
    ];

    /* ── Relationships ─────────────────────────── */

    public function buyer()  { return $this->belongsTo(User::class, 'buyer_id'); }
    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
    public function order()  { return $this->belongsTo(Order::class); }

    /* ── State helpers ─────────────────────────── */

    public function isPending()   { return $this->status === 'pending'; }
    public function isAccepted()  { return $this->status === 'accepted'; }
    public function isRefused()   { return $this->status === 'refused'; }
    public function isCompleted() { return $this->status === 'completed'; }
    public function isCancelled() { return $this->status === 'cancelled'; }

    public function isDigital()  { return $this->product_type === 'digital'; }
    public function isPhysical() { return $this->product_type === 'physical'; }

    public function hasCounterPrice() { return ! is_null($this->counter_price); }

    /** Price the buyer ultimately pays */
    public function finalPrice(): float
    {
        return (float) ($this->counter_price ?? $this->buyer_price);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Awaiting Seller',
            'accepted'  => 'Pending Payment',
            'refused'   => $this->hasCounterPrice() ? 'Counter Offer' : 'Refused',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'   => 'orange',
            'accepted'  => 'blue',
            'refused'   => $this->hasCounterPrice() ? 'purple' : 'red',
            'completed' => 'green',
            'cancelled' => 'gray',
            default     => 'gray',
        };
    }
}