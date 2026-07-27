<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Experience */
class ExperienceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'university_id' => $this->university_id,
            'faculty_id' => $this->faculty_id,
            'degree_level_id' => $this->degree_level_id,
            'teaching_style' => $this->teaching_style,
            'teaching_type' => $this->teaching_type,
            'notes' => $this->notes,
            'project' => $this->project,
            'attendance_required' => $this->attendance_required,
            'attendance_details' => $this->attendance_details,
            'exam' => $this->exam,
            'conclusion' => $this->conclusion,
            'contact_methods' => $this->contact_methods,
            'teaching_rating' => $this->teaching_rating,
            'exam_difficulty' => $this->exam_difficulty,
            'overall_rating' => $this->overall_rating,
            'has_notes' => $this->has_notes,
            'has_project' => $this->has_project,
            'has_exam' => $this->has_exam,
            'rejection_reason' => $this->rejection_reason,
            'university' => $this->whenLoaded('university', fn () => ['id' => $this->university?->id, 'name' => $this->university?->name]),
            'faculty' => $this->whenLoaded('faculty', fn () => ['id' => $this->faculty?->id, 'name' => $this->faculty?->name]),
            'degree_level' => $this->whenLoaded('degreeLevel', fn () => ['id' => $this->degreeLevel?->id, 'name' => $this->degreeLevel?->name]),
            'field' => $this->whenLoaded('field', fn () => ['id' => $this->field?->id, 'name' => $this->field?->name]),
            'major' => $this->whenLoaded('major', fn () => ['id' => $this->major?->id, 'name' => $this->major?->name]),
            'course' => $this->whenLoaded('course', fn () => ['id' => $this->course?->id, 'name' => $this->course?->name]),
            'professor' => $this->whenLoaded('professor', fn () => ['id' => $this->professor?->id, 'name' => $this->professor?->name]),
            'attachments' => ExperienceAttachmentResource::collection($this->whenLoaded('attachments')),
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
