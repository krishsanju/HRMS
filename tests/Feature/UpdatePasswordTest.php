<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function an_authenticated_employee_can_update_their_password()
    {
        $employee = Employee::factory()->create([
            'email' => 'test@example.com',
            'password' => 'old_secure_password',
        ]);

        Sanctum::actingAs($employee, ['*']); // Authenticate the employee

        $newPassword = 'new_secure_password';
        $response = $this->putJson('/api/user/password', [
            'current_password' => 'old_secure_password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Password has been successfully updated.']);

        $this->assertTrue(Hash::check($newPassword, $employee->fresh()->password));
    }

    /** @test */
    public function password_update_fails_with_incorrect_current_password()
    {
        $employee = Employee::factory()->create([
            'email' => 'test@example.com',
            'password' => 'old_secure_password',
        ]);

        Sanctum::actingAs($employee, ['*']);

        $newPassword = 'new_secure_password';
        $response = $this->putJson('/api/user/password', [
            'current_password' => 'incorrect_password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(422) // Unprocessable Entity for validation errors
                 ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('old_secure_password', $employee->fresh()->password)); // Password should not change
    }

    /** @test */
    public function password_update_fails_with_invalid_new_password_format()
    {
        $employee = Employee::factory()->create([
            'email' => 'test@example.com',
            'password' => 'old_secure_password',
        ]);

        Sanctum::actingAs($employee, ['*']);

        $response = $this->putJson('/api/user/password', [
            'current_password' => 'old_secure_password',
            'password' => 'short', // Invalid new password
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);

        $this->assertTrue(Hash::check('old_secure_password', $employee->fresh()->password));
    }

    /** @test */
    public function unauthenticated_user_cannot_update_password()
    {
        $employee = Employee::factory()->create([
            'email' => 'test@example.com',
            'password' => 'old_secure_password',
        ]);

        $newPassword = 'new_secure_password';
        $response = $this->putJson('/api/user/password', [
            'current_password' => 'old_secure_password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(401); // Unauthorized
        $this->assertTrue(Hash::check('old_secure_password', $employee->fresh()->password));
    }
}