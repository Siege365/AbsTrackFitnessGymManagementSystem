<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateAttendanceCustomerType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:update-customer-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update customer_type field for existing attendance records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating customer_type for existing attendance records...');

        // Update records that have client_id (should be 'client', not 'walk-in')
        $clientCount = DB::table('attendances')
            ->whereNotNull('client_id')
            ->where('customer_type', '!=', 'client')
            ->update(['customer_type' => 'client']);

        // Update records that are walk-ins (have customer_name but no client_id)
        $walkinCount = DB::table('attendances')
            ->whereNull('client_id')
            ->whereNotNull('customer_name')
            ->where('customer_type', '!=', 'walk-in')
            ->update(['customer_type' => 'walk-in']);

        $this->info("Updated {$clientCount} client records");
        $this->info("Updated {$walkinCount} walk-in records");
        $this->info('Done!');

        return 0;
    }
}
