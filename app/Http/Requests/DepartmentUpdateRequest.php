<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization will be handled by #HRMS-111 later
    }

    public function rules(): array
    {
        $departmentId = $this->route('department'); // Assuming route parameter is 'department' for resource routes
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($departmentId)],
        ];
    }
}