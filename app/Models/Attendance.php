<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'client_id',
        'membership_id',
        'customer_name',
        'customer_contact',
        'customer_type',
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
     * Get the membership that owns the attendance record.
     */
    public function membership()
    {
        return $this->belongsTo(Membership::class);
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

    /**
     * Get the display name (membership, client, or walk-in customer name)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->membership) {
            return $this->membership->name;
        }
        if ($this->client) {
            return $this->client->name;
        }
        return $this->customer_name ?? 'Walk-in';
    }

    /**
     * Get the subscription type with priority: membership > client > walk-in
     */
    public function getSubscriptionTypeAttribute(): string
    {
        if ($this->membership) {
            return $this->membership->plan_type;
        }
        if ($this->client) {
            return $this->client->plan_type;
        }
        return 'Walk-in';
    }

    /**
     * Get the status with priority: membership > client
     */
    public function getActiveStatusAttribute(): ?string
    {
        if ($this->membership) {
            return $this->membership->status;
        }
        if ($this->client) {
            return $this->client->status;
        }
        return null;
    }

    /**
     * Get avatar with priority: membership > client
     */
    public function getActiveAvatarAttribute(): ?string
    {
        if ($this->membership && $this->membership->avatar) {
            return $this->membership->avatar;
        }
        if ($this->client && $this->client->avatar) {
            return $this->client->avatar;
        }
        return null;
    }

    /**
     * Get customer type display label
     */
    public function getCustomerTypeDisplayAttribute(): string
    {
        return match($this->customer_type) {
            'walk-in', 'session' => 'Walk-in',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'half-yearly' => 'Half-yearly',
            'annual' => 'Annual',
            default => ucfirst($this->customer_type ?? 'walk-in'),
        };
    }
}
