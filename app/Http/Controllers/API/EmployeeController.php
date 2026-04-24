<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index(){ return Employee::all(); }
    public function store(EmployeeStoreRequest $request){ return Employee::create($request->validated()); }
    public function show($id){ return Employee::findOrFail($id); }
    public function update(EmployeeUpdateRequest $request, Employee $employee){
        $employee->update($request->validated());
        return $employee;
    }
    public function destroy($id){ Employee::destroy($id); return response()->json(['deleted'=>true]); }
}