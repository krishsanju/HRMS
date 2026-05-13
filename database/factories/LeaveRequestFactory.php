<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 5) . ' days');

        return [
            'employee_id' => Employee::inRandomOrder()->first()->id ?? Employee::factory(),
            'leave_type_id' => LeaveType::inRandomOrder()->first()->id ?? LeaveType::factory(),
            'from_date' => $startDate,
            'to_date' => $endDate,
            'reason' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }

    /**
     * Indicate that the leave request is approved.
     */
    public function approved(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    /**
     * Indicate that the leave request is rejected.
     */
    public function rejected(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
            ];
        });
    }
}