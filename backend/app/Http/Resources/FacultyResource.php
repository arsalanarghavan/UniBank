<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Faculty */
class FacultyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'university_id' => $this->university_id,
            'university' => new UniversityResource($this->whenLoaded('university')),
            'fields' => FieldResource::collection($this->whenLoaded('fields')),
            'professors' => ProfessorResource::collection($this->whenLoaded('professors')),
        ];
    }
}
