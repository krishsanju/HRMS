<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = ['Annual Leave', 'Sick Leave', 'Maternity Leave', 'Paternity Leave', 'Unpaid Leave'];
        foreach ($leaveTypes as $type) {
            LeaveType::factory()->create(['name' => $type]);
        }
    }
}