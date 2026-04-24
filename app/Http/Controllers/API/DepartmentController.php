<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentStoreRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index(){ return Department::all(); }
    public function store(DepartmentStoreRequest $request){ return Department::create($request->validated()); }
    public function show($id){ return Department::findOrFail($id); }
    public function update(DepartmentUpdateRequest $request, Department $department){
        $department->update($request->validated());
        return $department;
    }
    public function destroy($id){ Department::destroy($id); return response()->json(['deleted'=>true]); }
}