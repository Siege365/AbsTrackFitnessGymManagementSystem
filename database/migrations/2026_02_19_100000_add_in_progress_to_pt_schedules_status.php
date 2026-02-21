<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the enum column to include 'in_progress'
        DB::statement("ALTER TABLE pt_schedules MODIFY COLUMN status ENUM('upcoming', 'in_progress', 'done', 'cancelled') DEFAULT 'upcoming'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE pt_schedules MODIFY COLUMN status ENUM('upcoming', 'done', 'cancelled') DEFAULT 'upcoming'");
    }
};
