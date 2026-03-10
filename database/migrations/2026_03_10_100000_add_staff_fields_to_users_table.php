<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('cashier')->after('name');
            $table->string('contact')->nullable()->after('email');
            $table->string('address')->nullable()->after('contact');
            $table->string('avatar')->nullable()->after('address');
            $table->string('status')->default('active')->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'contact', 'address', 'avatar', 'status']);
        });
    }
};
