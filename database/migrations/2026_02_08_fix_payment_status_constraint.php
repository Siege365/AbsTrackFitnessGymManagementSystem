<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration is a placeholder - the payment_status constraint issue
     * is resolved in the RefundService by updating status and amount together.
     */
    public function up(): void
    {
        // No action needed - RefundService handles status updates directly
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed
    }
};
