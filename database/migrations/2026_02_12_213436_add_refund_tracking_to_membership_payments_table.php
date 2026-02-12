<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('membership_payments', function (Blueprint $table) {
            $table->boolean('is_refunded')->default(false)->after('processed_by');
            $table->enum('refund_status', ['none', 'partial', 'full'])->default('none')->after('is_refunded');
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('refund_status');
            $table->timestamp('refunded_at')->nullable()->after('refunded_amount');
            $table->text('refund_reason')->nullable()->after('refunded_at');
            $table->string('refunded_by')->nullable()->after('refund_reason');
            $table->string('previous_status')->nullable()->after('previous_due_date');
            $table->index('is_refunded');
            $table->index('refund_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_payments', function (Blueprint $table) {
            $table->dropIndex(['is_refunded']);
            $table->dropIndex(['refund_status']);
            $table->dropColumn([
                'is_refunded',
                'refund_status',
                'refunded_amount',
                'refunded_at',
                'refund_reason',
                'refunded_by',
                'previous_status'
            ]);
        });
    }
};