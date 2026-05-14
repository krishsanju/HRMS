<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\Employee;

class EmployeeObserver
{
    public function created(Employee $employee): void
    {
        Activity::create([
            'user_id' => auth()->id(),
            'activity_type' => 'employee_created',
            'subject_id' => $employee->id,
            'subject_type' => Employee::class,
        ]);
    }

    public function updated(Employee $employee): void
    {
        Activity::create([
            'user_id' => auth()->id(),
            'activity_type' => 'employee_updated',
            'subject_id' => $employee->id,
            'subject_type' => Employee::class,
        ]);
    }
}