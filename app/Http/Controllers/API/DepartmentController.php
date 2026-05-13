<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentStoreRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index(){ return Department::all(); }
    public function store(DepartmentStoreRequest $r){ return Department::create($r->validated()); }
    public function show($id){ return Department::findOrFail($id); }
    public function update(DepartmentUpdateRequest $r,$id){ $d=Department::findOrFail($id); $d->update($r->validated()); return $d; }
    public function destroy($id){ Department::destroy($id); return response()->json(['deleted'=>true]); }
}