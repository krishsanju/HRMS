<?php

namespace Database\Seeders;

use App\Models\Employee; // Import Employee model
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Remove User::factory() call as Employee is the authenticatable entity for API
        // User::factory(10)->create();

        // Create an HR Admin employee
        Employee::factory()->hrAdmin()->create([
            'first_name' => 'HR',
            'last_name' => 'Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create a regular employee
        Employee::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
        ]);

        // Optionally create more employees
        Employee::factory(5)->create();
    }
}