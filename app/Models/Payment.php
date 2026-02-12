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
        'refunded_at',
        'refund_reason',
        'refunded_by',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'return_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PaymentItem::class);
    }
}