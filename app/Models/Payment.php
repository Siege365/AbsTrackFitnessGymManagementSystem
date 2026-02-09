<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'payment_status',
        'refunded_amount',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'return_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all payment items for this payment
     */
    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    /**
     * Get all refund audit logs for this payment (polymorphic)
     */
    public function refunds(): MorphMany
    {
        return $this->morphMany(RefundAuditLog::class, 'refundable');
    }

    /**
     * Get only completed/successful refunds
     */
    public function completedRefunds(): MorphMany
    {
        return $this->refunds()->where('status', 'completed');
    }

    // ================== STATUS HELPERS ==================

    /**
     * Check if payment is fully refunded
     */
    public function isFullyRefunded(): bool
    {
        return $this->payment_status === 'refunded';
    }

    /**
     * Check if payment is partially refunded
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->payment_status === 'partially_refunded';
    }

    // ================== CALCULATIONS ==================

    /**
     * Get total refunded amount (only completed refunds)
     */
    public function getTotalRefundedAmount(): float
    {
        return (float) $this->completedRefunds()->sum('refund_amount');
    }

    /**
     * Get remaining refundable amount for this payment
     */
    public function getRemainingRefundableAmount(): float
    {
        return max(0, (float)$this->total_amount - $this->getTotalRefundedAmount());
    }

    // ================== STATUS MANAGEMENT ==================

    /**
     * Update payment status based on total refunds
     * Called after each refund operation
     * Ensures payment_status is always accurate
     */
    public function updateRefundStatus(): void
    {
        $totalRefunded = $this->getTotalRefundedAmount();
        $totalAmount = (float)$this->total_amount;

        if ($totalRefunded >= $totalAmount) {
            // Full refund
            $this->payment_status = 'refunded';
        } elseif ($totalRefunded > 0) {
            // Partial refund
            $this->payment_status = 'partially_refunded';
        } else {
            // No refund
            $this->payment_status = 'completed';
        }

        // Update the refunded_amount column to match
        $this->refunded_amount = $totalRefunded;
        $this->save();
    }
}
