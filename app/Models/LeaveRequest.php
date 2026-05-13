<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'from_date',
        'to_date',
        'status',
        'reason',
        'leave_type',
        'rejection_reason'
    ];

    /**
     * Get the employee that made the leave request.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Add any necessary casts for dates if not already handled by Laravel
    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];
}