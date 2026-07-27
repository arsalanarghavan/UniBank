<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Http\Resources\FieldResource;
use App\Http\Resources\MajorResource;
use App\Models\Course;
use App\Models\Field;
use App\Models\Major;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxonomyController extends Controller
{
    public function fields(Request $request): JsonResponse
    {
        $q = Field::query()->with(['majors.courses.degreeLevel'])->orderBy('name');
        if ($facultyId = $request->integer('faculty_id')) {
            $q->where('faculty_id', $facultyId);
        }
        if ($universityId = $request->integer('university_id')) {
            $q->whereHas('faculty', fn ($qq) => $qq->where('university_id', $universityId));
        }

        return response()->json(['data' => FieldResource::collection($q->get())]);
    }

    public function storeField(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'faculty_id' => ['required', 'exists:faculties,id'],
        ]);
        $field = Field::query()->create($data);

        return response()->json(['message' => __('taxonomy.field_created'), 'data' => new FieldResource($field)], 201);
    }

    public function updateField(Request $request, Field $field): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'faculty_id' => ['sometimes', 'exists:faculties,id'],
        ]);
        $field->update($data);

        return response()->json(['message' => __('taxonomy.field_updated'), 'data' => new FieldResource($field)]);
    }

    public function destroyField(Field $field): JsonResponse
    {
        $field->delete();

        return response()->json(['message' => __('taxonomy.field_deleted')]);
    }

    public function storeMajor(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'field_id' => ['required', 'exists:fields,id'],
        ]);
        $major = Major::query()->create($data);

        return response()->json(['message' => __('taxonomy.major_created'), 'data' => new MajorResource($major)], 201);
    }

    public function updateMajor(Request $request, Major $major): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'field_id' => ['sometimes', 'exists:fields,id'],
        ]);
        $major->update($data);

        return response()->json(['message' => __('taxonomy.major_updated'), 'data' => new MajorResource($major)]);
    }

    public function destroyMajor(Major $major): JsonResponse
    {
        $major->delete();

        return response()->json(['message' => __('taxonomy.major_deleted')]);
    }

    public function storeCourse(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'major_id' => ['required', 'exists:majors,id'],
            'degree_level_id' => ['nullable', 'exists:degree_levels,id'],
        ]);
        $course = Course::query()->create($data);

        return response()->json(['message' => __('taxonomy.course_created'), 'data' => new CourseResource($course->load('degreeLevel'))], 201);
    }

    public function updateCourse(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'major_id' => ['sometimes', 'exists:majors,id'],
            'degree_level_id' => ['nullable', 'exists:degree_levels,id'],
        ]);
        $course->update($data);

        return response()->json(['message' => __('taxonomy.course_updated'), 'data' => new CourseResource($course->load('degreeLevel'))]);
    }

    public function destroyCourse(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json(['message' => __('taxonomy.course_deleted')]);
    }
}
