<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;
use App\Modules\Telegram\Services\TelegramPublisher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function pending(Request $request): JsonResponse
    {
        $items = Experience::query()
            ->with(['field', 'major', 'course', 'professor', 'user'])
            ->where('status', Experience::STATUS_PENDING)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => ExperienceResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function approve(Request $request, Experience $experience, TelegramPublisher $publisher): JsonResponse
    {
        if ($experience->status !== Experience::STATUS_PENDING) {
            abort(422, __('moderation.not_pending'));
        }

        $experience->update(['status' => Experience::STATUS_APPROVED]);
        $publisher->publishExperience($experience->fresh(['field', 'major', 'course', 'professor', 'user']));

        return response()->json([
            'message' => __('moderation.approved'),
            'data' => new ExperienceResource($experience->fresh(['field', 'major', 'course', 'professor', 'user'])),
        ]);
    }

    public function reject(Request $request, Experience $experience): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($experience->status !== Experience::STATUS_PENDING) {
            abort(422, __('moderation.not_pending'));
        }

        $experience->update([
            'status' => Experience::STATUS_REJECTED,
            'rejection_reason' => $data['reason'] ?? null,
        ]);

        return response()->json([
            'message' => __('moderation.rejected'),
            'data' => new ExperienceResource($experience->fresh(['field', 'major', 'course', 'professor', 'user'])),
        ]);
    }
}
