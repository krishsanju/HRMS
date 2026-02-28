<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Http\Resources\EmployeeResource; // Added

class EmployeeController extends Controller
{
    public function index(){ return Employee::all(); }
    public function store(Request $r){ return Employee::create($r->all()); }
    public function show($id)
    {
        // Modified to eager load relations and return EmployeeResource
        $employee = Employee::with([
            'department',
            'attendances' => fn($query) => $query->latest()->take(5), // Latest 5 attendance records
            'leaveRequests' => fn($query) => $query->latest()->take(5) // Latest 5 leave requests
        ])->findOrFail($id);

        return new EmployeeResource($employee);
    }
    public function update(Request $r,$id){ $e=Employee::findOrFail($id); $e->update($r->all()); return $e; }
    public function destroy($id){ Employee::destroy($id); return response()->json(['deleted'=>true]); }
}