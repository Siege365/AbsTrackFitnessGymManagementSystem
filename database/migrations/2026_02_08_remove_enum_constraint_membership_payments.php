<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDefaultConnection();
        
        if ($driver === 'sqlite') {
            // SQLite: Recreate table without enum constraint
            DB::statement('
                CREATE TABLE membership_payments_new (
                    id INTEGER PRIMARY KEY,
                    receipt_number TEXT UNIQUE NOT NULL,
                    membership_id INTEGER,
                    member_name TEXT,
                    plan_type TEXT,
                    payment_type TEXT,
                    payment_method TEXT,
                    amount DECIMAL(10, 2),
                    duration_days INTEGER,
                    previous_due_date TIMESTAMP,
                    new_due_date TIMESTAMP,
                    notes TEXT,
                    processed_by TEXT,
                    payment_status TEXT DEFAULT "completed" CHECK(payment_status IN ("completed", "partially_refunded", "refunded")),
                    refunded_amount DECIMAL(10, 2) DEFAULT 0,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');

            // Copy data from old table
            DB::statement('
                INSERT INTO membership_payments_new 
                SELECT id, receipt_number, membership_id, member_name, plan_type, payment_type,
                       payment_method, amount, duration_days, previous_due_date, new_due_date,
                       notes, processed_by, "completed", 0, created_at, updated_at
                FROM membership_payments
            ');

            // Drop old table and rename new one
            DB::statement('DROP TABLE membership_payments');
            DB::statement('ALTER TABLE membership_payments_new RENAME TO membership_payments');

            // Recreate indexes
            DB::statement('CREATE UNIQUE INDEX membership_payments_receipt_number_unique ON membership_payments(receipt_number)');
            DB::statement('CREATE INDEX membership_payments_payment_status_index ON membership_payments(payment_status)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot easily revert this
    }
};
