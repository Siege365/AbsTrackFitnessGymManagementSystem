<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Client;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
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

        $attendances = [];
        $today = Carbon::today();
        $statuses = ['active', 'expired', 'due_soon'];

        // Create attendance records for today
        $todayClients = $clients->random(min(8, $clients->count()));
        foreach ($todayClients as $client) {
            $attendances[] = [
                'client_id' => $client->id,
                'date' => $today->format('Y-m-d'),
                'time_in' => sprintf('%02d:%02d:00', rand(6, 12), rand(0, 59)),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Create attendance records for yesterday (for percentage comparison)
        $yesterday = $today->copy()->subDay();
        $yesterdayClients = $clients->random(min(6, $clients->count()));
        foreach ($yesterdayClients as $client) {
            $attendances[] = [
                'client_id' => $client->id,
                'date' => $yesterday->format('Y-m-d'),
                'time_in' => sprintf('%02d:%02d:00', rand(6, 12), rand(0, 59)),
                'status' => $statuses[array_rand($statuses)],
                'created_at' => $yesterday,
                'updated_at' => $yesterday,
            ];
        }

        // Create attendance records for past week
        for ($day = 2; $day <= 7; $day++) {
            $pastDate = $today->copy()->subDays($day);
            $count = rand(4, 10);
            $dayClients = $clients->random(min($count, $clients->count()));
            
            foreach ($dayClients as $client) {
                $attendances[] = [
                    'client_id' => $client->id,
                    'date' => $pastDate->format('Y-m-d'),
                    'time_in' => sprintf('%02d:%02d:00', rand(6, 12), rand(0, 59)),
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => $pastDate,
                    'updated_at' => $pastDate,
                ];
            }
        }

        Attendance::insert($attendances);

        $this->command->info('Created ' . count($attendances) . ' Attendance records.');
    }
}
