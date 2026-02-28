<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Http\Requests\UpdatePasswordRequest; // Import the new request

class EmployeeController extends Controller
{
    public function index(){ return Employee::all(); }
    public function store(Request $r){ return Employee::create($r->all()); }
    public function show($id){ return Employee::findOrFail($id); }
    public function update(Request $r,$id){ $e=Employee::findOrFail($id); $e->update($r->all()); return $e; }
    public function destroy($id){ Employee::destroy($id); return response()->json(['deleted'=>true]); }

    /**
     * Update the authenticated employee's password.
     *
     * @param  \App\Http\Requests\UpdatePasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $employee = auth('sanctum')->user(); // Get the currently authenticated employee

        $employee->password = $request->password; // Mutator will hash it
        $employee->save();

        return response()->json(['message' => 'Password has been successfully updated.'], 200);
    }
}