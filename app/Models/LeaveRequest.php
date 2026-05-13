<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Import Carbon for date calculations

class LeaveRequest extends Model
{
    protected $fillable = ['employee_id','from_date','to_date','status', 'leave_type', 'reason'];

    protected $appends = ['duration_in_days']; // Add accessor to appends

    /**
     * Get the employee that owns the leave request.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the duration of the leave in days.
     *
     * @return int
     */
    public function getDurationInDaysAttribute()
    {
        $fromDate = Carbon::parse($this->from_date);
        $toDate = Carbon::parse($this->to_date);
        // Add 1 day to include both start and end date
        return $fromDate->diffInDays($toDate) + 1;
    }
}