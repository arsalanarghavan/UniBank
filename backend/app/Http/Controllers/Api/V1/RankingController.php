<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Professor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $minReviews = max(1, $request->integer('min_reviews', 3));

        $rows = Professor::query()
            ->select('professors.id', 'professors.name')
            ->join('experiences', 'experiences.professor_id', '=', 'professors.id')
            ->where('experiences.status', Experience::STATUS_APPROVED)
            ->whereNotNull('experiences.overall_rating')
            ->groupBy('professors.id', 'professors.name')
            ->havingRaw('COUNT(experiences.id) >= ?', [$minReviews])
            ->selectRaw('COUNT(experiences.id) as reviews_count')
            ->selectRaw('AVG(experiences.overall_rating) as avg_overall')
            ->selectRaw("AVG(CASE experiences.teaching_rating
                WHEN 'excellent' THEN 5
                WHEN 'good' THEN 4
                WHEN 'average' THEN 3
                WHEN 'poor' THEN 1
                ELSE NULL END) as avg_teaching")
            ->get()
            ->map(function ($row) {
                $overall = (float) $row->avg_overall;
                $teaching = (float) ($row->avg_teaching ?? 0);
                $score = ($overall * 0.6) + ($teaching * 0.4);

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'reviews_count' => (int) $row->reviews_count,
                    'avg_overall' => round($overall, 2),
                    'avg_teaching' => round($teaching, 2),
                    'score' => round($score, 2),
                ];
            })
            ->sortByDesc('score')
            ->values();

        return response()->json(['data' => $rows]);
    }
}
