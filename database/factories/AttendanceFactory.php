<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = $this->faker->dateTimeThisMonth();
        $checkOut = (clone $checkIn)->modify('+' . $this->faker->numberBetween(4, 9) . ' hours');

        return [
            'employee_id' => Employee::factory(),
            'check_in' => $checkIn,
            'check_out' => $this->faker->boolean(80) ? $checkOut : null, // 80% chance of having a check_out
        ];
    }
}