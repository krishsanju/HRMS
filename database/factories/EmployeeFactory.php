<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'employee_code' => 'EMP-' . $this->faker->unique()->randomNumber(4),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'department_id' => null, // Assuming department_id can be null or will be set by seeder/test
            'joining_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'role' => $this->faker->randomElement(['employee', 'hr', 'admin']), // Default role
        ];
    }
}