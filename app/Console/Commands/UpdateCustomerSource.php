<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PTSchedule;
use Illuminate\Support\Facades\DB;

class UpdateCustomerSource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pt:update-customer-source';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update customer_source field for existing PT schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating customer_source for existing PT schedules...');

        // Update records that have membership_id
        $membershipCount = DB::table('pt_schedules')
            ->whereNull('customer_source')
            ->whereNotNull('membership_id')
            ->update(['customer_source' => 'membership']);

        // Update records that have client_id
        $clientCount = DB::table('pt_schedules')
            ->whereNull('customer_source')
            ->whereNotNull('client_id')
            ->update(['customer_source' => 'client']);

        // Update records that are walk-ins (have customer_name but no client_id or membership_id)
        $walkinCount = DB::table('pt_schedules')
            ->whereNull('customer_source')
            ->whereNull('client_id')
            ->whereNull('membership_id')
            ->whereNotNull('customer_name')
            ->update(['customer_source' => 'walkin']);

        $this->info("Updated {$membershipCount} membership records");
        $this->info("Updated {$clientCount} client records");
        $this->info("Updated {$walkinCount} walk-in records");
        $this->info('Done!');

        return 0;
    }
}
