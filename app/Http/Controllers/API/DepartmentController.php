<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Http\Resources\DepartmentResource;

class DepartmentController extends Controller
{
    public function index(){ 
        return DepartmentResource::collection(Department::all()); 
    }
    public function store(Request $r){ 
        $department = Department::create($r->all()); 
        return new DepartmentResource($department);
    }
    public function show($id){ 
        return new DepartmentResource(Department::findOrFail($id)); 
    }
    public function update(Request $r,$id){ 
        $d=Department::findOrFail($id); 
        $d->update($r->all()); 
        return new DepartmentResource($d); 
    }
    public function destroy($id){ 
        Department::destroy($id); 
        return response()->json(['deleted'=>true]); 
    }
}