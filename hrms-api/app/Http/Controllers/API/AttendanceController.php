<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function checkIn(Request $r){
        return Attendance::create(['employee_id'=>$r->employee_id,'check_in'=>now()]);
    }

    public function checkOut(Request $r){
        $att=Attendance::where('employee_id',$r->employee_id)->latest()->first();
        $att->update(['check_out'=>now()]);
        return $att;
    }
}
