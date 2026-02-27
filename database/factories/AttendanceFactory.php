<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = $this->faker->dateTimeBetween('-1 day', 'now');
        $checkOut = $this->faker->boolean(70) ? (clone $checkIn)->modify('+' . $this->faker->numberBetween(4, 10) . ' hours') : null;

        return [
            'employee_id' => Employee::factory(),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ];
    }
}