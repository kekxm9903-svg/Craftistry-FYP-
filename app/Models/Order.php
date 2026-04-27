<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'total',
        'stripe_session_id',
        'payment_status',
        'payment_method',
        'artist_id',
        'title',
        'description',
        'price',
        'shipping_fee',
        'status',
        'tracking_number',
        'courier',
        'has_review',
    ];

    protected $casts = [
        'has_review' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Gets the first item's ArtworkSell for thumbnail use
    public function artwork()
    {
        return $this->hasOneThrough(
            ArtworkSell::class,
            OrderItem::class,
            'order_id',        // FK on order_items
            'id',              // FK on artwork_sells
            'id',              // local key on orders
            'artwork_sell_id'  // local key on order_items
        );
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeForArtist($query, ?int $artistId)
    {
        return $query->where('artist_id', $artistId);
    }

    public function scopeForBuyer($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')
                     ->where('payment_status', 'paid');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->payment_status === 'paid'
            && in_array($this->status, ['processing', 'preparing', 'shipped']);
    }

    public function getTrackingUrl(): ?string
    {
        if (!$this->tracking_number) return null;

        return match (strtolower($this->courier ?? '')) {
            'poslaju'     => 'https://www.poslaju.com.my/track-trace/?no=' . $this->tracking_number,
            'jnt', 'j&t'  => 'https://www.jtexpress.my/tracking/' . $this->tracking_number,
            'dhl'         => 'https://www.dhl.com/my-en/home/tracking.html?tracking-id=' . $this->tracking_number,
            'ninjavan'    => 'https://www.ninjavan.co/en-my/tracking?id=' . $this->tracking_number,
            'citylink'    => 'https://www.citylinkexpress.com/tracking/?trackingNo=' . $this->tracking_number,
            default       => 'https://www.tracking.my/?nums=' . $this->tracking_number,
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending_payment' => 'Pending Payment',
            'processing'      => 'Order Placed',
            'preparing'       => 'Preparing',
            'shipped'         => 'Shipped',
            'completed'       => 'Completed',
            'cancelled'       => 'Cancelled',
            default           => ucfirst($this->status ?? 'Unknown'),
        };
    }

    public function getSellerStatusLabel(): string
    {
        return match ($this->status) {
            'pending_payment' => 'Awaiting Payment',
            'processing'      => 'New Order',
            'preparing'       => 'Preparing',
            'shipped'         => 'Shipped',
            'completed'       => 'Completed',
            'cancelled'       => 'Cancelled',
            default           => ucfirst($this->status ?? 'Unknown'),
        };
    }

    public function getStatusClass(): string
    {
        return match ($this->status) {
            'pending_payment' => 'yellow',
            'processing'      => 'blue',
            'preparing'       => 'orange',
            'shipped'         => 'purple',
            'completed'       => 'green',
            'cancelled'       => 'red',
            default           => 'gray',
        };
    }

    public function getStatusChipClass(): string
    {
        return 'chip-' . $this->status;
    }
}