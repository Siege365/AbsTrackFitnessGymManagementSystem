<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SQLite enum columns create CHECK constraints that block new plan_type values.
     * Recreate the tables without the CHECK constraints while preserving data.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // SQLite uses CHECK constraints for enums; MySQL does not — skip if MySQL
        if ($driver !== 'sqlite') {
            // On MySQL, plan_type is a regular VARCHAR — no CHECK constraint to remove.
            // If the column is an ENUM, convert it to VARCHAR so new plan types can be added.
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE memberships MODIFY COLUMN plan_type VARCHAR(50) NOT NULL DEFAULT 'Regular'");
                DB::statement("ALTER TABLE membership_payments MODIFY COLUMN plan_type VARCHAR(50) NOT NULL DEFAULT 'Regular'");
            }
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        // --- memberships table ---
        DB::statement('ALTER TABLE memberships RENAME TO _memberships_backup');

        DB::statement("
            CREATE TABLE memberships (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                avatar VARCHAR(255),
                plan_type VARCHAR(50) NOT NULL DEFAULT 'Regular',
                start_date DATE NOT NULL,
                due_date DATE NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'Active',
                contact VARCHAR(255) NOT NULL,
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                age INTEGER,
                is_student TINYINT(1) NOT NULL DEFAULT 0,
                student_id VARCHAR(255)
            )
        ");

        DB::statement('INSERT INTO memberships SELECT id, name, avatar, plan_type, start_date, due_date, status, contact, created_at, updated_at, age, is_student, student_id FROM _memberships_backup');
        DB::statement('DROP TABLE _memberships_backup');

        // --- membership_payments table ---
        DB::statement('ALTER TABLE membership_payments RENAME TO _membership_payments_backup');

        DB::statement("
            CREATE TABLE membership_payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                receipt_number VARCHAR(255) NOT NULL,
                membership_id INTEGER NOT NULL,
                member_name VARCHAR(255) NOT NULL,
                plan_type VARCHAR(50) NOT NULL DEFAULT 'Regular',
                payment_type VARCHAR(20) NOT NULL,
                payment_method VARCHAR(255) NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                duration_days INTEGER NOT NULL,
                previous_due_date DATE,
                new_due_date DATE NOT NULL,
                notes TEXT,
                processed_by VARCHAR(255) NOT NULL,
                created_at TIMESTAMP,
                updated_at TIMESTAMP,
                is_refunded TINYINT(1) NOT NULL DEFAULT 0,
                refund_status VARCHAR(255) NOT NULL DEFAULT 'none',
                refunded_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                refunded_at TIMESTAMP,
                refund_reason TEXT,
                refunded_by VARCHAR(255),
                previous_status VARCHAR(255),
                buddy_member_id INTEGER,
                buddy_name VARCHAR(255),
                buddy_contact VARCHAR(255),
                FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE CASCADE
            )
        ");

        DB::statement('INSERT INTO membership_payments SELECT id, receipt_number, membership_id, member_name, plan_type, payment_type, payment_method, amount, duration_days, previous_due_date, new_due_date, notes, processed_by, created_at, updated_at, is_refunded, refund_status, refunded_amount, refunded_at, refund_reason, refunded_by, previous_status, buddy_member_id, buddy_name, buddy_contact FROM _membership_payments_backup');
        DB::statement('DROP TABLE _membership_payments_backup');

        // Recreate indexes
        DB::statement('CREATE INDEX idx_mp_receipt ON membership_payments(receipt_number)');
        DB::statement('CREATE INDEX idx_mp_membership ON membership_payments(membership_id)');
        DB::statement('CREATE INDEX idx_mp_payment_type ON membership_payments(payment_type)');
        DB::statement('CREATE INDEX idx_mp_created ON membership_payments(created_at)');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // No rollback needed — removing CHECK constraints is safe
    }
};
