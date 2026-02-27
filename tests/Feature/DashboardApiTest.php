<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\User; // Assuming a User model for authentication
use App\Models\Department;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $hrAdminUser;
    protected User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an HR/Admin user
        $this->hrAdminUser = User::factory()->admin()->create();
        // Create a regular employee user
        $this->employeeUser = User::factory()->employee()->create();

        // Seed some data
        Department::factory()->count(3)->create(); // Ensure departments exist for employees
        Employee::factory()->count(5)->create(['status' => 'active']);
        Employee::factory()->count(2)->create(['status' => 'inactive']);

        LeaveRequest::factory()->count(3)->create(['status' => 'pending']);
        LeaveRequest::factory()->count(2)->create(['status' => 'approved']);
        LeaveRequest::factory()->count(1)->create(['status' => 'rejected']);

        // Create attendance records for today
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();
        $employee3 = Employee::factory()->create();

        Attendance::factory()->create([
            'employee_id' => $employee1->id,
            'check_in' => now()->subHours(8),
            'check_out' => now(),
        ]);
        Attendance::factory()->create([
            'employee_id' => $employee2->id,
            'check_in' => now()->subHours(4),
            'check_out' => null, // Still checked in
        ]);
        Attendance::factory()->create([
            'employee_id' => $employee3->id,
            'check_in' => now()->subHours(6),
            'check_out' => now()->subHours(1),
        ]);
    }

    /** @test */
    public function hr_admin_can_get_employee_counts()
    {
        $response = $this->actingAs($this->hrAdminUser)->getJson('/api/dashboard/employees/counts');

        $response->assertStatus(200)
                 ->assertJsonStructure(['active', 'inactive', 'total'])
                 ->assertJson([
                     'active' => 8, // 5 seeded + 3 for attendance
                     'inactive' => 2,
                     'total' => 10,
                 ]);
    }

    /** @test */
    public function hr_admin_can_get_leave_request_counts()
    {
        $response = $this->actingAs($this->hrAdminUser)->getJson('/api/dashboard/leaves/counts');

        $response->assertStatus(200)
                 ->assertJsonStructure(['pending', 'approved', 'rejected', 'total'])
                 ->assertJson([
                     'pending' => 3,
                     'approved' => 2,
                     'rejected' => 1,
                     'total' => 6,
                 ]);
    }

    /** @test */
    public function hr_admin_can_get_attendance_summary()
    {
        $response = $this->actingAs($this->hrAdminUser)->getJson('/api/dashboard/attendance/summary');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'date',
                     'check_ins_today',
                     'check_outs_today',
                     'total_working_hours_today',
                 ])
                 ->assertJson([
                     'check_ins_today' => 3,
                     'check_outs_today' => 2,
                 ]);
        // Check total_working_hours_today is a float and approximately correct
        $this->assertIsFloat($response->json('total_working_hours_today'));
        $this->assertEqualsWithDelta(8.0 + 5.0, $response->json('total_working_hours_today'), 0.1); // 8 hours + 5 hours
    }

    /** @test */
    public function hr_admin_can_reject_leave_request()
    {
        $leave = LeaveRequest::factory()->create(['status' => 'pending']);
        $rejectionReason = 'Not enough staff available.';

        $response = $this->actingAs($this->hrAdminUser)->patchJson("/api/leave/{$leave->id}/reject", [
            'rejection_reason' => $rejectionReason,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $leave->id,
                     'status' => 'rejected',
                     'rejection_reason' => $rejectionReason,
                 ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
        ]);
    }

    /** @test */
    public function hr_admin_can_reject_leave_request_without_reason()
    {
        $leave = LeaveRequest::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->hrAdminUser)->patchJson("/api/leave/{$leave->id}/reject");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $leave->id,
                     'status' => 'rejected',
                     'rejection_reason' => null,
                 ]);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'rejected',
            'rejection_reason' => null,
        ]);
    }

    /** @test */
    public function non_hr_admin_cannot_access_dashboard_endpoints()
    {
        $response = $this->actingAs($this->employeeUser)->getJson('/api/dashboard/employees/counts');
        $response->assertStatus(403);

        $response = $this->actingAs($this->employeeUser)->getJson('/api/dashboard/leaves/counts');
        $response->assertStatus(403);

        $response = $this->actingAs($this->employeeUser)->getJson('/api/dashboard/attendance/summary');
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_dashboard_endpoints()
    {
        $response = $this->getJson('/api/dashboard/employees/counts');
        $response->assertStatus(401);

        $response = $this->getJson('/api/dashboard/leaves/counts');
        $response->assertStatus(401);

        $response = $this->getJson('/api/dashboard/attendance/summary');
        $response->assertStatus(401);
    }

    /** @test */
    public function non_hr_admin_cannot_reject_leave_request()
    {
        $leave = LeaveRequest::factory()->create(['status' => 'pending']);
        $response = $this->actingAs($this->employeeUser)->patchJson("/api/leave/{$leave->id}/reject");
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_reject_leave_request()
    {
        $leave = LeaveRequest::factory()->create(['status' => 'pending']);
        $response = $this->patchJson("/api/leave/{$leave->id}/reject");
        $response->assertStatus(401);
    }

    /** @test */
    public function employee_can_apply_for_leave()
    {
        $employee = Employee::factory()->create();
        $response = $this->actingAs($this->employeeUser)->postJson('/api/leave/apply', [
            'employee_id' => $employee->id,
            'from_date' => now()->addDays(5)->toDateString(),
            'to_date' => now()->addDays(7)->toDateString(),
            'reason' => 'Vacation trip to the mountains.',
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'employee_id' => $employee->id,
                     'status' => 'pending',
                     'rejection_reason' => null,
                 ]);
        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);
    }

    /** @test */
    public function employee_cannot_apply_for_leave_without_reason()
    {
        $employee = Employee::factory()->create();
        $response = $this->actingAs($this->employeeUser)->postJson('/api/leave/apply', [
            'employee_id' => $employee->id,
            'from_date' => now()->addDays(5)->toDateString(),
            'to_date' => now()->addDays(7)->toDateString(),
            // 'reason' is missing
        ]);

        $response->assertStatus(422) // Unprocessable Entity due to validation error
                 ->assertJsonValidationErrors(['reason']);
    }

    /** @test */
    public function employee_can_check_in()
    {
        $employee = Employee::factory()->create();
        $response = $this->actingAs($this->employeeUser)->postJson('/api/attendance/check-in', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'employee_id', 'check_in', 'check_out']);
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employee->id,
            'check_out' => null,
        ]);
    }

    /** @test */
    public function employee_can_check_out()
    {
        $employee = Employee::factory()->create();
        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'check_in' => now()->subHours(4),
            'check_out' => null,
        ]);

        $response = $this->actingAs($this->employeeUser)->postJson('/api/attendance/check-out', [
            'employee_id' => $employee->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'employee_id', 'check_in', 'check_out']);
        $this->assertNotNull($response->json('check_out'));
        $this->assertDatabaseMissing('attendances', [
            'employee_id' => $employee->id,
            'check_out' => null,
        ]);
    }
}