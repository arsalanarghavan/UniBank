<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Professor */
class ProfessorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'bio' => $this->bio,
            'links' => ProfessorLinkResource::collection($this->whenLoaded('links')),
            'teaching_assignments' => TeachingAssignmentResource::collection($this->whenLoaded('teachingAssignments')),
            'faculties' => FacultyResource::collection($this->whenLoaded('faculties')),
        ];
    }
}
