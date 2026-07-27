<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MajorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'field_id' => $this->field_id,
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
