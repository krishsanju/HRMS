<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\PasswordResetMail;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake(); // Prevent emails from actually being sent
        Notification::fake(); // Prevent notifications from actually being sent
    }

    /** @test */
    public function an_employee_can_request_a_password_reset_link()
    {
        $employee = Employee::factory()->create(['email' => 'test@example.com', 'password' => 'old_password']);

        $response = $this->postJson('/api/password/forgot', ['email' => $employee->email]);

        $response->assertOk()
                 ->assertJson(['message' => 'If an account with that email exists, a password reset link has been sent.']);

        Mail::assertQueued(PasswordResetMail::class, function ($mail) use ($employee) {
            return $mail->hasTo($employee->email);
        });

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $employee->email]);
    }

    /** @test */
    public function requesting_a_password_reset_for_non_existent_email_returns_generic_success()
    {
        $response = $this->postJson('/api/password/forgot', ['email' => 'nonexistent@example.com']);

        $response->assertOk()
                 ->assertJson(['message' => 'If an account with that email exists, a password reset link has been sent.']);

        Mail::assertNothingQueued(); // No email should be sent
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'nonexistent@example.com']);
    }

    /** @test */
    public function an_employee_can_reset_their_password_with_a_valid_token()
    {
        $employee = Employee::factory()->create(['email' => 'test@example.com', 'password' => 'old_password']);
        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $employee->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $newPassword = 'new_secure_password';
        $response = $this->postJson('/api/password/reset', [
            'email' => $employee->email,
            'token' => $token,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Password has been successfully reset.']);

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $employee->email]);
        $this->assertTrue(app('hash')->check($newPassword, $employee->fresh()->password));
    }

    /** @test */
    public function password_reset_fails_with_invalid_token()
    n{
        $employee = Employee::factory()->create(['email' => 'test@example.com', 'password' => 'old_password']);
        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $employee->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $response = $this->postJson('/api/password/reset', [
            'email' => $employee->email,
            'token' => 'invalid_token',
            'password' => 'new_secure_password',
            'password_confirmation' => 'new_secure_password',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'This password reset token is invalid or has expired.']);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $employee->email]); // Token should still exist
    }

    /** @test */
    public function password_reset_fails_with_expired_token()
    {
        $employee = Employee::factory()->create(['email' => 'test@example.com', 'password' => 'old_password']);
        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $employee->email,
            'token' => $token,
            'created_at' => Carbon::now()->subMinutes(config('auth.passwords.employees.expire') + 1) // Expired
        ]);

        $response = $this->postJson('/api/password/reset', [
            'email' => $employee->email,
            'token' => $token,
            'password' => 'new_secure_password',
            'password_confirmation' => 'new_secure_password',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'This password reset token is invalid or has expired.']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $employee->email]); // Expired token should be deleted
    }

    /** @test */
    public function password_reset_fails_with_mismatched_email_and_token()
    {
        $employee1 = Employee::factory()->create(['email' => 'test1@example.com', 'password' => 'old_password']);
        $employee2 = Employee::factory()->create(['email' => 'test2@example.com', 'password' => 'old_password']);
        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $employee1->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        $response = $this->postJson('/api/password/reset', [
            'email' => $employee2->email, // Mismatched email
            'token' => $token,
            'password' => 'new_secure_password',
            'password_confirmation' => 'new_secure_password',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'This password reset token is invalid or has expired.']);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $employee1->email]); // Token for employee1 should still exist
    }
}