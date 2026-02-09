<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'payment_status',
        'refunded_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'duration_days' => 'integer',
        'previous_due_date' => 'datetime',
        'new_due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the membership associated with this payment
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
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
        return max(0, (float)$this->amount - $this->getTotalRefundedAmount());
    }

    // ================== STATUS MANAGEMENT ==================

    /**
     * Update payment status based on total refunds
     * Called after each refund operation
     * DO NOT modify membership details (membership will handle that)
     */
    public function updateRefundStatus(): void
    {
        $totalRefunded = $this->getTotalRefundedAmount();
        $totalAmount = (float)$this->amount;

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
