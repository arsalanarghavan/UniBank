<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Http\Resources\ProfessorResource;
use App\Models\Course;
use App\Models\Professor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->string('q')->toString());
        if ($q === '') {
            return response()->json(['data' => ['professors' => [], 'courses' => []]]);
        }

        $professors = Professor::query()->where('name', 'like', "%{$q}%")->orderBy('name')->limit(20)->get();
        $courses = Course::query()->with('major.field')->where('name', 'like', "%{$q}%")->orderBy('name')->limit(20)->get();

        return response()->json([
            'data' => [
                'professors' => ProfessorResource::collection($professors),
                'courses' => CourseResource::collection($courses),
            ],
        ]);
    }
}
