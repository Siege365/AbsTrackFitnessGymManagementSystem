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
        // Create admin user for gym management
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@abstrack.com',
            'password' => bcrypt('password'),
        ]);

        // Create test gym manager
        User::factory()->create([
            'name' => 'Gym Manager',
            'email' => 'manager@abstrack.com',
            'password' => bcrypt('password'),
        ]);
    }
}
