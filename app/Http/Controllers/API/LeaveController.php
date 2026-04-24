<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class LeaveController extends Controller
{
    public function apply(Request $r)
    {
        return LeaveRequest::create($r->all());
    }

    public function approve($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update(['status' => 'approved']);
        return $leave;
    }

    /**
     * Cancel an employee's leave request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        // AC2: Employee who applied the leave can only cancel the leave
        // Assuming Auth::id() returns the primary key of the authenticated user,
        // which corresponds to the employee_id in the LeaveRequest model.
        if (Auth::id() !== $leaveRequest->employee_id) {
            return response()->json(['message' => 'You are not authorized to cancel this leave request.'], 403);
        }

        // AC3: Cannot cancel the approved leave
        if ($leaveRequest->status === 'approved') {
            return response()->json(['message' => 'Approved leave requests cannot be cancelled.'], 409);
        }

        // Update status to 'cancelled'
        $leaveRequest->status = 'cancelled';
        $leaveRequest->save();

        return response()->json($leaveRequest, 200);
    }
}