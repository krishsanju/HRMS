<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        // Whitelist of sortable columns to prevent arbitrary column sorting.
        $sortableColumns = ['first_name', 'employee_code', 'position', 'hire_date', 'department_name'];
        $sortBy = $request->input('sort_by', 'first_name');
        $sortOrder = $request->input('sort_order', 'asc');

        // Validate sort_by against the whitelist
        if (!in_array($sortBy, $sortableColumns)) {
            $sortBy = 'first_name';
        }

        // Validate sort_order
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        // Handle sorting by department name, which requires a join
        if ($sortBy === 'department_name') {
            $query->join('departments', 'employees.department_id', '=', 'departments.id')
                  ->orderBy('departments.name', $sortOrder)
                  ->select('employees.*'); // Select only employee columns to avoid conflicts
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Eager load department after sorting is established
        $query->with('department');

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