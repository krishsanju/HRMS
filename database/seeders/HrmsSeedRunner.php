<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class HrmsSeedRunner extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            LeaveTypeSeeder::class,
            UserSeeder::class, // Creates admin and employee users
            EmployeeSeeder::class, // Links employee users to employee profiles
            LeaveRequestSeeder::class,
        ]);
    }
}