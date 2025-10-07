<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use Carbon\Carbon;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'John Doe',
                'age' => 25,
                'plan_type' => 'Monthly',
                'start_date' => Carbon::parse('2025-09-08'),
                'due_date' => Carbon::parse('2025-10-08'),
                'status' => 'Expired',
                'contact' => '09123456789',
            ],
            [
                'name' => 'Jane Smith',
                'age' => 32,
                'plan_type' => 'Monthly',
                'start_date' => Carbon::parse('2025-10-01'),
                'due_date' => Carbon::parse('2025-11-01'),
                'status' => 'Active',
                'contact' => '09187654321',
            ],
            [
                'name' => 'Mike Johnson',
                'age' => 27,
                'plan_type' => 'Session',
                'start_date' => Carbon::parse('2025-10-05'),
                'due_date' => Carbon::parse('2025-10-12'),
                'status' => 'Due soon',
                'contact' => '+63 917 123 4567',
            ],
            [
                'name' => 'Sarah Williams',
                'age' => 29,
                'plan_type' => 'Monthly',
                'start_date' => Carbon::parse('2025-09-20'),
                'due_date' => Carbon::parse('2025-10-20'),
                'status' => 'Active',
                'contact' => '(02) 8888 9999',
            ],
            [
                'name' => 'David Brown',
                'age' => 35,
                'plan_type' => 'Session',
                'start_date' => Carbon::parse('2025-10-01'),
                'due_date' => Carbon::parse('2025-11-15'),
                'status' => 'Active',
                'contact' => '0927 555 6666',
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
