<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'url',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    // ── Type icon map ─────────────────────────────────────────────

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'order_status'   => 'fas fa-shopping-bag',
            'request_status' => 'fas fa-paint-brush',
            'class_reminder' => 'fas fa-graduation-cap',
            'new_order'      => 'fas fa-shopping-cart',
            'new_request'    => 'fas fa-envelope',
            'new_enrollment' => 'fas fa-user-plus',
            default          => 'fas fa-bell',
        };
    }

    public function getColorAttribute(): string
    {
        return match($this->type) {
            'order_status'   => '#667eea',
            'request_status' => '#764ba2',
            'class_reminder' => '#f97316',
            'new_order'      => '#22c55e',
            'new_request'    => '#3b82f6',
            'new_enrollment' => '#10b981',
            default          => '#6b7280',
        };
    }

    // ── Static factory helper ─────────────────────────────────────

    public static function send(int $userId, string $type, string $title, string $message, ?string $url = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'url'     => $url,
        ]);
    }
}