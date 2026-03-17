<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_id',
        'inventory_supply_id',
        'product_name',
        'quantity',
        'unit_price',
        'subtotal',
        // New refund tracking fields (add after migration)
        'refunded_quantity',
        'refunded_amount',
        'is_refunded',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'refunded_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'is_refunded' => 'boolean',
    ];

    /**
     * Get parent payment
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get inventory supply
     */
    public function inventorySupply()
    {
        return $this->belongsTo(InventorySupply::class);
    }

    /**
     * Get remaining refundable quantity
     */
    public function getRemainingRefundableQuantity()
    {
        return $this->quantity - ($this->refunded_quantity ?? 0);
    }

    /**
     * Get remaining refundable amount
     */
    public function getRemainingRefundableAmount()
    {
        return $this->subtotal - ($this->refunded_amount ?? 0);
    }

    /**
     * Check if item can be refunded
     */
    public function canBeRefunded()
    {
        return $this->getRemainingRefundableQuantity() > 0;
    }
}