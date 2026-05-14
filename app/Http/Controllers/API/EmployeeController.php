<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query()->with('department');

        // Search by name, email or employee code
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('employee_code', 'like', $searchTerm);
            });
        }

        // Filter by department_id
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'first_name');
        $sortOrder = $request->input('sort_order', 'asc');

        // Whitelist of sortable columns on the employees table
        $sortableColumns = ['employee_code', 'first_name', 'position', 'hire_date'];

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        if ($sortBy === 'department_name') {
            $query->join('departments', 'employees.department_id', '=', 'departments.id')
                  ->orderBy('departments.name', $sortOrder)
                  ->select('employees.*'); // Select only employee columns to avoid ambiguity
        } elseif (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $sortOrder);
            // Add secondary sort for name
            if ($sortBy === 'first_name') {
                $query->orderBy('last_name', $sortOrder);
            }
        } else {
            // Default sort
            $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc');
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
            'position' => 'required|string|max:255',
            'hire_date' => 'required|date',
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
            'position' => 'required|string|max:255',
            'hire_date' => 'required|date',
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