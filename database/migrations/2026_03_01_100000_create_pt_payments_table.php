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
        Schema::create('pt_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('member_name');
            $table->string('plan_type');              // e.g. PTSession, PTMonthly
            $table->enum('payment_type', ['new', 'renewal', 'extension']);
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);
            $table->integer('duration_days')->nullable();
            $table->integer('sessions')->nullable();   // for session-based plans
            $table->date('previous_due_date')->nullable();
            $table->date('new_due_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('processed_by');

            // Refund tracking
            $table->boolean('is_refunded')->default(false);
            $table->enum('refund_status', ['none', 'partial', 'full'])->default('none');
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            $table->string('refunded_by')->nullable();
            $table->string('previous_status')->nullable();

            $table->timestamps();

            $table->index('receipt_number');
            $table->index('client_id');
            $table->index('payment_type');
            $table->index('is_refunded');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_payments');
    }
};
