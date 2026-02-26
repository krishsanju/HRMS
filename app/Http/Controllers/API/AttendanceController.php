```php
<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Validator;
use App\Models\Employee;

class AttendanceController extends Controller
{
    public function checkIn(Request $request){
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $activeAttendance = Attendance::where('employee_id', $request->employee_id)
            ->whereNull('check_out')
            ->first();

        if ($activeAttendance) {
            return response()->json([
                'message' => 'Employee is already checked in.',
                'attendance' => $activeAttendance
            ], 409); // 409 Conflict
        }

        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'check_in'    => now()
        ]);

        return response()->json($attendance, 201);
    }

    public function checkOut(Request $request){
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = Attendance::where('employee_id', $request->employee_id)
                                ->whereNull('check_out')
                                ->latest('check_in')
                                ->first();

        if (!$attendance) {
            return response()->json(['message' => 'No active check-in found for this employee.'], 404);
        }

        $attendance->update(['check_out' => now()]);
        return response()->json($attendance);
    }

    /**
     * Get the current attendance status for a specific employee.
     */
    public function status(Employee $employee)
    {
        $activeAttendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if ($activeAttendance) {
            return response()->json([
                'status' => 'checked-in',
                'details' => $activeAttendance,
            ]);
        }

        return response()->json(['status' => 'checked-out']);
    }

    /**
     * Get the attendance history for a specific employee.
     */
    public function history(Employee $employee, Request $request)
    {
        $history = Attendance::where('employee_id', $employee->id)
            ->orderBy('check_in', 'desc')
            ->paginate($request->query('per_page', 15));

        return response()->json($history);
    }
}
```