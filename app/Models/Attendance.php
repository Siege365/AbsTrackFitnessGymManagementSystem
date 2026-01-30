<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'client_id',
        'date',
        'time_in',
        'time_out',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime:H:i',
        'time_out' => 'datetime:H:i',
    ];

    /**
     * Get the client that owns the attendance record.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope for today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Get formatted time in
     */
    public function getFormattedTimeInAttribute()
    {
        return Carbon::parse($this->time_in)->format('g:i A');
    }

    /**
     * Get formatted time out
     */
    public function getFormattedTimeOutAttribute()
    {
        if (!$this->time_out) {
            return '-';
        }
        return Carbon::parse($this->time_out)->format('g:i A');
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->date)->format('d M Y');
    }

    /**
     * Get status badge class based on client's membership status
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'active' => 'badge-success',
            'due_soon' => 'badge-warning',
            'expired' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get status display text
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'active' => 'Active',
            'due_soon' => 'Due soon',
            'expired' => 'Expired',
            default => ucfirst($this->status),
        };
    }
}
