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
        Schema::table('membership_payments', function (Blueprint $table) {
            $table->timestamp('refunded_at')->nullable()->after('processed_by');
            $table->text('refund_reason')->nullable()->after('refunded_at');
            $table->string('refunded_by')->nullable()->after('refund_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_payments', function (Blueprint $table) {
            $table->dropColumn(['refunded_at', 'refund_reason', 'refunded_by']);
        });
    }
};