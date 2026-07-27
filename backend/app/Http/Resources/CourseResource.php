<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Course */
class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'major_id' => $this->major_id,
            'degree_level_id' => $this->degree_level_id,
            'degree_level' => new DegreeLevelResource($this->whenLoaded('degreeLevel')),
        ];
    }
}
