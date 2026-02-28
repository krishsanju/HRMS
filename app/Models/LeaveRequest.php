<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = ['employee_id','from_date','to_date','status', 'reason']; // Added 'reason' based on #HRMS-117 and #HRMS-109

    /**
     * Scope a query to only include pending leave requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved leave requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected leave requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}