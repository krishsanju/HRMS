<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // In a real application, this would check if the authenticated user
        // is allowed to apply for leave (e.g., Auth::user()->can('apply-leave')).
        // For this task, we assume authorization is handled elsewhere or is not required here.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'from_date' => ['required', 'date', 'after_or_equal:today'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'The employee ID is required.',
            'employee_id.integer' => 'The employee ID must be an integer.',
            'employee_id.exists' => 'The selected employee ID is invalid.',
            'from_date.required' => 'The start date is required.',
            'from_date.date' => 'The start date must be a valid date.',
            'from_date.after_or_equal' => 'The start date cannot be in the past.',
            'to_date.required' => 'The end date is required.',
            'to_date.date' => 'The end date must be a valid date.',
            'to_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'reason.required' => 'A reason for leave is required.',
            'reason.string' => 'The reason must be a string.',
            'reason.min' => 'The reason must be at least :min characters long.',
            'reason.max' => 'The reason may not be greater than :max characters long.',
        ];
    }
}