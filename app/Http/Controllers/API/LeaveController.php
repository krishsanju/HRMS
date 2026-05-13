<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Http\Resources\LeaveRequestResource;

class LeaveController extends Controller
{
    public function apply(Request $r){
        $leaveRequest = LeaveRequest::create($r->all());
        return new LeaveRequestResource($leaveRequest);
    }

    public function approve($id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'approved']);
        return new LeaveRequestResource($leave);
    }
}