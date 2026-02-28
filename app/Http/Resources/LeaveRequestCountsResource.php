<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestCountsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_requests' => $this['total_requests'],
            'pending_requests' => $this['pending_requests'],
            'approved_requests' => $this['approved_requests'],
            'rejected_requests' => $this['rejected_requests'],
        ];
    }
}