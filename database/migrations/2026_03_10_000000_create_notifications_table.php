<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type');           // e.g. new_membership, low_stock, payment_received
            $table->string('title');
            $table->text('message');
            $table->string('icon')->default('mdi-bell');
            $table->string('color')->default('info');  // success, info, warning, danger
            $table->string('link')->nullable();         // URL to navigate when clicked
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['is_read', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
