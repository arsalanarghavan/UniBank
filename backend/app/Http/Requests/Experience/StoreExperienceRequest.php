<?php

namespace App\Http\Requests\Experience;

use App\Models\Experience;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'university_id' => ['required', 'exists:universities,id'],
            'faculty_id' => ['required', 'exists:faculties,id'],
            'field_id' => ['required', 'exists:fields,id'],
            'major_id' => ['required', 'exists:majors,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'degree_level_id' => ['required', 'exists:degree_levels,id'],
            'professor_id' => ['required', 'exists:professors,id'],
            'teaching_style' => ['required', 'string'],
            'teaching_type' => ['nullable', Rule::in(Experience::TEACHING_TYPES)],
            'notes' => ['nullable', 'string'],
            'project' => ['nullable', 'string'],
            'attendance_required' => ['required', 'boolean'],
            'attendance_details' => ['nullable', 'string'],
            'exam' => ['nullable', 'string'],
            'conclusion' => ['required', 'string'],
            'contact_methods' => ['nullable', 'array'],
            'teaching_rating' => ['required', Rule::in(Experience::TEACHING_RATINGS)],
            'exam_difficulty' => ['nullable', Rule::in(Experience::EXAM_DIFFICULTIES)],
            'overall_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'has_notes' => ['nullable', 'boolean'],
            'has_project' => ['nullable', 'boolean'],
            'has_exam' => ['nullable', 'boolean'],
        ];
    }
}
