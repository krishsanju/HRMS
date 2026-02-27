<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Http\Resources\EmployeeResource; // Import the resource

class EmployeeController extends Controller
{
    public function index(){ return Employee::all(); } // This will need to be updated for #HRMS-115 and #HRMS-114 later
    public function store(Request $r){ return Employee::create($r->all()); }
    public function show($id)
    {
        $employee = Employee::with(['department', 'attendances', 'leaveRequests'])->findOrFail($id);
        return new EmployeeResource($employee);
    }
    public function update(Request $r,$id){ $e=Employee::findOrFail($id); $e->update($r->all()); return $e; }
    public function destroy($id){ Employee::destroy($id); return response()->json(['deleted'=>true]); }
}
