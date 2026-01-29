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
        Schema::create('membership_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('membership_id')->constrained('memberships')->onDelete('cascade');
            $table->string('member_name');
            $table->enum('plan_type', ['Monthly', 'Session']);
            $table->enum('payment_type', ['new', 'renewal', 'extension']);
            $table->string('payment_method');
            $table->decimal('amount', 10, 2);
            $table->integer('duration_days');
            $table->date('previous_due_date')->nullable();
            $table->date('new_due_date');
            $table->text('notes')->nullable();
            $table->string('processed_by');
            $table->timestamps();

            $table->index('receipt_number');
            $table->index('membership_id');
            $table->index('payment_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_payments');
    }
};