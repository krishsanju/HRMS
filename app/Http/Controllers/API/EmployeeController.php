<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        // Whitelist of sortable columns to prevent arbitrary column sorting.
        $sortableColumns = ['first_name', 'employee_code', 'email', 'status', 'position', 'joining_date'];
        $sortableRelations = ['department_name'];

        $sortBy = $request->query('sort_by', 'first_name'); // Default sort column
        $sortOrder = $request->query('sort_order', 'asc');   // Default sort order

        // Validate sortOrder
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        $query = Employee::query();

        // Handle sorting
        if (in_array($sortBy, $sortableColumns)) {
            $query->orderBy($sortBy, $sortOrder);
            // Add secondary sort for name for better user experience
            if ($sortBy === 'first_name') {
                $query->orderBy('last_name', $sortOrder);
            }
        } elseif ($sortBy === 'department_name' && in_array($sortBy, $sortableRelations)) {
            // Handle sorting by a related model's column
            $query->join('departments', 'employees.department_id', '=', 'departments.id')
                  ->orderBy('departments.name', $sortOrder)
                  ->select('employees.*'); // Avoid column name conflicts
        } else {
            // Fallback to default sort if sortBy is invalid
            $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc');
        }

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
            $query->where('employees.department_id', $request->department_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('employees.status', $request->status);
        }

        // Eager load relationships
        $query->with('department');

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