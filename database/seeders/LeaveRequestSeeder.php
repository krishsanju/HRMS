<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $leaveTypes = LeaveType::all();

        if ($employees->isEmpty() || $leaveTypes->isEmpty()) {
            $this->command->warn('No employees or leave types found. Please run EmployeeSeeder and LeaveTypeSeeder first.');
            return;
        }

        // Generate 10-15 sample leave requests
        LeaveRequest::factory()->count(15)->make()->each(function ($leaveRequest) use ($employees, $leaveTypes) {
            $leaveRequest->employee_id = $employees->random()->id;
            $leaveRequest->leave_type_id = $leaveTypes->random()->id;
            $leaveRequest->save();
        });
    }
}