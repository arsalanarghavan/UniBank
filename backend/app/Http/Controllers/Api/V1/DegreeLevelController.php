<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DegreeLevelResource;
use App\Models\DegreeLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DegreeLevelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = DegreeLevel::query()->orderBy('sort_order')->orderBy('name');
        if (! $request->boolean('all')) {
            $q->where('is_active', true);
        }

        return response()->json(['data' => DegreeLevelResource::collection($q->get())]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:degree_levels,slug'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name_en'] ?? $data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;
        $item = DegreeLevel::query()->create($data);

        return response()->json(['message' => __('taxonomy.degree_created'), 'data' => new DegreeLevelResource($item)], 201);
    }

    public function update(Request $request, DegreeLevel $degreeLevel): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:degree_levels,slug,'.$degreeLevel->id],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $degreeLevel->update($data);

        return response()->json(['message' => __('taxonomy.degree_updated'), 'data' => new DegreeLevelResource($degreeLevel)]);
    }

    public function destroy(DegreeLevel $degreeLevel): JsonResponse
    {
        $degreeLevel->delete();

        return response()->json(['message' => __('taxonomy.degree_deleted')]);
    }
}
