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
        $checkInTime = $this->faker->dateTimeBetween('-3 months', 'now');
        $checkOutTime = (clone $checkInTime)->modify('+' . $this->faker->numberBetween(4, 9) . ' hours');

        return [
            'employee_id' => Employee::inRandomOrder()->first()->id ?? Employee::factory(),
            'check_in' => $checkInTime,
            'check_out' => $this->faker->boolean(80) ? $checkOutTime : null,
        ];
    }
}