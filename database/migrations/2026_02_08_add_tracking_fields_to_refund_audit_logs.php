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
        if (!Schema::hasTable('refund_audit_logs')) {
            return;
        }

        Schema::table('refund_audit_logs', function (Blueprint $table) {
            // Add columns if they don't exist
            if (!Schema::hasColumn('refund_audit_logs', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->after('refundable_id');
            }
            if (!Schema::hasColumn('refund_audit_logs', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('receipt_number');
            }
            if (!Schema::hasColumn('refund_audit_logs', 'product_name')) {
                $table->string('product_name')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('refund_audit_logs', 'quantity')) {
                $table->integer('quantity')->default(1)->after('product_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refund_audit_logs', function (Blueprint $table) {
            $table->dropColumn(['receipt_number', 'customer_name', 'product_name', 'quantity']);
        });
    }
};
