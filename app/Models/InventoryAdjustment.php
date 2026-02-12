<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_supply_id',
        'product_name',
        'product_number',
        'adjustment_type',
        'quantity_before',
        'quantity_adjusted',
        'quantity_after',
        'unit_cost',
        'total_value',
        'adjustable_id',
        'adjustable_type',
        'reference_number',
        'reason',
        'adjusted_by',
        'approved_by',
        'metadata',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the parent adjustable model
     */
    public function adjustable()
    {
        return $this->morphTo();
    }

    /**
     * Get the inventory item
     */
    public function inventorySupply()
    {
        return $this->belongsTo(InventorySupply::class);
    }

    /**
     * Scope for refund adjustments
     */
    public function scopeRefunds($query)
    {
        return $query->where('adjustment_type', 'refund');
    }

    /**
     * Scope for recent adjustments
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted total value
     */
    public function getFormattedTotalValueAttribute()
    {
        return '₱' . number_format($this->total_value, 2);
    }
}