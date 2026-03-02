<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventorySupply extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_number',
        'product_name',
        'avatar',
        'category',
        'category_color',
        'unit_price',
        'stock_qty',
        'low_stock_threshold',
        'last_restocked',
    ];

    protected $casts = [
        'last_restocked' => 'datetime',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Get all transactions for this inventory item.
     */
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Get the stock status badge class and text
     */
    public function getStatusAttribute()
    {
        if ($this->stock_qty == 0) {
            return ['class' => 'badge-danger', 'text' => 'Out of Stock'];
        } elseif ($this->stock_qty < $this->low_stock_threshold) {
            return ['class' => 'badge-warning', 'text' => 'Low Stock'];
        } else {
            return ['class' => 'badge-success', 'text' => 'In Stock'];
        }
    }

    /**
     * Get inventory adjustments
     */
    public function adjustments()
    {
        return $this->hasMany(InventoryAdjustment::class);
    }
}