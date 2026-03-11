<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('email', ['admin@abstrack.com', 'manager@abstrack.com'])
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        //
    }
};
