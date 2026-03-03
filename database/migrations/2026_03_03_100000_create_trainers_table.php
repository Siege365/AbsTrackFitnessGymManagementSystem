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
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('specialization')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();

            $table->index('full_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
