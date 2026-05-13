<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization will be handled by #HRMS-111 later
    }

    public function rules(): array
    {
        return [
            'employee_code' => ['required', 'string', 'max:255', 'unique:employees,employee_code'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees,email'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'joining_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'terminated'])],
        ];
    }
}