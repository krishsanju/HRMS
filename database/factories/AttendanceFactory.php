<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

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
     * @return array
     */
    public function definition()
    {
        $checkIn = Carbon::parse($this->faker->dateTimeBetween('-1 day', 'now'));
        $checkOut = $checkIn->copy()->addHours($this->faker->numberBetween(4, 9));

        return [
            'employee_id' => Employee::factory(), // Automatically create an employee if not provided
            'check_in' => $checkIn,
            'check_out' => $this->faker->boolean(80) ? $checkOut : null, // 80% chance of having a checkout
        ];
    }
}