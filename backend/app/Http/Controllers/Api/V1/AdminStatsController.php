<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $byStatus = Experience::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthly = Experience::query()
            ->select(DB::raw("{$monthExpr} as month"), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'data' => [
                'users_count' => User::query()->count(),
                'professors_count' => Professor::query()->count(),
                'experiences_total' => Experience::query()->count(),
                'experiences_by_status' => [
                    'pending' => (int) ($byStatus['pending'] ?? 0),
                    'approved' => (int) ($byStatus['approved'] ?? 0),
                    'rejected' => (int) ($byStatus['rejected'] ?? 0),
                ],
                'monthly_experiences' => $monthly,
            ],
        ]);
    }
}
