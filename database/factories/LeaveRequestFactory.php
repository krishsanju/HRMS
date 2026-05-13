<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', '+3 months');
        $endDate = fake()->dateTimeBetween($startDate, (clone $startDate)->modify('+15 days'));

        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'from_date' => $startDate,
            'to_date' => $endDate,
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'reason' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the leave request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the leave request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the leave request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (LeaveRequest $leaveRequest) {
            // Ensure employee_id is set if not provided
            if (!$leaveRequest->employee_id) {
                $leaveRequest->employee_id = Employee::factory()->create()->id;
            }
            // Ensure leave_type_id is set if not provided
            if (!$leaveRequest->leave_type_id) {
                $leaveRequest->leave_type_id = LeaveType::factory()->create()->id;
            }
        });
    }
}