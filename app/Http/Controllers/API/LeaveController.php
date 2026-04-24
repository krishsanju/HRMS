<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveApplyRequest;
use App\Http\Requests\LeaveApproveRequest;
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    public function apply(LeaveApplyRequest $request){
        return LeaveRequest::create($request->validated());
    }

    public function approve(LeaveApproveRequest $request, LeaveRequest $leaveRequest){
        $leaveRequest->update(['status' => 'approved']);
        return $leaveRequest;
    }
}