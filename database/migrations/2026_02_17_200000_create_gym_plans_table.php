<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_plans', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['membership', 'personal_training']);
            $table->string('plan_name');
            $table->string('plan_key')->unique(); // e.g. 'Regular', 'Student', 'GymBuddy', 'PTSession'
            $table->decimal('price', 10, 2);
            $table->integer('duration_days'); // e.g. 30, 90, 180, 365, 1 for per-session
            $table->string('duration_label')->nullable(); // e.g. 'Monthly', '3 Months', 'Per Session'
            $table->string('badge_text')->nullable(); // e.g. 'Best Value', 'Student Only'
            $table->string('badge_color')->nullable(); // e.g. 'success', 'info', 'warning'
            $table->boolean('requires_student')->default(false);
            $table->boolean('requires_buddy')->default(false);
            $table->integer('buddy_count')->default(1); // how many people included (2 for gym buddy)
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_plans');
    }
};
