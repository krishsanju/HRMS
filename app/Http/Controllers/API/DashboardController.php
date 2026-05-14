<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Activity;
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

        return response()->json([
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'approvedLeaveRequests' => $approvedLeaveRequests,
            'todayCheckIns' => $todayCheckIns,
            'totalDepartments' => $totalDepartments,
        ]);
    }

    public function recentActivities(Request $request)
    {
        $activities = Activity::with(['user', 'subject'])
            ->latest()
            ->take(10)
            ->get();

        return response()->json($activities);
    }
}