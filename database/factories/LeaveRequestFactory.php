<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class LeaveRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LeaveRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $startDate = Carbon::parse($this->faker->dateTimeBetween('-1 month', '+1 month'));
        $endDate = $startDate->copy()->addDays($this->faker->numberBetween(1, 5));

        return [
            'employee_id' => Employee::factory(), // Automatically create an employee if not provided
            'from_date' => $startDate->format('Y-m-d'),
            'to_date' => $endDate->format('Y-m-d'),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'reason' => $this->faker->sentence,
        ];
    }
}