<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceCheckInRequest;
use App\Http\Requests\AttendanceCheckOutRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function checkIn(AttendanceCheckInRequest $r){
        return Attendance::create(['employee_id'=>$r->employee_id,'check_in'=>now()]);
    }

    public function checkOut(AttendanceCheckOutRequest $r){
        $att=Attendance::where('employee_id',$r->employee_id)->latest()->first();
        if ($att) {
            $att->update(['check_out'=>now()]);
        } else {
            return response()->json(['message' => 'No active check-in found for this employee.'], 404);
        }
        return $att;
    }
}