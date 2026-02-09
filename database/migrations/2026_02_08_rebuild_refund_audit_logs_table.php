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
        // Drop existing table if it exists to rebuild it properly
        if (Schema::hasTable('refund_audit_logs')) {
            Schema::drop('refund_audit_logs');
        }

        Schema::create('refund_audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship (works for both payments and membership_payments)
            $table->string('refundable_type'); // 'App\\Models\\Payment' or 'App\\Models\\MembershipPayment'
            $table->unsignedBigInteger('refundable_id');
            
            // Tracking fields for display
            $table->string('receipt_number')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('product_name')->nullable();
            $table->integer('quantity')->default(1);
            
            // Refund details
            $table->decimal('refund_amount', 10, 2);
            $table->string('refund_reason');
            $table->string('refund_method')->default('cash'); // cash, card_reversal, store_credit
            
            // Who processed the refund
            $table->string('refunded_by')->nullable(); // User name or ID who processed
            $table->string('authorized_by')->nullable(); // User name or ID who authorized
            
            // Additional tracking
            $table->text('notes')->nullable();
            $table->string('status')->default('completed'); // completed, pending, cancelled
            $table->decimal('previous_refunded_amount', 10, 2)->default(0); // Track cumulative
            
            $table->timestamps();
            
            // Indexes for query performance
            $table->index(['refundable_type', 'refundable_id']);
            $table->index('created_at');
            $table->index('refund_method');
            $table->index('status');
            $table->index('receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_audit_logs');
    }
};
