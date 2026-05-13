<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRejectRequest;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with('employee.department');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employee_id
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Search by employee name
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->whereHas('employee', function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', $searchTerm)
                    ->orWhere('last_name', 'like', $searchTerm);
            });
        }

        // Sorting
        if ($request->has('sort_by') && $request->has('sort_direction')) {
            $query->orderBy($request->sort_by, $request->sort_direction);
        } else {
            $query->orderBy('from_date', 'desc');
        }

        return $query->paginate($request->get('per_page', 15));
    }

    public function show($id)
    {
        return LeaveRequest::with('employee.department')->findOrFail($id);
    }

    public function apply(Request $r)
    {
        // Basic validation, more robust validation should be in a FormRequest
        $validatedData = $r->validate([
            'employee_id' => 'required|exists:employees,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|max:500',
            'leave_type' => 'required|string|max:255',
        ]);

        $validatedData['status'] = 'pending';

        return LeaveRequest::create($validatedData);
    }

    public function approve($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update(['status' => 'approved', 'rejection_reason' => null]);
        return $leave;
    }

    public function reject(LeaveRejectRequest $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update(['status' => 'rejected', 'rejection_reason' => $request->rejection_reason]);
        return $leave;
    }
}