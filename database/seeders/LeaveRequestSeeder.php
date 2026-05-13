<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LeaveRequest::factory(10)->create(); // Pending
        LeaveRequest::factory(5)->approved()->create(); // Approved
        LeaveRequest::factory(3)->rejected()->create(); // Rejected
    }
}