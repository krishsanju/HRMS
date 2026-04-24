<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'employee_code' => $this->faker->unique()->randomNumber(5),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'department_id' => null, // Assuming departments are seeded separately or can be null
            'joining_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'password' => Hash::make('password'), // Default password for factory-created employees
            'role' => 'employee', // Default role
            'remember_token' => Str::random(10),
        ];
    }

    public function hrAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'hr_admin',
        ]);
    }
}