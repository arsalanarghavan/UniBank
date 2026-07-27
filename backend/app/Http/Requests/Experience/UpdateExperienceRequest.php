<?php

namespace App\Http\Requests\Experience;

use App\Models\Experience;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'university_id' => ['sometimes', 'exists:universities,id'],
            'faculty_id' => ['sometimes', 'exists:faculties,id'],
            'field_id' => ['sometimes', 'exists:fields,id'],
            'major_id' => ['sometimes', 'exists:majors,id'],
            'course_id' => ['sometimes', 'exists:courses,id'],
            'degree_level_id' => ['sometimes', 'exists:degree_levels,id'],
            'professor_id' => ['sometimes', 'exists:professors,id'],
            'teaching_style' => ['sometimes', 'string'],
            'teaching_type' => ['nullable', Rule::in(Experience::TEACHING_TYPES)],
            'notes' => ['nullable', 'string'],
            'project' => ['nullable', 'string'],
            'attendance_required' => ['sometimes', 'boolean'],
            'attendance_details' => ['nullable', 'string'],
            'exam' => ['nullable', 'string'],
            'conclusion' => ['sometimes', 'string'],
            'contact_methods' => ['nullable', 'array'],
            'teaching_rating' => ['sometimes', Rule::in(Experience::TEACHING_RATINGS)],
            'exam_difficulty' => ['nullable', Rule::in(Experience::EXAM_DIFFICULTIES)],
            'overall_rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'has_notes' => ['nullable', 'boolean'],
            'has_project' => ['nullable', 'boolean'],
            'has_exam' => ['nullable', 'boolean'],
        ];
    }
}
