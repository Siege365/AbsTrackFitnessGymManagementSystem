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
        Schema::table('memberships', function (Blueprint $table) {
            // Add customer_id as nullable foreign key (backward compatible)
            $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->onDelete('cascade');
            
            // Index for faster queries
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};
