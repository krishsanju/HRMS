<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Retrieve the total number of active/inactive employees.
     */
    public function employeeCounts(): JsonResponse
    {
        $activeEmployees = Employee::where('status', 'active')->count();
        $inactiveEmployees = Employee::where('status', 'inactive')->count(); // Assuming 'inactive' is a possible status

        return response()->json([
            'active' => $activeEmployees,
            'inactive' => $inactiveEmployees,
            'total' => $activeEmployees + $inactiveEmployees,
        ]);
    }

    /**
     * Retrieve the count of pending, approved, and rejected leave requests.
     */
    public function leaveRequestCounts(): JsonResponse
    {
        $pending = LeaveRequest::where('status', 'pending')->count();
        $approved = LeaveRequest::where('status', 'approved')->count();
        $rejected = LeaveRequest::where('status', 'rejected')->count();

        return response()->json([
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'total' => $pending + $approved + $rejected,
        ]);
    }

    /**
     * Retrieve a summary of attendance data for today.
     */
    public function attendanceSummary(): JsonResponse
    {
        $today = now()->toDateString();

        $checkIns = Attendance::whereDate('check_in', $today)->count();
        $checkOuts = Attendance::whereDate('check_out', $today)->whereNotNull('check_out')->count();

        // Calculate total working hours for completed records today
        $totalWorkingMinutes = Attendance::whereDate('check_in', $today)
            ->whereNotNull('check_out')
            ->select(DB::raw('SUM(TIMESTAMPDIFF(MINUTE, check_in, check_out)) as total_minutes'))
            ->value('total_minutes');

        $totalWorkingHours = round($totalWorkingMinutes / 60, 2); // Convert minutes to hours

        return response()->json([
            'date' => $today,
            'check_ins_today' => $checkIns,
            'check_outs_today' => $checkOuts,
            'total_working_hours_today' => $totalWorkingHours,
        ]);
    }
}