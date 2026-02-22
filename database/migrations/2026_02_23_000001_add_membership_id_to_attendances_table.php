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
        Schema::table('attendances', function (Blueprint $table) {
            // Add membership_id as nullable foreign key
            $table->foreignId('membership_id')->nullable()->after('client_id')->constrained('memberships')->onDelete('cascade');
            
            // Index for faster queries
            $table->index('membership_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['membership_id']);
            $table->dropColumn('membership_id');
        });
    }
};
