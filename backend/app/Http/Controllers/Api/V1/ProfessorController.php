<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfessorLinkResource;
use App\Http\Resources\ProfessorResource;
use App\Http\Resources\TeachingAssignmentResource;
use App\Models\Professor;
use App\Models\ProfessorLink;
use App\Models\TeachingAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfessorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Professor::query()->with(['links'])->orderBy('name');
        if ($search = $request->string('q')->toString()) {
            $q->where('name', 'like', "%{$search}%");
        }
        if ($universityId = $request->integer('university_id')) {
            $q->whereHas('teachingAssignments', fn ($qq) => $qq->where('university_id', $universityId));
        }
        if ($courseId = $request->integer('course_id')) {
            $q->whereHas('teachingAssignments', fn ($qq) => $qq->where('course_id', $courseId));
        }
        if ($facultyId = $request->integer('faculty_id')) {
            $q->whereHas('faculties', fn ($qq) => $qq->where('faculties.id', $facultyId));
        }

        return ProfessorResource::collection($q->paginate($request->integer('per_page', 50)))->response();
    }

    public function show(Professor $professor): JsonResponse
    {
        $professor->load(['links', 'teachingAssignments.university', 'teachingAssignments.course', 'faculties.university']);

        return response()->json(['data' => new ProfessorResource($professor)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:professors,name'],
            'bio' => ['nullable', 'string'],
            'faculty_ids' => ['nullable', 'array'],
            'faculty_ids.*' => ['exists:faculties,id'],
        ]);
        $professor = Professor::query()->create(collect($data)->only(['name', 'bio'])->all());
        if (! empty($data['faculty_ids'])) {
            $professor->faculties()->sync($data['faculty_ids']);
        }

        return response()->json(['message' => __('taxonomy.professor_created'), 'data' => new ProfessorResource($professor->load('links', 'faculties'))], 201);
    }

    public function update(Request $request, Professor $professor): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:professors,name,'.$professor->id],
            'bio' => ['nullable', 'string'],
            'faculty_ids' => ['nullable', 'array'],
            'faculty_ids.*' => ['exists:faculties,id'],
        ]);
        $professor->update(collect($data)->only(['name', 'bio'])->all());
        if (array_key_exists('faculty_ids', $data)) {
            $professor->faculties()->sync($data['faculty_ids'] ?? []);
        }

        return response()->json(['message' => __('taxonomy.professor_updated'), 'data' => new ProfessorResource($professor->load('links', 'faculties'))]);
    }

    public function destroy(Professor $professor): JsonResponse
    {
        $professor->delete();

        return response()->json(['message' => __('taxonomy.professor_deleted')]);
    }

    public function storeLink(Request $request, Professor $professor): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(ProfessorLink::TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $link = $professor->links()->create($data);

        return response()->json(['message' => __('taxonomy.link_created'), 'data' => new ProfessorLinkResource($link)], 201);
    }

    public function updateLink(Request $request, Professor $professor, ProfessorLink $link): JsonResponse
    {
        abort_unless($link->professor_id === $professor->id, 404);
        $data = $request->validate([
            'type' => ['sometimes', Rule::in(ProfessorLink::TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'url' => ['sometimes', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $link->update($data);

        return response()->json(['message' => __('taxonomy.link_updated'), 'data' => new ProfessorLinkResource($link)]);
    }

    public function destroyLink(Professor $professor, ProfessorLink $link): JsonResponse
    {
        abort_unless($link->professor_id === $professor->id, 404);
        $link->delete();

        return response()->json(['message' => __('taxonomy.link_deleted')]);
    }

    public function storeAssignment(Request $request, Professor $professor): JsonResponse
    {
        $data = $request->validate([
            'university_id' => ['required', 'exists:universities,id'],
            'course_id' => ['required', 'exists:courses,id'],
        ]);
        $assignment = TeachingAssignment::query()->updateOrCreate(
            [
                'professor_id' => $professor->id,
                'university_id' => $data['university_id'],
                'course_id' => $data['course_id'],
            ],
            []
        );

        return response()->json([
            'message' => __('taxonomy.assignment_created'),
            'data' => new TeachingAssignmentResource($assignment->load(['university', 'course'])),
        ], 201);
    }

    public function destroyAssignment(Professor $professor, TeachingAssignment $assignment): JsonResponse
    {
        abort_unless($assignment->professor_id === $professor->id, 404);
        $assignment->delete();

        return response()->json(['message' => __('taxonomy.assignment_deleted')]);
    }
}
