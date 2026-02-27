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
    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        // Business rule: A department cannot be deleted if it has employees assigned to it.
        if ($department->employees()->exists()) {
            return response()->json([
                'message' => 'Cannot delete department. Employees are still assigned to this department. Please reassign or remove them first.'
            ], 409); // 409 Conflict
        }

        $department->delete();
        return response()->json(['deleted'=>true]);
    }
}
