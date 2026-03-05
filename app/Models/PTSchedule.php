<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PTSchedule extends Model
{
    protected $table = 'pt_schedules';

    protected $fillable = [
        'client_id',
        'membership_id',
        'customer_source',
        'customer_name',
        'customer_age',
        'customer_sex',
        'customer_contact',
        'trainer_name',
        'scheduled_date',
        'scheduled_time',
        'payment_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    /**
     * Get the client that owns the PT schedule.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the membership that owns the PT schedule.
     */
    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Scope for today's sessions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', Carbon::today());
    }

    /**
     * Scope for upcoming sessions
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
                     ->whereDate('scheduled_date', '>=', Carbon::today());
    }

    /**
     * Scope for in-progress sessions
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for cancelled sessions
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for done sessions
     */
    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope for expired sessions
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Auto-expire overdue PT schedules.
     * Marks 'upcoming' schedules as 'expired' if scheduled date/time has passed
     * and no attendance was recorded for the customer on that date.
     */
    public static function expireOverdueSchedules(): int
    {
        $now = Carbon::now();

        return static::where('status', 'upcoming')
            ->where(function ($query) use ($now) {
                // Past dates (entire day passed)
                $query->whereDate('scheduled_date', '<', $now->toDateString())
                    // OR same day but time has passed
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('scheduled_date', $now->toDateString())
                          ->whereTime('scheduled_time', '<', $now->toTimeString());
                    });
            })
            ->update(['status' => 'cancelled']);
    }

    /**
     * Auto-complete overdue in-progress PT sessions.
     * Marks 'in_progress' sessions as 'done' if scheduled time has passed by 2 hours.
     * This ensures trainers have time to manually update status, but forgotten sessions
     * are automatically completed.
     */
    public static function completeOverdueInProgressSessions(): int
    {
        $now = Carbon::now();
        $graceHours = 2; // Hours after scheduled time before auto-completing

        return static::where('status', 'in_progress')
            ->where(function ($query) use ($now, $graceHours) {
                // Past dates (entire day passed + grace period)
                $query->whereDate('scheduled_date', '<', $now->copy()->subHours($graceHours)->toDateString())
                    // OR scheduled time has passed by grace period hours
                    ->orWhereRaw("TIMESTAMP(scheduled_date, scheduled_time) < ?", [
                        $now->copy()->subHours($graceHours)->format('Y-m-d H:i:s')
                    ]);
            })
            ->update(['status' => 'done']);
    }

    /**
     * Get the display name (client name, membership name, or walk-in customer name)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->customer_source === 'membership' && $this->membership) {
            return $this->membership->name;
        }
        if ($this->customer_source === 'client' && $this->client) {
            return $this->client->name;
        }
        return $this->customer_name ?? 'N/A';
    }

    /**
     * Get formatted time
     */
    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->scheduled_time)->format('g:i A');
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->scheduled_date)->format('d M Y');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'done' => 'badge-success',
            'in_progress' => 'badge-info',
            'upcoming' => 'badge-warning',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
