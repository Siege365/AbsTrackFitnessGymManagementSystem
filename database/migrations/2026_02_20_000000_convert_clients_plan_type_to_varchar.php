<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The clients.plan_type column was created as ENUM('Monthly','Session')
     * but now stores gym_plan plan_key values (e.g. 'PTSession', etc.).
     * Convert it to VARCHAR so any plan_key value is accepted.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE clients MODIFY COLUMN plan_type VARCHAR(50) NOT NULL DEFAULT 'Monthly'");
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't enforce CHECK constraints strictly, but rebuild if needed
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::statement('ALTER TABLE clients RENAME TO _clients_backup');

            DB::statement("
                CREATE TABLE clients (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    age INTEGER,
                    sex VARCHAR(10),
                    avatar VARCHAR(255),
                    plan_type VARCHAR(50) NOT NULL DEFAULT 'Monthly',
                    start_date DATE NOT NULL,
                    due_date DATE NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'Active',
                    contact VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP,
                    updated_at TIMESTAMP
                )
            ");

            DB::statement('INSERT INTO clients SELECT id, name, age, sex, avatar, plan_type, start_date, due_date, status, contact, created_at, updated_at FROM _clients_backup');
            DB::statement('DROP TABLE _clients_backup');

            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Note: reverting to ENUM may fail if data contains values outside the enum list
            DB::statement("ALTER TABLE clients MODIFY COLUMN plan_type ENUM('Monthly','Session') NOT NULL DEFAULT 'Monthly'");
        }
    }
};
