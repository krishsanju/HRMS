<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Annual Leave', 'Sick Leave', 'Paternity Leave', 'Maternity Leave', 'Bereavement Leave', 'Unpaid Leave']),
            'description' => fake()->sentence(),
            'accrual_rate' => fake()->randomFloat(2, 0.5, 2.0), // e.g., 0.5 to 2.0 days per month
            'max_days' => fake()->numberBetween(10, 30), // e.g., 10 to 30 days max
        ];
    }
}