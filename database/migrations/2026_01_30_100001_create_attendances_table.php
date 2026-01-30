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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->date('date');
            $table->time('time_in');
            $table->time('time_out')->nullable();
            $table->enum('status', ['active', 'expired', 'due_soon'])->default('active');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('date');
            $table->index('status');
            $table->index(['client_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
