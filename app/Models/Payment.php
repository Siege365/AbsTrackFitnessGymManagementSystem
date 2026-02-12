<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'customer_name',
        'transaction_type',
        'payment_method',
        'paid_amount',
        'total_amount',
        'return_amount',
        'total_quantity',
        'cashier_name',
        // Existing refund fields
        'refunded_at',
        'refund_reason',
        'refunded_by',
        // New refund fields (add after migration)
        'is_refunded',
        'refund_status',
        'refunded_amount',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'return_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'is_refunded' => 'boolean',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get payment items
     */
    public function items()
    {
        return $this->hasMany(PaymentItem::class);
    }

    /**
     * Get refund logs
     */
    public function refundLogs()
    {
        return $this->morphMany(RefundLog::class, 'refundable');
    }

    /**
     * Get inventory adjustments
     */
    public function inventoryAdjustments()
    {
        return $this->morphMany(InventoryAdjustment::class, 'adjustable');
    }

    /**
     * Scope for refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->whereNotNull('refunded_at');
    }

    /**
     * Scope for non-refunded payments
     */
    public function scopeNotRefunded($query)
    {
        return $query->whereNull('refunded_at');
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded()
    {
        // If already refunded
        if ($this->refunded_at) {
            return false;
        }

        return $this->getRemainingRefundableAmount() > 0;
    }

    /**
     * Get remaining refundable amount
     */
    public function getRemainingRefundableAmount()
    {
        return $this->total_amount - ($this->refunded_amount ?? 0);
    }

    /**
     * Check if fully refunded
     */
    public function isFullyRefunded()
    {
        return $this->refunded_at !== null;
    }

    /**
     * Get refund status label
     */
    public function getRefundStatusLabelAttribute()
    {
        if ($this->refunded_at) {
            return 'Refunded';
        }
        return 'Paid';
    }
}