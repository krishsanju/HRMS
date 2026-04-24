<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveApproveRequest extends FormRequest
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
        // The 'id' is a route parameter, but we can still validate its type and existence.
        // When using route model binding, Laravel will attempt to resolve the model first.
        // This validation ensures the ID is valid before the controller method is even called.
        return [
            'id' => ['required', 'integer', 'exists:leave_requests,id'],
        ];
    }

    /**
     * Prepare the data for validation.
     */\    protected function prepareForValidation(): void
    {
        // Merge route parameters into the request data so they can be validated.
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}