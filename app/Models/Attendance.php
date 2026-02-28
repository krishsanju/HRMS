<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Added

class Attendance extends Model
{
    use HasFactory; // Added
    protected $fillable = ['employee_id','check_in','check_out'];

    /**
     * Get the employee that owns the attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}