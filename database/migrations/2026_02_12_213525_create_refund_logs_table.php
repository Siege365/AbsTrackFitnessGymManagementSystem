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
        Schema::create('refund_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('refundable'); // refundable_id, refundable_type
            $table->string('receipt_number');
            $table->enum('transaction_type', ['product', 'membership']);
            $table->decimal('original_amount', 10, 2);
            $table->decimal('refund_amount', 10, 2);
            $table->enum('refund_type', ['full', 'partial']);
            $table->string('customer_name');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->text('refund_reason')->nullable();
            $table->string('processed_by');
            $table->string('authorized_by')->nullable();
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('completed');
            $table->decimal('inventory_value_restored', 10, 2)->default(0);
            $table->integer('items_count')->default(0);
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('receipt_number');
            $table->index('transaction_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_logs');
    }
};