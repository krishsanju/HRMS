<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index(){ return Employee::all(); }
    public function store(EmployeeStoreRequest $r){ return Employee::create($r->validated()); }
    public function show($id){ return Employee::findOrFail($id); }
    public function update(EmployeeUpdateRequest $r,$id){ $e=Employee::findOrFail($id); $e->update($r->validated()); return $e; }
    public function destroy($id){ Employee::destroy($id); return response()->json(['deleted'=>true]); }
}