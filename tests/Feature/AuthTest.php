<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an HR Admin employee for testing
        Employee::factory()->create([
            'first_name' => 'HR',
            'last_name' => 'Admin',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'hr_admin',
        ]);

        // Create a regular employee for testing
        Employee::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);
    }

    /** @test */
    public function a_user_can_login_with_valid_credentials_and_get_a_token()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'hr@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'employee' => ['id', 'employee_code', 'first_name', 'last_name', 'email', 'department_id', 'joining_date', 'status', 'role', 'created_at', 'updated_at'],
                     'token',
                 ]);

        $this->assertNotNull($response->json('token'));
        $this->assertEquals('hr_admin', $response->json('employee.role'));
    }

    /** @test */
    public function a_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'hr@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422) // Unprocessable Entity for validation/authentication failure
                 ->assertJsonValidationErrors(['email']); // Laravel's default for failed auth attempt
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_route()
    {
        $response = $this->getJson('/api/employees'); // Assuming /api/employees is protected

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    /** @test */
    public function user_with_incorrect_role_cannot_access_role_restricted_route()
    {
        $employee = Employee::where('email', 'employee@example.com')->first(); // Regular employee
        $token = $employee->createToken('test-token', ['role:employee'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/employees'); // This route requires 'hr_admin'

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Forbidden. You do not have the required role.']);
    }

    /** @test */
    public function user_with_correct_role_can_access_role_restricted_route()
    {
        $employee = Employee::where('email', 'employee@example.com')->first(); // Regular employee
        $token = $employee->createToken('test-token', ['role:employee'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/attendance/check-in', ['employee_id' => $employee->id]); // This route requires 'employee'

        $response->assertStatus(201); // Assuming check-in returns 201
    }

    /** @test */
    public function authenticated_user_can_logout_and_revoke_token()
    {
        $employee = Employee::where('email', 'hr@example.com')->first();
        $token = $employee->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logged out successfully']);

        // Try to access a protected route with the revoked token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/employees');

        $response->assertStatus(401); // Should be unauthenticated
    }
}