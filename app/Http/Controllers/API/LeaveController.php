<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\Employee; // Import Employee model for filtering

class LeaveController extends Controller
{
    public function apply(Request $r){
        // Add validation for leave_type and reason as per business rules
        $r->validate([
            'employee_id' => 'required|exists:employees,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'leave_type' => 'required|string|max:255', // Assuming leave_type is a string
            'reason' => 'required|string|min:10', // As per "Enhancement of Leave Request Feature with 'Reason' Field"
        ]);
        return LeaveRequest::create($r->all());
    }

    public function approve($id){
        $leave=LeaveRequest::findOrFail($id);
        $leave->update(['status'=>'approved']);
        return $leave;
    }

    /**
     * Display a listing of the leave requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // HR Admin level authentication would be applied via middleware on the route.
        // For now, assuming authenticated HR Admin access.

        $query = LeaveRequest::with(['employee.department']);

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['approved', 'pending', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Filter by employee name
        if ($request->has('employee_name')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->employee_name . '%')
                  ->orWhere('last_name', 'like', '%' . $request->employee_name . '%');
            });
        }

        // Filter by department ID
        if ($request->has('department_id')) {
            $query->whereHas('employee.department', function ($q) use ($request) {
                $q->where('id', $request->department_id);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at'); // Default sort by submission date
        $sortOrder = $request->get('sort_order', 'desc'); // Default sort order descending

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc'; // Sanitize sort order
        }

        switch ($sortBy) {
            case 'employee_name':
                // Join with employees table to sort by name
                $query->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
                      ->orderBy('employees.first_name', $sortOrder)
                      ->select('leave_requests.*'); // Select all from leave_requests to avoid column ambiguity
                break;
            case 'submission_date':
                $query->orderBy('leave_requests.created_at', $sortOrder);
                break;
            case 'start_date':
                $query->orderBy('leave_requests.from_date', $sortOrder);
                break;
            default:
                $query->orderBy('leave_requests.created_at', $sortOrder);
                break;
        }

        $perPage = $request->get('per_page', 10);
        $leaves = $query->paginate($perPage);

        // Transform the collection to include all required fields
        $transformedLeaves = $leaves->through(function ($leave) {
            return [
                'id' => $leave->id,
                'employee_name' => $leave->employee->full_name,
                'employee_id' => $leave->employee->employee_code, // Using employee_code as Employee ID
                'department' => $leave->employee->department ? $leave->employee->department->name : 'N/A',
                'leave_type' => $leave->leave_type, // Assuming leave_type column exists
                'from_date' => $leave->from_date,
                'to_date' => $leave->to_date,
                'duration_in_days' => $leave->duration_in_days,
                'submission_date' => $leave->created_at->toDateTimeString(), // Format as string
                'status' => $leave->status,
                'reason' => $leave->reason, // Include reason as per business knowledge
            ];
        });

        return response()->json($transformedLeaves);
    }
}