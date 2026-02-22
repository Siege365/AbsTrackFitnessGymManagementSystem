<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Client;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;

class MigrateToCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:migrate
                            {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing clients and memberships to use the customers table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('Starting customer migration...');
        $this->newLine();

        DB::beginTransaction();

        try {
            // Step 1: Collect all unique (name, contact) pairs from both tables
            $this->info('📊 Step 1: Analyzing existing data...');
            
            $clientData = Client::select('name', 'contact', 'avatar', 'age', 'sex')
                ->get()
                ->map(fn($c) => [
                    'name' => $c->name,
                    'contact' => $c->contact,
                    'avatar' => $c->avatar,
                    'age' => $c->age ?? null,
                    'sex' => $c->sex ?? null,
                ]);
            
            $membershipData = Membership::select('name', 'contact', 'avatar', 'age', 'sex')
                ->get()
                ->map(fn($m) => [
                    'name' => $m->name,
                    'contact' => $m->contact,
                    'avatar' => $m->avatar,
                    'age' => $m->age ?? null,
                    'sex' => $m->sex ?? null,
                ]);

            // Merge and deduplicate by name+contact
            $allPeople = $clientData->concat($membershipData)
                ->groupBy('contact') // Group by contact (unique identifier)
                ->map(function ($group) {
                    // For duplicates, prefer the record with more data
                    return $group->sortByDesc(fn($item) => 
                        ($item['avatar'] ? 1 : 0) + 
                        ($item['age'] ? 1 : 0) + 
                        ($item['sex'] ? 1 : 0)
                    )->first();
                });

            $this->info("Found {$allPeople->count()} unique customers (by contact)");
            $this->info("Clients: " . Client::count());
            $this->info("Memberships: " . Membership::count());
            $this->newLine();

            // Step 2: Create customer records
            $this->info('📝 Step 2: Creating customer records...');
            $progressBar = $this->output->createProgressBar($allPeople->count());
            $progressBar->start();

            $createdCustomers = 0;
            $customerMap = []; // Map contact => customer_id

            foreach ($allPeople as $person) {
                if (!$dryRun) {
                    $customer = Customer::firstOrCreate(
                        ['contact' => $person['contact']],
                        [
                            'name' => $person['name'],
                            'avatar' => $person['avatar'],
                            'age' => $person['age'],
                            'sex' => $person['sex'],
                        ]
                    );
                    $customerMap[$person['contact']] = $customer->id;
                    $createdCustomers++;
                } else {
                    $createdCustomers++;
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);
            $this->info("✅ Created/found {$createdCustomers} customer records");
            $this->newLine();

            // Step 3: Link clients to customers
            $this->info('🔗 Step 3: Linking clients to customers...');
            $clientsUpdated = 0;

            if (!$dryRun) {
                $clients = Client::whereNull('customer_id')->get();
                $progressBar = $this->output->createProgressBar($clients->count());
                $progressBar->start();

                foreach ($clients as $client) {
                    if (isset($customerMap[$client->contact])) {
                        $client->customer_id = $customerMap[$client->contact];
                        $client->save();
                        $clientsUpdated++;
                    }
                    $progressBar->advance();
                }

                $progressBar->finish();
            } else {
                $clientsUpdated = Client::whereNull('customer_id')->count();
            }

            $this->newLine(2);
            $this->info("✅ Linked {$clientsUpdated} clients to customers");
            $this->newLine();

            // Step 4: Link memberships to customers
            $this->info('🔗 Step 4: Linking memberships to customers...');
            $membershipsUpdated = 0;

            if (!$dryRun) {
                $memberships = Membership::whereNull('customer_id')->get();
                $progressBar = $this->output->createProgressBar($memberships->count());
                $progressBar->start();

                foreach ($memberships as $membership) {
                    if (isset($customerMap[$membership->contact])) {
                        $membership->customer_id = $customerMap[$membership->contact];
                        $membership->save();
                        $membershipsUpdated++;
                    }
                    $progressBar->advance();
                }

                $progressBar->finish();
            } else {
                $membershipsUpdated = Membership::whereNull('customer_id')->count();
            }

            $this->newLine(2);
            $this->info("✅ Linked {$membershipsUpdated} memberships to customers");
            $this->newLine();

            // Summary
            $this->newLine();
            $this->info('═══════════════════════════════════════');
            $this->info('📋 MIGRATION SUMMARY');
            $this->info('═══════════════════════════════════════');
            $this->info("Total unique customers: {$createdCustomers}");
            $this->info("Clients linked: {$clientsUpdated}");
            $this->info("Memberships linked: {$membershipsUpdated}");
            $this->info('═══════════════════════════════════════');
            $this->newLine();

            if ($dryRun) {
                DB::rollBack();
                $this->warn('⚠️  DRY RUN - No changes were saved to database');
            } else {
                DB::commit();
                $this->info('✅ Migration completed successfully!');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
