<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Manually create a department
        $department = Department::create(['name' => 'Engineering']);

        // Manually create an employee
        $employee = Employee::create([
            'employee_code' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'department_id' => $department->id,
            'joining_date' => '2020-01-01',
            'status' => 'active',
        ]);

        // Create attendance records for the employee
        for ($i = 0; $i < 7; $i++) {
            Attendance::create([
                'employee_id' => $employee->id,
                'check_in' => now()->subDays($i)->startOfDay()->addHours(9),
                'check_out' => now()->subDays($i)->startOfDay()->addHours(17),
            ]);
        }

        // Create leave requests for the employee
        $statuses = ['pending', 'approved', 'rejected'];
        for ($i = 0; $i < 7; $i++) {
            LeaveRequest::create([
                'employee_id' => $employee->id,
                'from_date' => now()->subDays($i * 2)->format('Y-m-d'),
                'to_date' => now()->subDays($i * 2)->addDays(rand(1, 5))->format('Y-m-d'),
                'status' => $statuses[array_rand($statuses)],
            ]);
        }
    }

    /** @test */
    public function it_returns_an_employees_comprehensive_profile_with_relations_and_eager_loading()
    {
        $employee = Employee::first();

        DB::connection()->enableQueryLog(); // Start query log

        $response = $this->getJson("/api/employees/{$employee->id}");

        $queries = DB::connection()->getQueryLog();
        DB::connection()->disableQueryLog(); // Stop query log

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'employee_code',
                         'first_name',
                         'last_name',
                         'email',
                         'joining_date',
                         'status',
                         'department' => [
                             'id',
                             'name',
                             'created_at',
                             'updated_at',
                         ],
                         'attendances' => [
                             '*' => [
                                 'id',
                                 'employee_id',
                                 'check_in',
                                 'check_out',
                                 'created_at',
                                 'updated_at',
                             ]
                         ],
                         'leave_requests' => [
                             '*' => [
                                 'id',
                                 'employee_id',
                                 'from_date',
                                 'to_date',
                                 'status',
                                 'created_at',
                                 'updated_at',
                             ]
                         ],
                         'created_at',
                         'updated_at',
                     ]
                 ]);

        $response->assertJsonFragment([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'name' => 'Engineering', // Department name
        ]);

        $this->assertCount(5, $response->json('data.attendances'));
        $this->assertCount(5, $response->json('data.leave_requests'));

        // Expected queries:
        // 1. SELECT * FROM employees WHERE employees.id = ? LIMIT 1
        // 2. SELECT * FROM departments WHERE departments.id IN (?)
        // 3. SELECT * FROM attendances WHERE attendances.employee_id = ? ORDER BY created_at DESC LIMIT 5
        // 4. SELECT * FROM leave_requests WHERE leave_requests.employee_id = ? ORDER BY created_at DESC LIMIT 5
        $this->assertCount(4, $queries, 'Expected 4 queries for eager loading employee profile.');
    }

    /** @test */
    public function it_returns_404_if_employee_not_found()
    {
        $response = $this->getJson('/api/employees/999'); // Non-existent ID
        $response->assertStatus(404);
    }
}