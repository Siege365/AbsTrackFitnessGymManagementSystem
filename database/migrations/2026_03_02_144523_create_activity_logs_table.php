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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name');
            $table->string('action');                       // created, updated, deleted, refunded, stock_in, stock_out, renewed, etc.
            $table->string('module');                       // membership, client, inventory, product_payment, membership_payment, pt_payment, pt_session, attendance
            $table->text('description');                    // Human-readable summary
            $table->string('reference_number')->nullable(); // receipt_number, product_number, etc.
            $table->string('customer_name')->nullable();    // The affected member/client/customer
            $table->nullableMorphs('subject');              // subject_id, subject_type — links to the actual model
            $table->json('metadata')->nullable();           // Extra context (amounts, plan types, etc.)
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('action');
            $table->index('module');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
