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
        'category',
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
     * Auto-generate product number before creating a new record
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->product_number) {
                $lastProduct = static::orderBy('id', 'desc')->first();
                $lastNumber = $lastProduct ? (int)substr($lastProduct->product_number, 4) : 0;
                $model->product_number = 'PRD-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }

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
}