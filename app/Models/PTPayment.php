<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PTPayment extends Model
{
    use HasFactory;

    protected $table = 'pt_payments';

    protected $fillable = [
        'receipt_number',
        'client_id',
        'member_name',
        'plan_type',
        'payment_type',
        'payment_method',
        'amount',
        'duration_days',
        'sessions',
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
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'duration_days' => 'integer',
        'sessions' => 'integer',
        'previous_due_date' => 'datetime',
        'new_due_date' => 'datetime',
        'is_refunded' => 'boolean',
        'refunded_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client (PT member) associated with this payment
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
