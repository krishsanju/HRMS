<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User; // Assuming a User model exists for authentication
use App\Models\LeaveRequest;

class LeaveCancellationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create a user.
     * For the purpose of this test, we assume the User's ID
     * is directly used as the employee_id in the LeaveRequest model.
     */
    protected function createUserForAuth($overrides = [])
    {
        return User::factory()->create($overrides);
    }

    /** @test */
    public function test_employee_can_cancel_their_pending_leave()
    {
        $user = $this->createUserForAuth();
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user->id, // Link leave request to the user's ID
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'api')
                         ->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertOk() // 200 OK
                 ->assertJson(['status' => 'cancelled']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function test_employee_cannot_cancel_another_employees_leave()
    {
        $user1 = $this->createUserForAuth();
        $user2 = $this->createUserForAuth(); // The unauthorized employee

        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user1->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user2, 'api') // Acting as user2
                         ->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertForbidden() // 403 Forbidden
                 ->assertJson(['message' => 'Unauthorized to cancel this leave request.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'pending', // Status should remain unchanged
        ]);
    }

    /** @test */
    public function test_employee_cannot_cancel_an_approved_leave()
    {
        $user = $this->createUserForAuth();
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'status' => 'approved', // Approved leave
        ]);

        $response = $this->actingAs($user, 'api')
                         ->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(400) // 400 Bad Request
                 ->assertJson(['message' => 'Cannot cancel an approved leave request.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'approved', // Status should remain unchanged
        ]);
    }

    /** @test */
    public function test_employee_cannot_cancel_a_rejected_leave()
    {
        $user = $this->createUserForAuth();
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'status' => 'rejected', // Rejected leave
        ]);

        $response = $this->actingAs($user, 'api')
                         ->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(400) // 400 Bad Request
                 ->assertJson(['message' => 'Leave request is already in a final state and cannot be cancelled.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'rejected', // Status should remain unchanged
        ]);
    }

    /** @test */
    public function test_employee_cannot_cancel_an_already_cancelled_leave()
    {
        $user = $this->createUserForAuth();
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'status' => 'cancelled', // Already cancelled leave
        ]);

        $response = $this->actingAs($user, 'api')
                         ->patchJson("/api/leave/{$leaveRequest->id}/cancel");

        $response->assertStatus(400) // 400 Bad Request
                 ->assertJson(['message' => 'Leave request is already in a final state and cannot be cancelled.']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'cancelled', // Status should remain unchanged
        ]);
    }

    /** @test */
    public function test_guest_cannot_cancel_leave()
    {
        $user = $this->createUserForAuth();
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->patchJson("/api/leave/{$leaveRequest->id}/cancel"); // No actingAs

        $response->assertUnauthorized(); // 401 Unauthorized

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'pending', // Status should remain unchanged
        ]);
    }

    /** @test */
    public function test_cannot_cancel_non_existent_leave()
    {
        $user = $this->createUserForAuth();

        $response = $this->actingAs($user, 'api')
                         ->patchJson("/api/leave/999/cancel"); // Non-existent ID

        $response->assertNotFound(); // 404 Not Found
    }
}