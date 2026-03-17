<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'contact',
        'avatar',
        'age',
        'sex',
    ];

    /**
     * Get all clients (PT services) for this customer
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get all memberships (gym access) for this customer
     */
    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get the active client (PT service) for this customer
     */
    public function activeClient()
    {
        return $this->hasOne(Client::class)
            ->whereDate('due_date', '>=', now())
            ->latest('due_date');
    }

    /**
     * Get the active membership (gym access) for this customer
     */
    public function activeMembership()
    {
        return $this->hasOne(Membership::class)
            ->whereDate('due_date', '>=', now())
            ->latest('due_date');
    }

    /**
     * Check if customer has an active client (PT service)
     */
    public function hasActiveClient(): bool
    {
        return $this->clients()
            ->whereDate('due_date', '>=', now())
            ->exists();
    }

    /**
     * Check if customer has an active membership
     */
    public function hasActiveMembership(): bool
    {
        return $this->memberships()
            ->whereDate('due_date', '>=', now())
            ->exists();
    }

    /**
     * Get display name with contact info
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->contact})";
    }
}
