<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $toDate = (clone $fromDate)->modify('+' . $this->faker->numberBetween(1, 10) . ' days');

        return [
            'employee_id' => Employee::factory(),
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'rejection_reason' => $this->faker->boolean(20) ? $this->faker->sentence() : null,
        ];
    }
}