<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth; // Added for authentication check

class LeaveController extends Controller
{
    public function apply(Request $r){
        // Assuming validation for employee_id, from_date, to_date, and reason (from HRMS-119)
        // is handled elsewhere or will be added.
        return LeaveRequest::create($r->all());
    }

    public function approve($id){
        $leave = LeaveRequest::findOrFail($id);
        // Additional business logic for approval might be here (e.g., HR role check)
        $leave->update(['status' => 'approved']);
        return $leave;
    }

    public function cancel(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // AC2: Employee who applied the leave can only cancel
        // Assumption: Auth::id() directly corresponds to employee_id on LeaveRequest
        if (!Auth::check() || Auth::id() != $leaveRequest->employee_id) {
            return response()->json(['message' => 'Unauthorized to cancel this leave request.'], 403);
        }

        // AC2, AC3: Cannot cancel an approved leave
        if ($leaveRequest->status === 'approved') {
            return response()->json(['message' => 'Cannot cancel an approved leave request.'], 400);
        }

        // Prevent cancelling if already rejected or cancelled (good practice, beyond strict ACs)
        if ($leaveRequest->status === 'rejected' || $leaveRequest->status === 'cancelled') {
            return response()->json(['message' => 'Leave request is already in a final state and cannot be cancelled.'], 400);
        }

        $leaveRequest->status = 'cancelled';
        $leaveRequest->save();

        return response()->json($leaveRequest, 200);
    }
}