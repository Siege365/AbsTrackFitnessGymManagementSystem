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
            ['email' => 'admin@abstrack.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@abstrack.com'],
            [
                'name' => 'Gym Manager',
                'password' => bcrypt('password'),
            ]
        );

        $this->call([
            MembershipSeeder::class,
            InventorySupplySeeder::class,
            ClientSeeder::class,
        ]);
    }
}
