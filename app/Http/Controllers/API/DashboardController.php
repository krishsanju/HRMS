<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function metrics(Request $request)
    {
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();
        $approvedLeaveRequests = LeaveRequest::where('status', 'approved')->count();
        $totalDepartments = Department::count();

        // Today's check-ins
        $todayCheckIns = Attendance::whereDate('check_in', today())->count();

        // You can add more complex metrics here, e.g., recent activities, attendance summaries
        $recentActivities = [
            ['id' => 1, 'type' => 'Leave Approved', 'description' => "John Doe's vacation leave approved.", 'date' => now()->subHours(2)->diffForHumans()],
            ['id' => 2, 'type' => 'New Employee', 'description' => 'Jane Smith joined Engineering.', 'date' => now()->subDay()->diffForHumans()],
        ];

        return response()->json([
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'approvedLeaveRequests' => $approvedLeaveRequests,
            'todayCheckIns' => $todayCheckIns,
            'totalDepartments' => $totalDepartments,
            'recentActivities' => $recentActivities, // Placeholder
        ]);
    }
}
