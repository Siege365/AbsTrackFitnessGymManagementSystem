<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'inventory_supply_id',
        'product_name',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function inventorySupply()
    {
        return $this->belongsTo(InventorySupply::class);
    }
}