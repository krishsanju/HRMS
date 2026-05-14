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
        $leaveTypes = [
            ['name' => 'Annual Leave'],
            ['name' => 'Sick Leave'],
            ['name' => 'Maternity Leave'],
            ['name' => 'Paternity Leave'],
            ['name' => 'Unpaid Leave'],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::factory()->create($type);
        }
    }
}