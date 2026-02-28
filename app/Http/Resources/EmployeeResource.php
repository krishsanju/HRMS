<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'joining_date' => $this->joining_date,
            'status' => $this->status,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
            'leave_requests' => LeaveRequestResource::collection($this->whenLoaded('leaveRequests')),
        ];
    }
}