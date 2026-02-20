<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index(){ return Department::all(); }
    public function store(Request $r){ return Department::create($r->all()); }
    public function show($id){ return Department::findOrFail($id); }
    public function update(Request $r,$id){ $d=Department::findOrFail($id); $d->update($r->all()); return $d; }
    public function destroy($id){ Department::destroy($id); return response()->json(['deleted'=>true]); }
}
