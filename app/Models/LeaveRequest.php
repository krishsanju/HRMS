<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Added

class LeaveRequest extends Model
{
    use HasFactory; // Added
    protected $fillable = ['employee_id','from_date','to_date','status'];

    /**
     * Get the employee that owns the leave request.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}