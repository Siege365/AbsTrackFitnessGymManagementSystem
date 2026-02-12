<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'refundable_id',
        'refundable_type',
        'receipt_number',
        'transaction_type',
        'original_amount',
        'refund_amount',
        'refund_type',
        'customer_name',
        'member_id',
        'refund_reason',
        'processed_by',
        'authorized_by',
        'status',
        'inventory_value_restored',
        'items_count',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'inventory_value_restored' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent refundable model (Payment or MembershipPayment)
     */
    public function refundable()
    {
        return $this->morphTo();
    }

    /**
     * Get associated inventory adjustments
     */
    public function inventoryAdjustments()
    {
        return $this->morphMany(InventoryAdjustment::class, 'adjustable');
    }

    /**
     * Scope for completed refunds
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending refunds
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for product refunds
     */
    public function scopeProducts($query)
    {
        return $query->where('transaction_type', 'product');
    }

    /**
     * Scope for membership refunds
     */
    public function scopeMemberships($query)
    {
        return $query->where('transaction_type', 'membership');
    }

    /**
     * Get formatted refund amount
     */
    public function getFormattedRefundAmountAttribute()
    {
        return '₱' . number_format($this->refund_amount, 2);
    }

    /**
     * Get formatted original amount
     */
    public function getFormattedOriginalAmountAttribute()
    {
        return '₱' . number_format($this->original_amount, 2);
    }

    /**
     * Check if refund is full
     */
    public function isFullRefund()
    {
        return $this->refund_type === 'full';
    }

    /**
     * Check if refund is partial
     */
    public function isPartialRefund()
    {
        return $this->refund_type === 'partial';
    }
}