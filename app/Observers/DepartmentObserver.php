<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\Department;

class DepartmentObserver
{
    public function created(Department $department): void
    {
        Activity::create([
            'user_id' => auth()->id(),
            'activity_type' => 'department_created',
            'subject_id' => $department->id,
            'subject_type' => Department::class,
        ]);
    }
}