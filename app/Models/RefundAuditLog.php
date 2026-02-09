<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RefundAuditLog extends Model
{
    protected $table = 'refund_audit_logs';

    protected $fillable = [
        'refundable_type',
        'refundable_id',
        'receipt_number',
        'customer_name',
        'product_name',
        'quantity',
        'refund_amount',
        'refund_reason',
        'refund_method',
        'refunded_by',
        'authorized_by',
        'notes',
        'status',
        'previous_refunded_amount',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'previous_refunded_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent refundable model (Payment or MembershipPayment)
     * Works polymorphically for both payment types
     */
    public function refundable(): MorphTo
    {
        return $this->morphTo();
    }
}
