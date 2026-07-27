<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacultyResource;
use App\Models\Faculty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Faculty::query()->with('university')->orderBy('name');
        if ($uid = $request->integer('university_id')) {
            $q->where('university_id', $uid);
        }

        return response()->json(['data' => FacultyResource::collection($q->get())]);
    }

    public function show(Faculty $faculty): JsonResponse
    {
        $faculty->load(['university', 'fields.majors.courses.degreeLevel', 'professors']);

        return response()->json(['data' => new FacultyResource($faculty)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'university_id' => ['required', 'exists:universities,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        $item = Faculty::query()->create($data);

        return response()->json(['message' => __('taxonomy.faculty_created'), 'data' => new FacultyResource($item)], 201);
    }

    public function update(Request $request, Faculty $faculty): JsonResponse
    {
        $data = $request->validate([
            'university_id' => ['sometimes', 'exists:universities,id'],
            'name' => ['sometimes', 'string', 'max:255'],
        ]);
        $faculty->update($data);

        return response()->json(['message' => __('taxonomy.faculty_updated'), 'data' => new FacultyResource($faculty)]);
    }

    public function destroy(Faculty $faculty): JsonResponse
    {
        $faculty->delete();

        return response()->json(['message' => __('taxonomy.faculty_deleted')]);
    }
}
