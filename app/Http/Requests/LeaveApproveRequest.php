<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveApproveRequest extends FormRequest
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
                    $query->where('status', 'pending'); // Only pending requests can be approved
                }),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}