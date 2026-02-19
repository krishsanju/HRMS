<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index(){ return Employee::all(); }
    public function store(Request $r){ return Employee::create($r->all()); }
    public function show($id){ return Employee::findOrFail($id); }
    public function update(Request $r,$id){ $e=Employee::findOrFail($id); $e->update($r->all()); return $e; }
    public function destroy($id){ Employee::destroy($id); return response()->json(['deleted'=>true]); }
}
