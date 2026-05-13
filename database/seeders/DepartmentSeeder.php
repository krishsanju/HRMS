<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resources', 'description' => 'Manages employee relations, recruitment, and benefits.'],
            ['name' => 'Engineering', 'description' => 'Develops and maintains software products.'],
            ['name' => 'Sales', 'description' => 'Drives revenue by selling products and services.'],
            ['name' => 'Marketing', 'description' => 'Promotes products and services to target audiences.'],
            ['name' => 'Finance', 'description' => 'Manages financial planning, reporting, and operations.'],
            ['name' => 'Customer Support', 'description' => 'Provides assistance and support to customers.'],
            ['name' => 'Product Management', 'description' => 'Oversees the lifecycle of products from conception to launch.'],
        ];

        foreach ($departments as $department) {
            Department::factory()->create($department);
        }
    }
}