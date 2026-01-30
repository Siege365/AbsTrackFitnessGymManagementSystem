<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PTSchedule;
use App\Models\Client;
use Carbon\Carbon;

class PTScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        
        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Please run ClientSeeder first.');
            return;
        }

        $trainers = [
            'Coach Mike',
            'Coach Sarah',
            'Coach John',
            'Coach Lisa',
            'Coach David'
        ];

        $paymentTypes = ['Cash', 'Gcash'];
        $statuses = ['upcoming', 'done', 'cancelled'];

        $schedules = [];
        $today = Carbon::today();

        // Create schedules for today (PT Sessions Today)
        for ($i = 0; $i < 5; $i++) {
            $schedules[] = [
                'client_id' => $clients->random()->id,
                'trainer_name' => $trainers[array_rand($trainers)],
                'scheduled_date' => $today->format('Y-m-d'),
                'scheduled_time' => sprintf('%02d:00:00', rand(8, 17)),
                'payment_type' => $paymentTypes[array_rand($paymentTypes)],
                'status' => $statuses[array_rand(['upcoming', 'done'])], // No cancelled for today
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Create upcoming schedules (future dates)
        for ($i = 0; $i < 10; $i++) {
            $futureDate = $today->copy()->addDays(rand(1, 14));
            $schedules[] = [
                'client_id' => $clients->random()->id,
                'trainer_name' => $trainers[array_rand($trainers)],
                'scheduled_date' => $futureDate->format('Y-m-d'),
                'scheduled_time' => sprintf('%02d:00:00', rand(8, 17)),
                'payment_type' => $paymentTypes[array_rand($paymentTypes)],
                'status' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Create past schedules (done/cancelled)
        for ($i = 0; $i < 15; $i++) {
            $pastDate = $today->copy()->subDays(rand(1, 30));
            $schedules[] = [
                'client_id' => $clients->random()->id,
                'trainer_name' => $trainers[array_rand($trainers)],
                'scheduled_date' => $pastDate->format('Y-m-d'),
                'scheduled_time' => sprintf('%02d:00:00', rand(8, 17)),
                'payment_type' => $paymentTypes[array_rand($paymentTypes)],
                'status' => rand(0, 4) > 0 ? 'done' : 'cancelled', // 80% done, 20% cancelled
                'created_at' => $pastDate,
                'updated_at' => $pastDate,
            ];
        }

        PTSchedule::insert($schedules);

        $this->command->info('Created ' . count($schedules) . ' PT Schedules.');
    }
}
