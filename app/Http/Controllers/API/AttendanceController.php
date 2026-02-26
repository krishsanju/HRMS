```php
<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|integer',
        ]);

        $openAttendance = Attendance::where('employee_id', $request->employee_id)
            ->whereNull('check_out')
            ->first();

        if ($openAttendance) {
            return response()->json(['message' => 'Employee is already checked in.'], 409); // 409 Conflict
        }

        $attendance = Attendance::create([
            'employee_id' => $request->employee_id,
            'check_in' => now(),
        ]);

        return response()->json($attendance, 201);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|integer',
        ]);

        $attendance = Attendance::where('employee_id', $request->employee_id)
            ->whereNull('check_out')
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Employee is not currently checked in.'], 404);
        }

        $attendance->update(['check_out' => now()]);

        return response()->json($attendance);
    }

    /**
     * Get attendance history for a specific employee.
     */
    public function getHistory(int $employee_id): JsonResponse
    {
        $attendances = Attendance::where('employee_id', $employee_id)
            ->latest('check_in')
            ->paginate(20);

        return response()->json($attendances);
    }

    /**
     * Get the current attendance status for a specific employee.
     */
    public function getStatus(int $employee_id): JsonResponse
    {
        $latestAttendance = Attendance::where('employee_id', $employee_id)->latest('check_in')->first();

        if (!$latestAttendance) {
            return response()->json(['status' => 'not_found', 'message' => 'No records for this employee.'], 404);
        }

        $status = $latestAttendance->check_out === null ? 'checked_in' : 'checked_out';

        return response()->json([
            'status' => $status,
            'record' => $latestAttendance,
        ]);
    }
}
```