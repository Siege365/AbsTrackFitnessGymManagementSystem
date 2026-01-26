<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function items()
    {
        return $this->hasMany(PaymentItem::class);
    }
}