<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default admin user for logging in
        User::factory()->create([
            'name' => 'HR Admin',
            'email' => 'admin@hrms.com',
        ]);

        // Run individual seeders in the correct order
        $this->call([
            DepartmentSeeder::class,
            LeaveTypeSeeder::class,
            EmployeeSeeder::class,
            LeaveRequestSeeder::class,
            AttendanceSeeder::class,
        ]);
    }
}