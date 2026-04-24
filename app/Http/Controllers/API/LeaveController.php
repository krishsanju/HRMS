<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\Employee; // Assuming Employee model might be needed for Auth check, though Auth::id() might suffice
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse; // For explicit JsonResponse return types

class LeaveController extends Controller
{
    public function apply(Request $r): JsonResponse
    {
        // Assuming validation is handled elsewhere or will be added.
        // For now, directly create.
        $leaveRequest = LeaveRequest::create($r->all());
        return response()->json($leaveRequest, 201); // 201 Created
    }

    public function approve($id): JsonResponse
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update(['status' => 'approved']);
        return response()->json($leave);
    }

    /**
     * Cancel an employee's leave request.
     *
     * @param Request $request
     * @param int $id The ID of the leave request to cancel.
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // AC2: Employee who applied the leave can only cancel the leave
        // Assuming Auth::id() returns the ID of the authenticated employee.
        // If the authenticated user's ID is not directly the employee_id,
        // this logic would need to be adjusted (e.g., Auth::user()->employee->id).
        // For this context, we assume Auth::id() directly maps to employee_id.
        if (Auth::id() !== $leaveRequest->employee_id) {
            return response()->json(['message' => 'You are not authorized to cancel this leave request.'], 403); // Forbidden
        }

        // AC3: Cannot cancel the approved leave
        if ($leaveRequest->status === 'approved') {
            return response()->json(['message' => 'Approved leave requests cannot be cancelled.'], 400); // Bad Request
        }

        // Also cannot cancel if already rejected or cancelled (though AC doesn't explicitly state this, it's good practice)
        if ($leaveRequest->status === 'rejected') {
            return response()->json(['message' => 'Rejected leave requests cannot be cancelled.'], 400);
        }
        if ($leaveRequest->status === 'cancelled') {
            return response()->json(['message' => 'This leave request has already been cancelled.'], 400);
        }

        $leaveRequest->status = 'cancelled';
        $leaveRequest->save();

        return response()->json(['message' => 'Leave request cancelled successfully.', 'leave_request' => $leaveRequest]);
    }
}