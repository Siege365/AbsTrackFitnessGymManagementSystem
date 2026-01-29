<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'membership_id',
        'member_name',
        'plan_type',
        'payment_type',
        'payment_method',
        'amount',
        'duration_days',
        'previous_due_date',
        'new_due_date',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'duration_days' => 'integer',
        'previous_due_date' => 'datetime',
        'new_due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the membership associated with this payment
     */
    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }
}