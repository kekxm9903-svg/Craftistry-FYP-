<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClassEvent extends Model
{
    use HasFactory;

    protected $table = 'class_events';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'poster_image',
        'is_paid',           // ← ADDED
        'price',             // ← ADDED
        'media_type',
        'platform',
        'location',
        'start_date',
        'end_date',
        'enrollment_deadline',
        'cancellation_deadline',
        'require_form',
        'enrollment_form_url',
        'max_participants',
        'duration_weeks',
        'start_time',
        'end_time',
        'duration_hours',
        'duration_minutes',
    ];

    protected $casts = [
        'start_date'             => 'date',
        'end_date'               => 'date',
        'enrollment_deadline'    => 'date',
        'cancellation_deadline'  => 'date',
        'start_time'             => 'datetime:H:i',
        'end_time'               => 'datetime:H:i',
        'is_paid'                => 'boolean',  // ← ADDED
        'price'                  => 'decimal:2', // ← ADDED
    ];

    // ================================================================
    // RELATIONSHIPS
    // ================================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // ================================================================
    // ACCESSORS
    // ================================================================

    public function getPosterUrlAttribute()
    {
        if ($this->poster_image) {
            return asset('storage/' . $this->poster_image);
        }
        return null;
    }

    public function getMediaLocationAttribute()
    {
        return $this->media_type === 'online' ? $this->platform : $this->location;
    }

    public function getFormattedDateRangeAttribute()
    {
        $start = Carbon::parse($this->start_date)->format('M d, Y');
        $end   = Carbon::parse($this->end_date)->format('M d, Y');

        if ($this->start_date == $this->end_date) {
            return $start;
        }

        return $start . ' - ' . $end;
    }

    public function getFormattedTimeRangeAttribute()
    {
        $start = Carbon::parse($this->start_time)->format('g:i A');
        $end   = Carbon::parse($this->end_time)->format('g:i A');

        return $start . ' - ' . $end;
    }

    public function getFormattedDateTimeAttribute()
    {
        return $this->formatted_date_range . ' • ' . $this->formatted_time_range;
    }

    public function getDurationTextAttribute()
    {
        $text = '';

        if ($this->duration_hours > 0) {
            $text .= $this->duration_hours . ' hr';
            if ($this->duration_hours > 1) $text .= 's';
        }

        if ($this->duration_minutes > 0) {
            if ($text) $text .= ' ';
            $text .= $this->duration_minutes . ' min';
            if ($this->duration_minutes > 1) $text .= 's';
        }

        return $text ?: '0 mins';
    }

    /**
     * Human-readable enrollment deadline e.g. "Mar 20, 2025"
     */
    public function getFormattedDeadlineAttribute()
    {
        if ($this->enrollment_deadline) {
            return Carbon::parse($this->enrollment_deadline)->format('M d, Y');
        }
        return null;
    }

    /**
     * True if artist requires a Google Form to enroll.
     */
    public function getRequiresFormAttribute()
    {
        return !empty($this->enrollment_form_url);
    }

    /**
     * True if enrollment is still open (no deadline set OR today is on/before deadline).
     */
    public function getIsEnrollmentOpenAttribute()
    {
        if (!$this->enrollment_deadline) {
            return true;
        }
        return now()->toDateString() <= $this->enrollment_deadline->toDateString();
    }

    /**
     * Days remaining until deadline. Negative = past deadline. Null = no deadline set.
     */
    public function getDaysUntilDeadlineAttribute()
    {
        if (!$this->enrollment_deadline) return null;
        return now()->startOfDay()->diffInDays(
            $this->enrollment_deadline->copy()->startOfDay(), false
        );
    }

    // ================================================================
    // COMPUTED BOOLEAN ATTRIBUTES
    // ================================================================

    public function getIsOngoingAttribute()
    {
        $today = now()->toDateString();
        return $this->start_date->toDateString() <= $today
            && $this->end_date->toDateString() >= $today;
    }

    public function getIsUpcomingAttribute()
    {
        return $this->start_date->toDateString() > now()->toDateString();
    }

    public function getIsPastAttribute()
    {
        return $this->end_date->toDateString() < now()->toDateString();
    }

    // ================================================================
    // SCOPES
    // ================================================================

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString())
                     ->orderBy('start_date', 'asc')
                     ->orderBy('start_time', 'asc');
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now()->toDateString())
                     ->orderBy('start_date', 'desc');
    }

    public function scopeMediaType($query, $type)
    {
        return $query->where('media_type', $type);
    }

    /**
     * Whether enrollment is full (max_participants reached).
     */
    public function getIsFullAttribute(): bool
    {
        if (!$this->max_participants) return false;
        return $this->bookings()->count() >= $this->max_participants;
    }

    /**
     * How many spots remain. null = unlimited.
     */
    public function getSpotsRemainingAttribute(): ?int
    {
        if (!$this->max_participants) return null;
        return max(0, $this->max_participants - $this->bookings()->count());
    }

    /**
     * Formatted cancellation deadline for display.
     */
    public function getFormattedCancellationDeadlineAttribute(): ?string
    {
        return $this->cancellation_deadline
            ? $this->cancellation_deadline->format('d M Y')
            : null;
    }

    /**
     * Whether cancellation is still allowed.
     */
    public function getIsCancellationOpenAttribute(): bool
    {
        if (!$this->cancellation_deadline) return true;
        return now()->toDateString() <= $this->cancellation_deadline->toDateString();
    }

    /**
     * Display price — "Free" or "RM 25.00"
     */
    public function getPriceDisplayAttribute(): string
    {
        if (!$this->is_paid) {
            return 'Free';
        }
        return 'RM ' . number_format($this->price, 2);
    }
}