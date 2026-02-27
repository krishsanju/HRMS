<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Http\Resources\EmployeeResource; // Import the new EmployeeResource

class EmployeeController extends Controller
{
    public function index(){ return Employee::all(); } // This should ideally use a resource collection too, but not in scope for this task.
    public function store(Request $r){ return Employee::create($r->all()); } // This should ideally use a resource too, but not in scope for this task.

    public function show(string $id)
    {
        $employee = Employee::with([
            'department',
            'attendances' => function ($query) {
                $query->latest('check_in')->take(5); // Constrain to latest 5 attendance records
            },
            'leaveRequests' => function ($query) {
                $query->latest('from_date')->take(5); // Constrain to latest 5 leave requests
            }
        ])->findOrFail($id);

        return new EmployeeResource($employee);
    }

    public function update(Request $r,$id){ $e=Employee::findOrFail($id); $e->update($r->all()); return $e; } // This should ideally use a resource too, but not in scope for this task.
    public function destroy($id){ Employee::destroy($id); return response()->json(['deleted'=>true]); }
}