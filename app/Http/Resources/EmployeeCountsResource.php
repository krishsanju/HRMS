<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeCountsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_employees' => $this['total_employees'],
            'active_employees' => $this['active_employees'],
            'inactive_employees' => $this['inactive_employees'],
        ];
    }
}