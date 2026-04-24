<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceCheckInRequest;
use App\Http\Requests\AttendanceCheckOutRequest;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function checkIn(AttendanceCheckInRequest $request){
        return Attendance::create($request->validated() + ['check_in' => now()]);
    }

    public function checkOut(AttendanceCheckOutRequest $request){
        $att = Attendance::where('employee_id', $request->validated('employee_id'))->latest()->firstOrFail();
        $att->update(['check_out' => now()]);
        return $att;
    }
}