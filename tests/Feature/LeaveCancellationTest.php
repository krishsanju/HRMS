<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class LeaveCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure migrations are run for Employee, User (if separate), and LeaveRequest
        $this->artisan('migrate');

        // Create factories if they don't exist or are not auto-loaded
        // For this test, we assume User and Employee models are set up to work with factories.
        // And that the authenticated User's ID maps to the employee_id in LeaveRequest.
    }

    /** @test */
    public function test_employee_can_cancel_pending_leave(): void
    {
        $employee = Employee::factory()->create();
        // Assuming User model's ID is used as employee_id for authentication
        $user = User::factory()->create(['id' => $employee->id]);

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'from_date' => Carbon::now()->addDays(5),
            'to_date' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user)->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'cancelled']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function test_employee_cannot_cancel_approved_leave(): void
    {
        $employee = Employee::factory()->create();
        $user = User::factory()->create(['id' => $employee->id]);

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'approved',
            'from_date' => Carbon::now()->addDays(5),
            'to_date' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user)->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(409)
                 ->assertJsonFragment(['message' => 'Approved leave requests cannot be cancelled.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved', // Status should remain approved
        ]);
    }

    /** @test */
    public function test_employee_cannot_cancel_other_employees_leave(): void
    {
        $employee1 = Employee::factory()->create();
        $user1 = User::factory()->create(['id' => $employee1->id]);

        $employee2 = Employee::factory()->create();

        $leaveRequestForEmployee2 = LeaveRequest::factory()->create([
            'employee_id' => $employee2->id,
            'status' => 'pending',
            'from_date' => Carbon::now()->addDays(5),
            'to_date' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user1)->patchJson("/api/leave/{$leaveRequestForEmployee2->id}/cancel");

        $response->assertStatus(403)
                 ->assertJsonFragment(['message' => 'You are not authorized to cancel this leave request.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequestForEmployee2->id,
            'status' => 'pending', // Status should remain pending
        ]);
    }

    /** @test */
    public function test_cannot_cancel_non_existent_leave(): void
    {
        $employee = Employee::factory()->create();
        $user = User::factory()->create(['id' => $employee->id]);

        $nonExistentLeaveId = 999;

        $response = $this->actingAs($user)->patchJson("/api/leave/{$nonExistentLeaveId}/cancel");

        $response->assertStatus(404); // Route model binding will return 404 automatically
    }

    /** @test */
    public function test_unauthenticated_user_cannot_cancel_leave(): void
    {
        $employee = Employee::factory()->create();
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'pending',
            'from_date' => Carbon::now()->addDays(5),
            'to_date' => Carbon::now()->addDays(7),
        ]);

        $response = $this->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(401); // Unauthenticated
    }

    /** @test */
    public function test_employee_can_cancel_rejected_leave(): void
    {
        $employee = Employee::factory()->create();
        $user = User::factory()->create(['id' => $employee->id]);

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'rejected',
            'from_date' => Carbon::now()->addDays(5),
            'to_date' => Carbon::now()->addDays(7),
        ]);

        $response = $this->actingAs($user)->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        // As per AC3, only 'approved' leaves cannot be cancelled. Rejected leaves can be.
        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'cancelled']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'cancelled',
        ]);
    }
}