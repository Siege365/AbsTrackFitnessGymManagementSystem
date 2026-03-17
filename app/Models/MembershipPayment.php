<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipPayment extends Model
{
    use HasFactory, SoftDeletes;

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
        'is_refunded',
        'refund_status',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
        'refunded_by',
        'previous_status',
        'buddy_member_id',
        'buddy_name',
        'buddy_contact',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'duration_days' => 'integer',
        'previous_due_date' => 'datetime',
        'new_due_date' => 'datetime',
        'is_refunded' => 'boolean',
        'refunded_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
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