<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            $table->boolean('is_refunded')->default(false)->after('total_price');
            $table->integer('refunded_quantity')->default(0)->after('is_refunded');
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('refunded_quantity');
            
            $table->index('is_refunded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            $table->dropIndex(['is_refunded']);
            $table->dropColumn(['is_refunded', 'refunded_quantity', 'refunded_amount']);
        });
    }
};