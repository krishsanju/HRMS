<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchemaInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tables' => $this->resource['tables'],
            'migrations' => $this->resource['migrations']->map(fn($m) => [
                'name' => $m->migration,
                'batch' => $m->batch,
            ]),
        ];
    }
}