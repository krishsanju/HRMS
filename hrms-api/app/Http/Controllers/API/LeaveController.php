<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    public function apply(Request $r){
        return LeaveRequest::create($r->all());
    }

    public function approve($id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'approved']);
        return $leave;
    }
}
