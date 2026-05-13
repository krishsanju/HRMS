<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveApplyRequest;
use App\Http\Requests\LeaveApproveRequest;
use App\Http\Requests\LeaveRejectRequest;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    public function apply(LeaveApplyRequest $r){
        return LeaveRequest::create($r->validated());
    }

    public function approve(LeaveApproveRequest $r, $id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'approved']);
        return $leave;
    }

    public function reject(LeaveRejectRequest $r, $id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'rejected', 'rejection_reason' => $r->rejection_reason]);
        return $leave;
    }
}