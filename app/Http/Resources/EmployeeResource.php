<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\LeaveRequestResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // For 'summary or paginated list', we'll provide a summary of recent records.
        // Full pagination for these relations would typically be handled by dedicated endpoints.
        $attendanceSummaryLimit = $request->query('attendance_limit', 10);
        $leaveSummaryLimit = $request->query('leave_limit', 10);

        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'joining_date' => $this->joining_date,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'department' => DepartmentResource::make($this->whenLoaded('department')),
            'attendances' => AttendanceResource::collection(
                $this->whenLoaded('attendances', function () use ($attendanceSummaryLimit) {
                    return $this->attendances->sortByDesc('check_in')->take($attendanceSummaryLimit);
                })
            ),
            'leave_requests' => LeaveRequestResource::collection(
                $this->whenLoaded('leaveRequests', function () use ($leaveSummaryLimit) {
                    return $this->leaveRequests->sortByDesc('from_date')->take($leaveSummaryLimit);
                })
            ),
        ];
    }
}
