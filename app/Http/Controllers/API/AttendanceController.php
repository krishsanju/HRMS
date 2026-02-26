```php
<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function checkIn(Request $r){
        $validator = Validator::make($r->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $activeAttendance = Attendance::where('employee_id', $r->employee_id)
                                      ->whereNull('check_out')
                                      ->first();

        if ($activeAttendance) {
            return response()->json(['message' => 'Employee is already checked in.'], 409);
        }

        $attendance = Attendance::create([
            'employee_id' => $r->employee_id,
            'check_in'    => now(),
        ]);

        return response()->json([
            'message' => 'Check-in successful.',
            'data'    => $attendance,
        ], 201);
    }

    public function checkOut(Request $r){
        $validator = Validator::make($r->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = Attendance::where('employee_id', $r->employee_id)
                                ->whereNull('check_out')
                                ->latest('check_in')
                                ->first();

        if (!$attendance) {
            return response()->json(['message' => 'No active check-in found for this employee.'], 404);
        }

        $attendance->update(['check_out' => now()]);

        return response()->json([
            'message' => 'Check-out successful.',
            'data'    => $attendance,
        ]);
    }

    public function history(Employee $employee)
    {
        $attendances = Attendance::where('employee_id', $employee->id)
                                 ->latest('check_in')
                                 ->paginate(20);

        return response()->json($attendances);
    }

    public function status(Employee $employee)
    {
        $latestAttendance = Attendance::where('employee_id', $employee->id)
                                       ->latest('check_in')
                                       ->first();

        if (!$latestAttendance) {
            return response()->json([
                'status'  => 'never_recorded',
                'message' => 'No attendance record found for this employee.',
            ], 404);
        }

        $status = $latestAttendance->check_out === null ? 'checked_in' : 'checked_out';

        return response()->json([
            'status' => $status,
            'data'   => $latestAttendance,
        ]);
    }
}
```