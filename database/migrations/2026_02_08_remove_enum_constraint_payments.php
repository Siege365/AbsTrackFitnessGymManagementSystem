<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                CREATE TABLE payments_new (
                    id INTEGER PRIMARY KEY,
                    receipt_number TEXT UNIQUE NOT NULL,
                    customer_name TEXT NOT NULL,
                    transaction_type TEXT,
                    payment_method TEXT,
                    paid_amount DECIMAL(10, 2),
                    payment_status TEXT DEFAULT "completed" CHECK(payment_status IN ("completed", "partially_refunded", "refunded")),
                    refunded_amount DECIMAL(10, 2) DEFAULT 0,
                    total_amount DECIMAL(10, 2),
                    return_amount DECIMAL(10, 2) DEFAULT 0,
                    total_quantity INTEGER,
                    cashier_name TEXT,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ');

            // Copy data from old table
            DB::statement('
                INSERT INTO payments_new 
                SELECT id, receipt_number, customer_name, transaction_type, payment_method,
                       paid_amount, "completed", 0, total_amount, return_amount, 
                       total_quantity, cashier_name, created_at, updated_at
                FROM payments
            ');

            // Drop old table and rename new one
            DB::statement('DROP TABLE payments');
            DB::statement('ALTER TABLE payments_new RENAME TO payments');

            // Recreate indexes
            DB::statement('CREATE UNIQUE INDEX payments_receipt_number_unique ON payments(receipt_number)');
            DB::statement('CREATE INDEX payments_payment_status_index ON payments(payment_status)');
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
