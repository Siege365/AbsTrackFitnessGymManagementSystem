<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->string('customer_name');
            $table->string('transaction_type');
            $table->string('payment_method');
            $table->decimal('paid_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('return_amount', 10, 2)->default(0);
            $table->integer('total_quantity');
            $table->string('cashier_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};