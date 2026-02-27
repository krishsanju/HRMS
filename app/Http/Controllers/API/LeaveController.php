<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    public function apply(Request $r){
        // Add validation for apply method as per business rules (e.g., reason field from HRMS-117)
        $r->validate([
            'employee_id' => 'required|exists:employees,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|min:10', // From HRMS-117 and Enhancement of Leave Request Feature
        ]);
        return LeaveRequest::create($r->all());
    }

    public function approve($id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'approved']);
        return $leave;
    }

    /**
     * Reject a specific leave request.
     */
    public function reject(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);

        // Validate optional rejection reason
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $leave->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return response()->json($leave, 200);
    }
}