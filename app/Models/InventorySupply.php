<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventorySupply extends Model
{
    use HasFactory;

    protected $table = 'inventory_supplies';

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
        'last_restocked' => 'date',
        'unit_price' => 'decimal:2',
    ];
}