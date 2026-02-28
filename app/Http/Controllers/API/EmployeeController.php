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
    public function show($id)
    {
        $employee = Employee::with([
            'department',
            'attendances' => fn($query) => $query->latest()->take(5),
            'leaveRequests' => fn($query) => $query->latest()->take(5)
        ])->findOrFail($id);

        return new EmployeeResource($employee);
    }
    public function update(Request $r,$id){ $e=Employee::findOrFail($id); $e->update($r->all()); return $e; }
    public function destroy($id){ Employee::destroy($id); return response()->json(['deleted'=>true]); }
}