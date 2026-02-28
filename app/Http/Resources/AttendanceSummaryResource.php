<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'daily_summary' => $this['daily_summary'],
            'total_check_ins_period' => $this['total_check_ins_period'],
            'total_check_outs_period' => $this['total_check_outs_period'],
            'total_working_hours_period' => $this['total_working_hours_period'],
        ];
    }
}