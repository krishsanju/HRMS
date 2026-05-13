<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeUsers = User::where('role', 'employee')->get();
        $departments = Department::all();

        if ($employeeUsers->isEmpty() || $departments->isEmpty()) {
            $this->command->warn('No employee users or departments found. Please run UserSeeder and DepartmentSeeder first.');
            return;
        }

        // Create 20-30 employee profiles, linking them to existing employee users and departments
        $employeeUsers->each(function ($user) use ($departments) {
            Employee::factory()->create([
                'user_id' => $user->id,
                'email' => $user->email, // Ensure employee email matches user email
                'department_id' => $departments->random()->id,
                'status' => fake()->randomElement(['active', 'inactive']),
            ]);
        });

        // Ensure at least 20-30 employees total, if employeeUsers was less than 20
        $existingEmployeeCount = Employee::count();
        if ($existingEmployeeCount < 20) {
            $this->command->info("Creating additional employees to reach minimum 20-30.");
            $needed = 25 - $existingEmployeeCount; // Aim for 25 total
            Employee::factory()->count($needed)->create([
                'department_id' => $departments->random()->id,
                'user_id' => User::factory()->employee()->create()->id, // Create new employee users for these
            ]);
        }
    }
}