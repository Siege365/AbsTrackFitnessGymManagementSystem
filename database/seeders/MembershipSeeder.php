<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Membership;
use Carbon\Carbon;

class MembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'name' => 'Henry Klein',
                'age' => 24,
                'plan_type' => 'Monthly',
                'start_date' => Carbon::parse('2025-09-09'),
                'due_date' => Carbon::parse('2025-10-09'),
                'status' => 'Expired',
                'contact' => '0912-345-6789',
            ],
            [
                'name' => 'Estella Bryan',
                'age' => 28,
                'plan_type' => 'Monthly',
                'start_date' => Carbon::parse('2025-09-10'),
                'due_date' => Carbon::parse('2025-10-10'),
                'status' => 'Due soon',
                'contact' => '0998-765-4321',
            ],
            [
                'name' => 'Lucy Abbott',
                'age' => 22,
                'plan_type' => 'Session',
                'start_date' => Carbon::parse('2025-09-15'),
                'due_date' => Carbon::parse('2025-10-15'),
                'status' => 'Active',
                'contact' => '0917-111-2222',
            ],
            [
                'name' => 'Peter Gill',
                'age' => 30,
                'plan_type' => 'Session',
                'start_date' => Carbon::parse('2025-09-15'),
                'due_date' => Carbon::parse('2025-10-15'),
                'status' => 'Active',
                'contact' => '0916-323-5935',
            ],
            [
                'name' => 'Salle Reyes',
                'age' => 26,
                'plan_type' => 'Monthly',
                'start_date' => Carbon::parse('2025-09-11'),
                'due_date' => Carbon::parse('2025-10-11'),
                'status' => 'Due soon',
                'contact' => '0911-987-2842',
            ],
        ];

        foreach ($members as $member) {
            Membership::create($member);
        }
    }
}
