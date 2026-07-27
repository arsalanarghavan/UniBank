<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UniversityCategoryResource;
use App\Models\UniversityCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UniversityCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = UniversityCategory::query()->withCount('universities')->orderBy('sort_order')->orderBy('name');
        if (! $request->boolean('all')) {
            $q->where('is_active', true);
        }

        return response()->json(['data' => UniversityCategoryResource::collection($q->get())]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:university_categories,slug'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;
        $item = UniversityCategory::query()->create($data);

        return response()->json(['message' => __('taxonomy.category_created'), 'data' => new UniversityCategoryResource($item)], 201);
    }

    public function update(Request $request, UniversityCategory $universityCategory): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:university_categories,slug,'.$universityCategory->id],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $universityCategory->update($data);

        return response()->json(['message' => __('taxonomy.category_updated'), 'data' => new UniversityCategoryResource($universityCategory)]);
    }

    public function destroy(UniversityCategory $universityCategory): JsonResponse
    {
        $universityCategory->delete();

        return response()->json(['message' => __('taxonomy.category_deleted')]);
    }
}
