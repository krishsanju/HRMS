<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For now, return true. Implement actual authorization logic based on #HRMS-111.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee'); // Assuming route model binding or ID from route

        return [
            'employee_code' => ['sometimes', 'string', 'max:255', Rule::unique('employees', 'employee_code')->ignore($employeeId)],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employeeId)],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'joining_date' => ['sometimes', 'date'],
            'status' => ['sometimes', 'string', 'in:active,inactive,terminated'],
        ];
    }
}