<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\User; // Assuming a User model exists for authentication

class LeaveCancellationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create an authenticated user and associated employee.
     */
    protected function createAuthenticatedEmployee(array $leaveRequestData = []): array
    {
        // Create a User and an Employee, linking them by ID
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['id' => $user->id]); // Assuming user_id maps to employee_id
        
        // Authenticate the user for API routes
        $this->actingAs($user, 'api'); 

        $leaveRequest = LeaveRequest::factory()->create(array_merge([
            'employee_id' => $employee->id,
            'status' => 'pending',
        ], $leaveRequestData));

        return ['user' => $user, 'employee' => $employee, 'leaveRequest' => $leaveRequest];
    }

    /** @test */
    public function test_employee_can_cancel_pending_leave(): void
    {
        $data = $this->createAuthenticatedEmployee();
        $leaveRequest = $data['leaveRequest'];

        $response = $this->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertOk() // 200 OK
                 ->assertJson(['message' => 'Leave request cancelled successfully.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function test_employee_cannot_cancel_approved_leave(): void
    {
        $data = $this->createAuthenticatedEmployee(['status' => 'approved']);
        $leaveRequest = $data['leaveRequest'];

        $response = $this->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(400) // Bad Request
                 ->assertJson(['message' => 'Approved leave requests cannot be cancelled.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved', // Status should remain approved
        ]);
    }

    /** @test */
    public function test_employee_cannot_cancel_other_employees_leave(): void
    {
        $data1 = $this->createAuthenticatedEmployee();
        // Create another employee and their leave request
        $user2 = User::factory()->create();
        $employee2 = Employee::factory()->create(['id' => $user2->id]);
        $leaveRequest2 = LeaveRequest::factory()->create([
            'employee_id' => $employee2->id,
            'status' => 'pending',
        ]);

        // Employee 1 tries to cancel Employee 2's leave
        $response = $this->actingAs($data1['user'], 'api') // Authenticate as user1
                         ->patchJson("/api/leave/{$leaveRequest2->id}/cancel");

        $response->assertStatus(403) // Forbidden
                 ->assertJson(['message' => 'You are not authorized to cancel this leave request.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest2->id,
            'status' => 'pending', // Status should remain pending
        ]);
    }

    /** @test */
    public function test_unauthenticated_user_cannot_cancel_leave(): void
    {
        $employee = Employee::factory()->create();
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $employee->id,
            'status' => 'pending',
        ]);

        // No actingAs, so unauthenticated
        $response = $this->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(401); // Unauthorized
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function test_cancelling_non_existent_leave_returns_404(): void
    {
        $data = $this->createAuthenticatedEmployee();
        // Try to cancel a non-existent leave ID
        $response = $this->patchJson("/api/leave/9999/cancel");

        $response->assertStatus(404); // Not Found
    }

    /** @test */
    public function test_employee_cannot_cancel_rejected_leave(): void
    {
        $data = $this->createAuthenticatedEmployee(['status' => 'rejected']);
        $leaveRequest = $data['leaveRequest'];

        $response = $this->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(400) // Bad Request
                 ->assertJson(['message' => 'Rejected leave requests cannot be cancelled.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected', // Status should remain rejected
        ]);
    }

    /** @test */
    public function test_employee_cannot_cancel_already_cancelled_leave(): void
    {
        $data = $this->createAuthenticatedEmployee(['status' => 'cancelled']);
        $leaveRequest = $data['leaveRequest'];

        $response = $this->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(400) // Bad Request
                 ->assertJson(['message' => 'This leave request has already been cancelled.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'cancelled', // Status should remain cancelled
        ]);
    }
}