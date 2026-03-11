<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'module')) {
                $table->string('module')->default('general')->after('action');
            }
            if (!Schema::hasColumn('activity_logs', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('description');
            }
            if (!Schema::hasColumn('activity_logs', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('reference_number');
            }
            if (!Schema::hasColumn('activity_logs', 'metadata') && Schema::hasColumn('activity_logs', 'properties')) {
                $table->renameColumn('properties', 'metadata');
            } elseif (!Schema::hasColumn('activity_logs', 'metadata')) {
                $table->json('metadata')->nullable()->after('subject_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::table('activity_logs', function (Blueprint $table) {
            $columns = ['module', 'reference_number', 'customer_name'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('activity_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
