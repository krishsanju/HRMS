<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class HRMetricsApiTest extends TestCase
{
    use RefreshDatabase; // Ensure a clean database for each test

    protected function setUp(): void
    {
        parent::setUp();

        // Create some test data
        // Employees
        Employee::factory()->create(['status' => 'active', 'role' => 'employee']);
        Employee::factory()->create(['status' => 'active', 'role' => 'employee']);
        Employee::factory()->create(['status' => 'inactive', 'role' => 'employee']);
        Employee::factory()->create(['status' => 'active', 'role' => 'hr']); // HR user
        Employee::factory()->create(['status' => 'active', 'role' => 'admin']); // Admin user

        // Leave Requests
        $employee1 = Employee::find(1);
        $employee2 = Employee::find(2);

        LeaveRequest::factory()->create(['employee_id' => $employee1->id, 'status' => 'pending']);
        LeaveRequest::factory()->create(['employee_id' => $employee1->id, 'status' => 'approved']);
        LeaveRequest::factory()->create(['employee_id' => $employee2->id, 'status' => 'rejected']);
        LeaveRequest::factory()->create(['employee_id' => $employee2->id, 'status' => 'pending']);

        // Attendance
        $employee1 = Employee::find(1);
        $employee2 = Employee::find(2);

        // Today's attendance for employee1
        Attendance::factory()->create([
            'employee_id' => $employee1->id,
            'check_in' => Carbon::now()->subHours(8),
            'check_out' => Carbon::now(),
        ]);
        // Today's attendance for employee2 (only check-in)
        Attendance::factory()->create([
            'employee_id' => $employee2->id,
            'check_in' => Carbon::now()->subHours(4),
            'check_out' => null,
        ]);
        // Yesterday's attendance for employee1
        Attendance::factory()->create([
            'employee_id' => $employee1->id,
            'check_in' => Carbon::yesterday()->subHours(8),
            'check_out' => Carbon::yesterday(),
        ]);
    }

    /** @test */
    public function hr_admin_can_retrieve_employee_counts()
    {
        $admin = Employee::where('role', 'admin')->first();
        Sanctum::actingAs($admin, ['hr-metrics']);

        $response = $this->getJson('/api/hr-metrics/employee-counts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'total_employees',
                         'active_employees',
                         'inactive_employees',
                     ]
                 ])
                 ->assertJson([
                     'data' => [
                         'total_employees' => 5, // 3 employee, 1 hr, 1 admin
                         'active_employees' => 4, // 2 employee, 1 hr, 1 admin
                         'inactive_employees' => 1, // 1 employee
                     ]
                 ]);
    }

    /** @test */
    public function hr_admin_can_retrieve_leave_request_counts()
    {
        $hr = Employee::where('role', 'hr')->first();
        Sanctum::actingAs($hr, ['hr-metrics']);

        $response = $this->getJson('/api/hr-metrics/leave-request-counts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'total_requests',
                         'pending_requests',
                         'approved_requests',
                         'rejected_requests',
                     ]
                 ])
                 ->assertJson([
                     'data' => [
                         'total_requests' => 4,
                         'pending_requests' => 2,
                         'approved_requests' => 1,
                         'rejected_requests' => 1,
                     ]
                 ]);
    }

    /** @test */
    public function hr_admin_can_retrieve_attendance_summary()
    {
        $admin = Employee::where('role', 'admin')->first();
        Sanctum::actingAs($admin, ['hr-metrics']);

        $response = $this->getJson('/api/hr-metrics/attendance-summary');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'daily_summary' => [
                             '*' => [
                                 'check_ins_count',
                                 'check_outs_count',
                                 'total_working_hours',
                             ]
                         ],
                         'total_check_ins_period',
                         'total_check_outs_period',
                         'total_working_hours_period',
                     ]
                 ]);
        
        // Assert specific values for today and yesterday
        $today = Carbon::now()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $response->assertJsonPath('data.daily_summary.' . $today . '.check_ins_count', 2);
        $response->assertJsonPath('data.daily_summary.' . $today . '.check_outs_count', 1);
        $response->assertJsonPath('data.daily_summary.' . $yesterday . '.check_ins_count', 1);
        $response->assertJsonPath('data.daily_summary.' . $yesterday . '.check_outs_count', 1);

        // Total for the period
        $response->assertJsonPath('data.total_check_ins_period', 3);
        $response->assertJsonPath('data.total_check_outs_period', 2);
        // Working hours calculation: 8 hours for employee1 today, 0 for employee2 today, 8 hours for employee1 yesterday
        $response->assertJsonPath('data.total_working_hours_period', 16.00);
    }

    /** @test */
    public function hr_admin_can_retrieve_attendance_summary_with_date_range()
    {
        $admin = Employee::where('role', 'admin')->first();
        Sanctum::actingAs($admin, ['hr-metrics']);

        $startDate = Carbon::yesterday()->format('Y-m-d');
        $endDate = Carbon::yesterday()->format('Y-m-d');

        $response = $this->getJson("/api/hr-metrics/attendance-summary?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'daily_summary',
                         'total_check_ins_period',
                         'total_check_outs_period',
                         'total_working_hours_period',
                     ]
                 ]);
        
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $response->assertJsonPath('data.daily_summary.' . $yesterday . '.check_ins_count', 1);
        $response->assertJsonPath('data.daily_summary.' . $yesterday . '.check_outs_count', 1);
        $response->assertJsonPath('data.total_check_ins_period', 1);
        $response->assertJsonPath('data.total_check_outs_period', 1);
        $response->assertJsonPath('data.total_working_hours_period', 8.00);
    }

    /** @test */
    public function employee_cannot_access_hr_metrics()
    {
        $employee = Employee::where('role', 'employee')->first();
        Sanctum::actingAs($employee, ['hr-metrics']);

        $this->getJson('/api/hr-metrics/employee-counts')->assertStatus(403);
        $this->getJson('/api/hr-metrics/leave-request-counts')->assertStatus(403);
        $this->getJson('/api/hr-metrics/attendance-summary')->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_hr_metrics()
    {
        $this->getJson('/api/hr-metrics/employee-counts')->assertStatus(401);
        $this->getJson('/api/hr-metrics/leave-request-counts')->assertStatus(401);
        $this->getJson('/api/hr-metrics/attendance-summary')->assertStatus(401);
    }
}