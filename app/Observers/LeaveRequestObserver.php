<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\LeaveRequest;

class LeaveRequestObserver
{
    public function created(LeaveRequest $leaveRequest): void
    {
        Activity::create([
            'user_id' => $leaveRequest->employee->user->id ?? auth()->id(), // Prefer employee's user, fallback to admin
            'activity_type' => 'leave_request_submitted',
            'subject_id' => $leaveRequest->id,
            'subject_type' => LeaveRequest::class,
        ]);
    }

    public function updated(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->isDirty('status')) {
            $activityType = match ($leaveRequest->status) {
                'approved' => 'leave_request_approved',
                'rejected' => 'leave_request_rejected',
                default => null,
            };

            if ($activityType) {
                Activity::create([
                    'user_id' => auth()->id(), // The admin who approved/rejected
                    'activity_type' => $activityType,
                    'subject_id' => $leaveRequest->id,
                    'subject_type' => LeaveRequest::class,
                ]);
            }
        }
    }
}