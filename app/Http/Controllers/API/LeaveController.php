<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest; // Import the new Form Request
use Illuminate\Http\Request;
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    public function apply(StoreLeaveRequest $request){ // Use StoreLeaveRequest
        $leaveRequest = LeaveRequest::create($request->validated()); // Use validated data
        return response()->json($leaveRequest, 201); // Return JSON response with 201 status
    }

    public function approve($id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'approved']);
        return $leave;
    }
}