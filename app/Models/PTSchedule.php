<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PTSchedule extends Model
{
    protected $table = 'pt_schedules';

    protected $fillable = [
        'client_id',
        'trainer_name',
        'scheduled_date',
        'scheduled_time',
        'payment_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
    ];

    /**
     * Get the client that owns the PT schedule.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
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
            'upcoming' => 'badge-warning',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
