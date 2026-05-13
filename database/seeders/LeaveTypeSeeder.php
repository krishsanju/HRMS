<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            ['name' => 'Annual Leave', 'description' => 'Paid time off for vacation and personal matters.', 'accrual_rate' => 1.67, 'max_days' => 20],
            ['name' => 'Sick Leave', 'description' => 'Paid time off for illness or medical appointments.', 'accrual_rate' => 0.83, 'max_days' => 10],
            ['name' => 'Paternity Leave', 'description' => 'Paid time off for new fathers.', 'accrual_rate' => 0.0, 'max_days' => 5],
            ['name' => 'Maternity Leave', 'description' => 'Paid time off for new mothers.', 'accrual_rate' => 0.0, 'max_days' => 90],
            ['name' => 'Unpaid Leave', 'description' => 'Unpaid time off for personal reasons.', 'accrual_rate' => 0.0, 'max_days' => 30],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::factory()->create($leaveType);
        }
    }
}