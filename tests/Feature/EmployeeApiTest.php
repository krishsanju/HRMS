<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that we can retrieve a specific employee via the API.
     */
    public function test_it_can_show_a_specific_employee(): void
    {
        // 1. Arrange
        // Create a user to authenticate the request
        $user = User::factory()->create();

        // Create a test employee using our new factory
        $employee = Employee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // 2. Act
        // Authenticate as the user and make an API call to the endpoint
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/employees/' . $employee->id);

        // 3. Assert
        // Assert that the request was successful
        $response->assertStatus(200);

        // Assert that the response contains the correct data structure and values
        $response->assertJson([
            'id' => $employee->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $employee->email,
            'department' => [
                'id' => $employee->department->id,
                'name' => $employee->department->name,
            ]
        ]);

        // Also assert that the response has the expected top-level keys
        $response->assertJsonStructure([
            'id',
            'employee_code',
            'first_name',
            'last_name',
            'email',
            'phone',
            'position',
            'department_id',
            'hire_date',
            'status',
            'created_at',
            'updated_at',
            'department' => [
                'id',
                'name',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}