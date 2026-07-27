<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UniversityResource;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UniversityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = University::query()->with(['category'])->orderBy('name');
        if ($cat = $request->integer('university_category_id')) {
            $q->where('university_category_id', $cat);
        }
        if (! $request->boolean('all')) {
            $q->where('is_active', true);
        }

        return response()->json(['data' => UniversityResource::collection($q->get())]);
    }

    public function show(University $university): JsonResponse
    {
        $university->load(['category', 'faculties.fields.majors.courses.degreeLevel', 'bots']);

        return response()->json(['data' => new UniversityResource($university)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'university_category_id' => ['required', 'exists:university_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:universities,slug'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;
        $item = University::query()->create($data);

        return response()->json(['message' => __('taxonomy.university_created'), 'data' => new UniversityResource($item->load('category'))], 201);
    }

    public function update(Request $request, University $university): JsonResponse
    {
        $data = $request->validate([
            'university_category_id' => ['sometimes', 'exists:university_categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:universities,slug,'.$university->id],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $university->update($data);

        return response()->json(['message' => __('taxonomy.university_updated'), 'data' => new UniversityResource($university->load('category'))]);
    }

    public function destroy(University $university): JsonResponse
    {
        $university->delete();

        return response()->json(['message' => __('taxonomy.university_deleted')]);
    }
}
