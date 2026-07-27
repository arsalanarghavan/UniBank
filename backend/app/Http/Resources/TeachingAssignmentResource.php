<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\TeachingAssignment */
class TeachingAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'professor_id' => $this->professor_id,
            'university_id' => $this->university_id,
            'course_id' => $this->course_id,
            'professor' => new ProfessorResource($this->whenLoaded('professor')),
            'university' => new UniversityResource($this->whenLoaded('university')),
            'course' => new CourseResource($this->whenLoaded('course')),
        ];
    }
}
