<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix: Change pt_payments.client_id from NOT NULL with ON DELETE CASCADE
     * to NULLABLE with ON DELETE SET NULL. This prevents PT payment history
     * from being cascade-deleted when a client record is removed during refunds.
     */
    public function up(): void
    {
        Schema::table('pt_payments', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['client_id']);

            // Make client_id nullable
            $table->unsignedBigInteger('client_id')->nullable()->change();

            // Re-add foreign key with SET NULL instead of CASCADE
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pt_payments', function (Blueprint $table) {
            $table->dropForeign(['client_id']);

            $table->unsignedBigInteger('client_id')->nullable(false)->change();

            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade');
        });
    }
};
