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
        Schema::create('pt_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('trainer_name');
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->string('payment_type')->default('Cash'); // Cash, Gcash, etc.
            $table->enum('status', ['upcoming', 'done', 'cancelled'])->default('upcoming');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('scheduled_date');
            $table->index('status');
            $table->index(['client_id', 'scheduled_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pt_schedules');
    }
};
