<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pt_schedules', function (Blueprint $table) {
            $table->string('receipt_number')->nullable()->after('payment_type');
            $table->string('plan_key')->nullable()->after('receipt_number');
            $table->string('plan_name')->nullable()->after('plan_key');
            $table->unsignedInteger('plan_duration_days')->nullable()->after('plan_name');
            $table->decimal('amount', 10, 2)->nullable()->after('plan_duration_days');
            $table->decimal('paid_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('return_amount', 10, 2)->nullable()->after('paid_amount');
            $table->string('processed_by')->nullable()->after('return_amount');
            $table->boolean('is_refunded')->default(false)->after('processed_by');
            $table->string('refund_status')->nullable()->after('is_refunded');
            $table->decimal('refunded_amount', 10, 2)->nullable()->after('refund_status');
            $table->text('refund_reason')->nullable()->after('refunded_amount');
            $table->string('refunded_by')->nullable()->after('refund_reason');
            $table->timestamp('refunded_at')->nullable()->after('refunded_by');

            $table->index('receipt_number');
            $table->index('is_refunded');
        });

        $schedules = DB::table('pt_schedules')
            ->select('id', 'created_at')
            ->orderBy('id')
            ->get();

        foreach ($schedules as $schedule) {
            $dateStamp = $schedule->created_at
                ? Carbon::parse($schedule->created_at)->format('Ymd')
                : now()->format('Ymd');

            DB::table('pt_schedules')
                ->where('id', $schedule->id)
                ->update([
                    'receipt_number' => sprintf('PT-%s-%05d', $dateStamp, $schedule->id),
                    'plan_key' => 'personal_training',
                    'plan_name' => 'Personal Training',
                    'return_amount' => 0,
                    'processed_by' => 'Admin',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pt_schedules', function (Blueprint $table) {
            $table->dropIndex(['receipt_number']);
            $table->dropIndex(['is_refunded']);
            $table->dropColumn([
                'receipt_number',
                'plan_key',
                'plan_name',
                'plan_duration_days',
                'amount',
                'paid_amount',
                'return_amount',
                'processed_by',
                'is_refunded',
                'refund_status',
                'refunded_amount',
                'refund_reason',
                'refunded_by',
                'refunded_at',
            ]);
        });
    }
};