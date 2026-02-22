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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact')->unique();
            $table->string('avatar')->nullable();
            $table->integer('age')->nullable();
            $table->enum('sex', ['Male', 'Female', 'Other'])->nullable();
            $table->timestamps();
            
            // Index for faster searches
            $table->index('name');
            $table->index('contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
