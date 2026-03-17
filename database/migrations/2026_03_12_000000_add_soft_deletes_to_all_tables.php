<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tables that need soft deletes added.
     */
    protected array $tables = [
        'users',
        'trainers',
        'customers',
        'clients',
        'memberships',
        'membership_payments',
        'payments',
        'payment_items',
        'pt_payments',
        'pt_schedules',
        'refund_logs',
        'gym_plans',
        'inventory_supplies',
        'inventory_transactions',
        'inventory_adjustments',
        'attendances',
        'notifications',
        'activity_logs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
