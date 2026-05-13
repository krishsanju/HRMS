<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceStoreRequest;
use App\Http\Requests\AttendanceUpdateRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee.department');

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->has('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->has('date_from')) {
            $query->whereDate('check_in', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('check_in', '<=', $request->date_to);
        }

        // Basic status filtering (e.g., 'present' if check_out exists, 'absent' if no record for day, 'pending' if check_in but no check_out)
        // This would require more complex logic, for now, we'll just filter by check_out presence
        if ($request->has('status')) {
            if ($request->status === 'present') {
                $query->whereNotNull('check_out');
            } elseif ($request->status === 'pending_checkout') {
                $query->whereNull('check_out');
            }
        }

        return $query->latest('check_in')->paginate($request->get('per_page', 15));
    }

    public function store(AttendanceStoreRequest $request)
    {
        $attendance = Attendance::create($request->validated());
        return response()->json($attendance, 201);
    }

    public function show($id)
    {
        return Attendance::with('employee.department')->findOrFail($id);
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update($request->validated());
        return response()->json($attendance);
    }

    public function destroy($id)
    {
        Attendance::destroy($id);
        return response()->json(['deleted' => true], 204);
    }

    public function checkIn(Request $r)
    {
        // This method is kept for employee self-service, but admin panel will use store()
        return Attendance::create(['employee_id' => $r->employee_id, 'check_in' => now()]);
    }

    public function checkOut(Request $r)
    {
        // This method is kept for employee self-service, but admin panel will use update() for manual edits
        $att = Attendance::where('employee_id', $r->employee_id)->latest()->first();
        if ($att) {
            $att->update(['check_out' => now()]);
        }
        return $att;
    }
}