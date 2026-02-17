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
        // 1. Add student fields to memberships table (if not already present)
        Schema::table('memberships', function (Blueprint $table) {
            if (!Schema::hasColumn('memberships', 'is_student')) {
                $table->boolean('is_student')->default(false)->after('contact');
            }
            if (!Schema::hasColumn('memberships', 'student_id')) {
                $table->string('student_id')->nullable()->after('is_student');
            }
        });

        // 2. plan_type columns are already TEXT in SQLite (enum maps to text).
        //    No column type change needed — new plan values work as-is.

        // 3. Add buddy fields to membership_payments table (if not already present)
        Schema::table('membership_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('membership_payments', 'buddy_member_id')) {
                $table->unsignedBigInteger('buddy_member_id')->nullable()->after('membership_id');
            }
            if (!Schema::hasColumn('membership_payments', 'buddy_name')) {
                $table->string('buddy_name')->nullable()->after('buddy_member_id');
            }
            if (!Schema::hasColumn('membership_payments', 'buddy_contact')) {
                $table->string('buddy_contact')->nullable()->after('buddy_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_payments', function (Blueprint $table) {
            $table->dropColumn(['buddy_member_id', 'buddy_name', 'buddy_contact']);
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn(['is_student', 'student_id']);
        });
    }
};
