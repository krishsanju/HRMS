<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Http\Resources\EmployeeCountsResource;
use App\Http\Resources\LeaveRequestCountsResource;
use App\Http\Resources\AttendanceSummaryResource;
use Carbon\Carbon;

class HRMetricsController extends Controller
{
    /**
     * Retrieve total, active, and inactive employee counts.
     *
     * @return \App\Http\Resources\EmployeeCountsResource
     */
    public function getEmployeeCounts()
    {
        $activeEmployees = Employee::active()->count();
        $inactiveEmployees = Employee::inactive()->count();
        $totalEmployees = Employee::count();

        return new EmployeeCountsResource([
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'inactive_employees' => $inactiveEmployees,
        ]);
    }

    /**
     * Retrieve counts of pending, approved, and rejected leave requests.
     *
     * @return \App\Http\Resources\LeaveRequestCountsResource
     */
    public function getLeaveRequestCounts()
    {
        $pending = LeaveRequest::pending()->count();
        $approved = LeaveRequest::approved()->count();
        $rejected = LeaveRequest::rejected()->count();
        $total = LeaveRequest::count();

        return new LeaveRequestCountsResource([
            'total_requests' => $total,
            'pending_requests' => $pending,
            'approved_requests' => $approved,
            'rejected_requests' => $rejected,
        ]);
    }

    /**
     * Retrieve a summary of attendance data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\AttendanceSummaryResource
     */
    public function getAttendanceSummary(Request $request)
    {
        $query = Attendance::query();

        if ($request->has('start_date')) {
            $query->where('check_in', '>=', Carbon::parse($request->input('start_date'))->startOfDay());
        }
        if ($request->has('end_date')) {
            $query->where('check_in', '<=', Carbon::parse($request->input('end_date'))->endOfDay());
        }

        $attendanceRecords = $query->get();

        $dailySummary = $attendanceRecords->groupBy(function($date) {
            return Carbon::parse($date->check_in)->format('Y-m-d');
        })->map(function ($dayRecords) {
            $checkIns = $dayRecords->count();
            $checkOuts = $dayRecords->whereNotNull('check_out')->count();
            $totalWorkingHours = $dayRecords->sum(function ($record) {
                return $record->working_hours; // Uses the accessor
            });

            return [
                'check_ins_count' => $checkIns,
                'check_outs_count' => $checkOuts,
                'total_working_hours' => round($totalWorkingHours, 2),
            ];
        });

        return new AttendanceSummaryResource([
            'daily_summary' => $dailySummary->toArray(),
            'total_check_ins_period' => $attendanceRecords->count(),
            'total_check_outs_period' => $attendanceRecords->whereNotNull('check_out')->count(),
            'total_working_hours_period' => round($attendanceRecords->sum('working_hours'), 2),
        ]);
    }
}