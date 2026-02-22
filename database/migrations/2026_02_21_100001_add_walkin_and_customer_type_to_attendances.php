<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Support walk-in customers and store detected customer type in attendances.
     * customer_type values: walk-in, session, monthly, quarterly, half-yearly, annual
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Make client_id nullable for walk-in customers
            $table->unsignedBigInteger('client_id')->nullable()->change();

            // Walk-in customer fields
            $table->string('customer_name')->nullable()->after('client_id');
            $table->string('customer_contact')->nullable()->after('customer_name');

            // Detected customer type for reporting
            $table->string('customer_type', 50)->default('walk-in')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_contact', 'customer_type']);
        });
    }
};
