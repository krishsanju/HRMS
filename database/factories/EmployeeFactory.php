<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_code' => 'EMP-' . fake()->unique()->randomNumber(5),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(), // Separate email from user email
            'department_id' => Department::factory(),
            'joining_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'status' => fake()->randomElement(['active', 'inactive']),
            'user_id' => User::factory()->employee(), // Link to an employee user
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Employee $employee) {
            // Ensure the user's email matches the employee's email if not explicitly set
            if ($employee->user && $employee->user->email !== $employee->email) {
                $employee->user->email = $employee->email;
                $employee->user->save();
            }
        })->afterCreating(function (Employee $employee) {
            // Ensure the user's email matches the employee's email if not explicitly set
            if ($employee->user && $employee->user->email !== $employee->email) {
                $employee->user->email = $employee->email;
                $employee->user->save();
            }
        });
    }

    /**
     * Indicate that the employee is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the employee is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}