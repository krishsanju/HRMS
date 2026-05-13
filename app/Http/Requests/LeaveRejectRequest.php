<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization will be handled by #HRMS-111 later
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                Rule::exists('leave_requests', 'id')->where(function ($query) {
                    $query->where('status', 'pending'); // Only pending requests can be rejected
                }),
            ],
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'], // From #HRMS-117
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}