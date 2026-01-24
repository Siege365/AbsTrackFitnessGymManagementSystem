<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_supplies', function (Blueprint $table) {
            $table->id();
            $table->string('product_number')->unique();
            $table->string('product_name');
            $table->string('category');
            $table->decimal('unit_price', 10, 2);
            $table->integer('stock_qty')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->date('last_restocked')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_supplies');
    }
};