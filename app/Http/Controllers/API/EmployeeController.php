<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Http\Resources\EmployeeResource;

class EmployeeController extends Controller
{
    public function index(){ 
        // HRMS-115: Employee List with Search and Pagination API - returns paginated list
        return EmployeeResource::collection(Employee::paginate(15)); 
    }
    public function store(Request $r){ 
        $employee = Employee::create($r->all()); 
        return new EmployeeResource($employee);
    }
    public function show($id){ 
        // HRMS-120: Employee Profile API (Extended with Relations) - eager load relations
        return new EmployeeResource(Employee::findOrFail($id)->load('department', 'attendances', 'leaveRequests')); 
    }
    public function update(Request $r,$id){ 
        $e=Employee::findOrFail($id); 
        $e->update($r->all()); 
        return new EmployeeResource($e); 
    }
    public function destroy($id){ 
        Employee::destroy($id); 
        return response()->json(['deleted'=>true]); 
    }
}