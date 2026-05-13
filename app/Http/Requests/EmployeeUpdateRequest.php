<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization will be handled by #HRMS-111 later
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee'); // Assuming route parameter is 'employee' for resource routes
        return [
            'employee_code' => ['sometimes', 'string', 'max:255', Rule::unique('employees', 'employee_code')->ignore($employeeId)],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employeeId)],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'joining_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive', 'terminated'])],
        ];
    }
}