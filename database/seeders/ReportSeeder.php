<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\MembershipPayment;
use App\Models\PTSchedule;
use App\Models\Attendance;
use App\Models\InventorySupply;
use App\Models\Client;
use App\Models\Membership;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds to populate data for reports testing.
     */
    public function run(): void
    {
        $this->command->info('Seeding report test data...');

        // Create inventory products first
        $this->seedInventoryProducts();
        
        // Create clients if needed
        $this->seedClients();

        // Seed payments and payment items (retail sales)
        $this->seedPayments();

        // Seed membership payments
        $this->seedMembershipPayments();

        // Seed PT schedules
        $this->seedPTSchedules();

        // Seed attendance records
        $this->seedAttendance();

        $this->command->info('Report test data seeded successfully!');
    }

    /**
     * Seed inventory products
     */
    private function seedInventoryProducts(): void
    {
        $products = [
            ['product_number' => 'PRD-001', 'product_name' => 'Nature Spring (500ml)', 'category' => 'Beverages', 'unit_price' => 25.00, 'stock_qty' => 100, 'low_stock_threshold' => 20],
            ['product_number' => 'PRD-002', 'product_name' => 'Coca Cola (330ml)', 'category' => 'Beverages', 'unit_price' => 35.00, 'stock_qty' => 80, 'low_stock_threshold' => 15],
            ['product_number' => 'PRD-003', 'product_name' => 'Whey Protein (1kg)', 'category' => 'Supplements', 'unit_price' => 1500.00, 'stock_qty' => 25, 'low_stock_threshold' => 5],
            ['product_number' => 'PRD-004', 'product_name' => 'Energy Bar', 'category' => 'Snacks', 'unit_price' => 75.00, 'stock_qty' => 50, 'low_stock_threshold' => 10],
            ['product_number' => 'PRD-005', 'product_name' => 'Gatorade (500ml)', 'category' => 'Beverages', 'unit_price' => 45.00, 'stock_qty' => 60, 'low_stock_threshold' => 12],
            ['product_number' => 'PRD-006', 'product_name' => 'Protein Shake', 'category' => 'Supplements', 'unit_price' => 120.00, 'stock_qty' => 40, 'low_stock_threshold' => 8],
            ['product_number' => 'PRD-007', 'product_name' => 'Gym Gloves', 'category' => 'Accessories', 'unit_price' => 350.00, 'stock_qty' => 15, 'low_stock_threshold' => 3],
            ['product_number' => 'PRD-008', 'product_name' => 'Resistance Band', 'category' => 'Accessories', 'unit_price' => 250.00, 'stock_qty' => 20, 'low_stock_threshold' => 5],
        ];

        foreach ($products as $product) {
            InventorySupply::updateOrCreate(
                ['product_number' => $product['product_number']],
                $product
            );
        }

        $this->command->info('  - Inventory products seeded');
    }

    /**
     * Seed clients
     */
    private function seedClients(): void
    {
        $clients = [
            ['name' => 'John Smith', 'age' => 28, 'contact' => '09171234567', 'plan_type' => 'Monthly'],
            ['name' => 'Maria Garcia', 'age' => 32, 'contact' => '09182345678', 'plan_type' => 'Monthly'],
            ['name' => 'James Wilson', 'age' => 25, 'contact' => '09193456789', 'plan_type' => 'Session'],
            ['name' => 'Sarah Johnson', 'age' => 29, 'contact' => '09204567890', 'plan_type' => 'Monthly'],
            ['name' => 'Michael Brown', 'age' => 35, 'contact' => '09215678901', 'plan_type' => 'Session'],
            ['name' => 'Emily Davis', 'age' => 27, 'contact' => '09226789012', 'plan_type' => 'Monthly'],
            ['name' => 'David Martinez', 'age' => 31, 'contact' => '09237890123', 'plan_type' => 'Session'],
            ['name' => 'Jennifer Lee', 'age' => 24, 'contact' => '09248901234', 'plan_type' => 'Monthly'],
            ['name' => 'Robert Taylor', 'age' => 38, 'contact' => '09259012345', 'plan_type' => 'Monthly'],
            ['name' => 'Lisa Anderson', 'age' => 26, 'contact' => '09260123456', 'plan_type' => 'Session'],
        ];

        foreach ($clients as $client) {
            Client::updateOrCreate(
                ['contact' => $client['contact']],
                array_merge($client, [
                    'start_date' => Carbon::now()->subMonths(rand(1, 6)),
                    'due_date' => Carbon::now()->addMonths(rand(1, 3)),
                ])
            );
        }

        $this->command->info('  - Clients seeded');
    }

    /**
     * Seed payments and payment items (retail transactions)
     */
    private function seedPayments(): void
    {
        $paymentMethods = ['Cash', 'Gcash', 'Paymaya', 'Card'];
        $products = InventorySupply::all();
        
        // Generate payments for the last 6 months
        for ($monthsAgo = 0; $monthsAgo < 6; $monthsAgo++) {
            $baseDate = Carbon::now()->subMonths($monthsAgo);
            $numTransactions = rand(20, 40); // Random transactions per month

            for ($i = 0; $i < $numTransactions; $i++) {
                $transactionDate = $baseDate->copy()
                    ->day(rand(1, min(28, $baseDate->daysInMonth)))
                    ->hour(rand(6, 21))
                    ->minute(rand(0, 59));

                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $numItems = rand(1, 4);
                $totalAmount = 0;
                $totalQty = 0;

                // Create payment first
                $payment = Payment::create([
                    'receipt_number' => 'RCP-' . strtoupper(Str::random(8)),
                    'customer_name' => 'Walk-in Customer',
                    'transaction_type' => 'retail',
                    'payment_method' => $paymentMethod,
                    'paid_amount' => 0,
                    'total_amount' => 0,
                    'return_amount' => 0,
                    'total_quantity' => 0,
                    'cashier_name' => 'Admin',
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ]);

                // Add items
                $selectedProducts = $products->random(min($numItems, $products->count()));
                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 5);
                    $subtotal = $product->unit_price * $qty;
                    $totalAmount += $subtotal;
                    $totalQty += $qty;

                    PaymentItem::create([
                        'payment_id' => $payment->id,
                        'inventory_supply_id' => $product->id,
                        'product_name' => $product->product_name,
                        'quantity' => $qty,
                        'unit_price' => $product->unit_price,
                        'subtotal' => $subtotal,
                        'created_at' => $transactionDate,
                        'updated_at' => $transactionDate,
                    ]);
                }

                // Update payment totals
                $payment->update([
                    'total_amount' => $totalAmount,
                    'paid_amount' => $totalAmount,
                    'total_quantity' => $totalQty,
                ]);
            }
        }

        $this->command->info('  - Payments and items seeded');
    }

    /**
     * Seed membership payments
     */
    private function seedMembershipPayments(): void
    {
        $paymentMethods = ['Cash', 'Gcash', 'Card', 'Bank Transfer'];
        $planTypes = [
            'Monthly' => ['amount' => 1500, 'days' => 30],
            'Session' => ['amount' => 100, 'days' => 1],
        ];

        $memberships = Membership::all();
        if ($memberships->isEmpty()) {
            // Create memberships if none exist
            $clients = Client::all();
            foreach ($clients as $client) {
                $memberships->push(Membership::create([
                    'name' => $client->name,
                    'age' => $client->age,
                    'contact' => $client->contact,
                    'plan_type' => $client->plan_type,
                    'start_date' => $client->start_date,
                    'due_date' => $client->due_date,
                ]));
            }
        }

        // Generate membership payments for the last 6 months
        for ($monthsAgo = 0; $monthsAgo < 6; $monthsAgo++) {
            $baseDate = Carbon::now()->subMonths($monthsAgo);
            $numPayments = rand(8, 15);

            for ($i = 0; $i < $numPayments; $i++) {
                $paymentDate = $baseDate->copy()
                    ->day(rand(1, min(28, $baseDate->daysInMonth)))
                    ->hour(rand(8, 18))
                    ->minute(rand(0, 59));

                $membership = $memberships->random();
                $planType = array_rand($planTypes);
                $planInfo = $planTypes[$planType];
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $paymentType = rand(0, 1) ? 'new' : 'renewal';

                $prevDueDate = $paymentDate->copy()->subDays(rand(1, 10));
                $newDueDate = $paymentDate->copy()->addDays($planInfo['days']);

                MembershipPayment::create([
                    'receipt_number' => 'MBR-' . strtoupper(Str::random(8)),
                    'membership_id' => $membership->id,
                    'member_name' => $membership->name,
                    'plan_type' => $planType,
                    'payment_type' => $paymentType,
                    'payment_method' => $paymentMethod,
                    'amount' => $planInfo['amount'],
                    'duration_days' => $planInfo['days'],
                    'previous_due_date' => $prevDueDate,
                    'new_due_date' => $newDueDate,
                    'notes' => $paymentType === 'new' ? 'New member registration' : 'Membership renewal',
                    'processed_by' => 'Admin',
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ]);
            }
        }

        $this->command->info('  - Membership payments seeded');
    }

    /**
     * Seed PT schedules
     */
    private function seedPTSchedules(): void
    {
        $trainers = [
            'Ronnie Coleman',
            'Justin Troy Rosalada',
            'Eulo Icon Sexcion',
            'David Laid',
            'Nicolas Deloso Torre III',
        ];
        $paymentTypes = ['Cash', 'Gcash', 'Card'];
        $statuses = ['done', 'done', 'done', 'cancelled', 'upcoming']; // Weighted towards 'done'
        $times = ['06:00', '07:00', '08:00', '09:00', '10:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

        $clients = Client::all();

        // Generate PT schedules for the last 6 months
        for ($monthsAgo = 0; $monthsAgo < 6; $monthsAgo++) {
            $baseDate = Carbon::now()->subMonths($monthsAgo);
            $numSessions = rand(25, 45);

            for ($i = 0; $i < $numSessions; $i++) {
                $sessionDate = $baseDate->copy()
                    ->day(rand(1, min(28, $baseDate->daysInMonth)));

                $client = $clients->random();
                $trainer = $trainers[array_rand($trainers)];
                $time = $times[array_rand($times)];
                $status = $statuses[array_rand($statuses)];
                $paymentType = $paymentTypes[array_rand($paymentTypes)];

                // Future dates should be 'upcoming'
                if ($sessionDate->isFuture()) {
                    $status = 'upcoming';
                }

                PTSchedule::create([
                    'client_id' => $client->id,
                    'trainer_name' => $trainer,
                    'scheduled_date' => $sessionDate,
                    'scheduled_time' => $time,
                    'payment_type' => $paymentType,
                    'status' => $status,
                    'notes' => null,
                    'created_at' => $sessionDate,
                    'updated_at' => $sessionDate,
                ]);
            }
        }

        $this->command->info('  - PT schedules seeded');
    }

    /**
     * Seed attendance records
     */
    private function seedAttendance(): void
    {
        $clients = Client::all();
        $statuses = ['active', 'active', 'active', 'due_soon', 'expired'];

        // Generate attendance for the last 30 days
        for ($daysAgo = 0; $daysAgo < 30; $daysAgo++) {
            $date = Carbon::now()->subDays($daysAgo);
            
            // Skip some random days (gym closed or low attendance)
            if (rand(0, 10) < 2) continue;

            // Number of check-ins varies by day
            $numCheckIns = rand(15, 40);
            $usedClients = [];

            for ($i = 0; $i < $numCheckIns; $i++) {
                $client = $clients->random();
                
                // Avoid duplicate check-ins for same client on same day
                if (in_array($client->id, $usedClients)) continue;
                $usedClients[] = $client->id;

                // Random time between 5 AM and 10 PM
                $hour = rand(5, 22);
                $minute = rand(0, 59);
                $timeIn = sprintf('%02d:%02d', $hour, $minute);
                
                // 70% chance of having a time out
                $timeOut = null;
                if (rand(0, 10) < 7) {
                    $outHour = min($hour + rand(1, 3), 23);
                    $outMinute = rand(0, 59);
                    $timeOut = sprintf('%02d:%02d', $outHour, $outMinute);
                }

                $status = $statuses[array_rand($statuses)];

                Attendance::create([
                    'client_id' => $client->id,
                    'date' => $date,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => $status,
                    'created_at' => $date->copy()->setTimeFromTimeString($timeIn),
                    'updated_at' => $date->copy()->setTimeFromTimeString($timeIn),
                ]);
            }
        }

        $this->command->info('  - Attendance records seeded');
    }
}
