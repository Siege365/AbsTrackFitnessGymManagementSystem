<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'expired' status to pt_schedules and support walk-in customers
     * by making client_id nullable and adding customer detail fields.
     */
    public function up(): void
    {
        // Add 'expired' to the status enum
        DB::statement("ALTER TABLE pt_schedules MODIFY COLUMN status ENUM('upcoming', 'in_progress', 'done', 'cancelled', 'expired') DEFAULT 'upcoming'");

        Schema::table('pt_schedules', function (Blueprint $table) {
            // Make client_id nullable for walk-in customers
            $table->unsignedBigInteger('client_id')->nullable()->change();

            // Walk-in customer detail fields (used when no client record exists)
            $table->string('customer_name')->nullable()->after('client_id');
            $table->integer('customer_age')->nullable()->after('customer_name');
            $table->string('customer_sex')->nullable()->after('customer_age');
            $table->string('customer_contact')->nullable()->after('customer_sex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pt_schedules MODIFY COLUMN status ENUM('upcoming', 'in_progress', 'done', 'cancelled') DEFAULT 'upcoming'");

        Schema::table('pt_schedules', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_age', 'customer_sex', 'customer_contact']);
        });
    }
};
