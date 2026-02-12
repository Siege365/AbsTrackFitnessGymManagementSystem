<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('inventory_adjustments')) {
            return;
        }

        if (!Schema::hasColumn('inventory_adjustments', 'inventory_supply_id')) {
            Schema::table('inventory_adjustments', function (Blueprint $table) {
                $table->unsignedBigInteger('inventory_supply_id')->nullable()->after('id');
            });

            // Attempt to add foreign key where supported (skip for sqlite)
            try {
                $driver = Schema::getConnection()->getDriverName();
                if ($driver !== 'sqlite') {
                    Schema::table('inventory_adjustments', function (Blueprint $table) {
                        $table->foreign('inventory_supply_id')
                              ->references('id')
                              ->on('inventory_supplies')
                              ->onDelete('cascade');
                    });
                }
            } catch (\Exception $e) {
                // If adding FK fails, ignore to avoid breaking migration on restrictive DBs
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('inventory_adjustments')) {
            return;
        }

        if (Schema::hasColumn('inventory_adjustments', 'inventory_supply_id')) {
            // Drop foreign key if exists (skip errors)
            try {
                $driver = Schema::getConnection()->getDriverName();
                if ($driver !== 'sqlite') {
                    Schema::table('inventory_adjustments', function (Blueprint $table) {
                        $table->dropForeign(['inventory_supply_id']);
                    });
                }
            } catch (\Exception $e) {
                // ignore
            }

            Schema::table('inventory_adjustments', function (Blueprint $table) {
                $table->dropColumn('inventory_supply_id');
            });
        }
    }
};
