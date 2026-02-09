<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refunds extends Model
{
    protected $table = 'refunds';

    protected $fillable = [
        'payment_id',
        'payment_item_id',
        'receipt_number',
        'customer_name',
        'product_name',
        'quantity',
        'unit_price',
        'refund_amount',
        'reason',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentItem()
    {
        return $this->belongsTo(PaymentItem::class, 'payment_item_id');
    }
}
