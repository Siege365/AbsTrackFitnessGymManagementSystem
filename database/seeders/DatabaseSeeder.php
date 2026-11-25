<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'owner@abstrack.com'],
            [
                'name' => 'Gym Owner',
                'password' => bcrypt('password'),
                'role' => 'owner',
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@abstrack.com'],
            [
                'name' => 'Gym Manager',
                'password' => bcrypt('password'),
                'role' => 'manager',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@abstrack.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $this->call([
            GymPlanSeeder::class,
            MembershipSeeder::class,
            InventorySupplySeeder::class,
            ClientSeeder::class,
            PTScheduleSeeder::class,
            AttendanceSeeder::class,
        ]);
    }
}
