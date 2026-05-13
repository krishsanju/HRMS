<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('department');

        // Search by name or email
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', $searchTerm)
                    ->orWhere('last_name', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm);
            });
        }

        // Filter by department_id
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        if ($request->has('sort_by') && $request->has('sort_direction')) {
            $query->orderBy($request->sort_by, $request->sort_direction);
        } else {
            $query->orderBy('last_name')->orderBy('first_name');
        }

        return $query->paginate($request->get('per_page', 15));
    }

    public function store(Request $r)
    {
        // Basic validation, more robust validation should be in a FormRequest
        $validatedData = $r->validate([
            'employee_code' => 'required|unique:employees,employee_code|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'joining_date' => 'required|date',
            'status' => 'required|in:active,inactive,on_leave,terminated',
        ]);

        return Employee::create($validatedData);
    }

    public function show($id)
    {
        return Employee::with(['department', 'attendances', 'leaveRequests'])->findOrFail($id);
    }

    public function update(Request $r, $id)
    {
        $employee = Employee::findOrFail($id);

        // Basic validation, more robust validation should be in a FormRequest
        $validatedData = $r->validate([
            'employee_code' => 'required|unique:employees,employee_code,' . $id . '|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id . '|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'joining_date' => 'required|date',
            'status' => 'required|in:active,inactive,on_leave,terminated',
        ]);

        $employee->update($validatedData);
        return $employee;
    }

    public function destroy($id)
    {
        Employee::destroy($id);
        return response()->json(['deleted' => true], 204);
    }
}