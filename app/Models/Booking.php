<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'class_event_id',
        'user_id',
        'booked_at',
        'payment_status',
        'stripe_session_id',
        'amount_paid',
    ];

    protected $casts = [
        'booked_at' => 'datetime',
    ];

    public function classEvent()
    {
        return $this->belongsTo(ClassEvent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->payment_status, ['paid', 'free']);
    }
}