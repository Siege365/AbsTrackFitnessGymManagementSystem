<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inventory_supply_id',
        'transaction_type',
        'quantity',
        'previous_stock',
        'new_stock',
        'notes',
        'performed_by',
    ];

    /**
     * Get the inventory item that owns the transaction.
     */
    public function inventorySupply()
    {
        return $this->belongsTo(InventorySupply::class);
    }
}