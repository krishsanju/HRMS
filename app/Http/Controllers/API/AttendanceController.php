<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Http\Resources\AttendanceResource;

class AttendanceController extends Controller
{
    public function checkIn(Request $r){
        $attendance = Attendance::create(['employee_id'=>$r->employee_id,'check_in'=>now()]);
        return new AttendanceResource($attendance);
    }

    public function checkOut(Request $r){
        $att=Attendance::where('employee_id',$r->employee_id)->latest()->first();
        $att->update(['check_out'=>now()]);
        return new AttendanceResource($att);
    }
}