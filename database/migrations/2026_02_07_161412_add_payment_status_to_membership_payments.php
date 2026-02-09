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
            if (!Schema::hasColumn('membership_payments', 'payment_status')) {
                $table->enum('payment_status', ['completed', 'partially_refunded', 'refunded'])->default('completed')->after('amount');
                $table->decimal('refunded_amount', 10, 2)->default(0)->after('payment_status');
                $table->index('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_payments', function (Blueprint $table) {
            if (Schema::hasColumn('membership_payments', 'payment_status')) {
                $table->dropIndex('membership_payments_payment_status_index');
                $table->dropColumn(['payment_status', 'refunded_amount']);
            }
        });
    }
};
