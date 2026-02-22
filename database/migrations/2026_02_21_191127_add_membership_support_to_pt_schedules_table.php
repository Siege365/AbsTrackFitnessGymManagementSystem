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
        Schema::table('pt_schedules', function (Blueprint $table) {
            // Add membership_id to support PT schedules for memberships
            $table->foreignId('membership_id')->nullable()->after('client_id')->constrained('memberships')->onDelete('cascade');
            
            // Add customer_source to track whether the schedule is for a client, membership, or walk-in
            $table->enum('customer_source', ['client', 'membership', 'walkin'])->nullable()->after('membership_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pt_schedules', function (Blueprint $table) {
            $table->dropForeign(['membership_id']);
            $table->dropColumn(['membership_id', 'customer_source']);
        });
    }
};
