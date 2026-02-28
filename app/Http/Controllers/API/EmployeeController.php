<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Http\Resources\EmployeeResource;

class EmployeeController extends Controller
{
    public function index(){ return Employee::all(); }
    public function store(Request $r){ return Employee::create($r->all()); }

    /**
     * Display the specified employee profile with related data.
     */
    public function show($id)
    {
        $employee = Employee::with('department')
                            ->with(['attendances' => fn($query) => $query->latest('check_in')->take(5)])
                            ->with(['leaveRequests' => fn($query) => $query->latest('from_date')->take(5)])
                            ->findOrFail($id);

        return new EmployeeResource($employee);
    }

    public function update(Request $r,$id){ $e=Employee::findOrFail($id); $e->update($r->all()); return $e; }
    public function destroy($id){ Employee::destroy($id); return response()->json(['deleted'=>true]); }
}