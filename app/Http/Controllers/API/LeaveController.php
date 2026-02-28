<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest; // Import the new Form Request
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    public function apply(StoreLeaveRequest $request)
    {
        $leaveRequest = LeaveRequest::create($request->validated());
        return response()->json($leaveRequest, 201);
    }

    public function approve($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update(['status' => 'approved']);
        return $leave;
    }
}