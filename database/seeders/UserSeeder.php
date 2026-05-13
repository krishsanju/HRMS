<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5+ distinct administrator accounts
        User::factory()->admin()->create([
            'name' => 'HR Admin One',
            'email' => 'admin1@example.com',
            'password' => Hash::make('password'),
        ]);
        User::factory()->admin()->create([
            'name' => 'HR Admin Two',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password'),
        ]);
        User::factory()->admin()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
        ]);
        User::factory()->admin()->count(2)->create(); // 2 more random admins

        // Create 20-30 distinct employee accounts (will be linked to employees later)
        User::factory()->employee()->count(25)->create(); // 25 employee users
    }
}