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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_supply_id');
            $table->string('product_name');
            $table->string('product_number')->nullable();
            $table->enum('adjustment_type', ['refund', 'manual', 'damage', 'expiry', 'other'])->default('refund');
            $table->integer('quantity_before');
            $table->integer('quantity_adjusted');
            $table->integer('quantity_after');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_value', 10, 2);
            $table->morphs('adjustable'); // adjustable_id, adjustable_type
            $table->string('reference_number')->nullable();
            $table->text('reason')->nullable();
            $table->string('adjusted_by');
            $table->string('approved_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('inventory_supply_id')
                ->references('id')
                ->on('inventory_supplies')
                ->onDelete('cascade');

            // Indexes
            $table->index('adjustment_type');
            $table->index('reference_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};