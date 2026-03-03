<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest; // Import the new Form Request
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    // Update the method signature to use the new Form Request
    public function apply(StoreLeaveRequestRequest $request)
    {
        // The request is automatically validated by StoreLeaveRequestRequest
        // Only validated data is passed to create, ensuring data integrity
        return LeaveRequest::create($request->validated());
    }

    public function approve($id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'approved']);
        return $leave;
    }
}