<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastTelegramMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function broadcast(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        BroadcastTelegramMessage::dispatch($data['message'], null);

        return response()->json(['message' => __('admin.broadcast_queued')]);
    }

    public function directMessage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telegram_id' => ['required', 'integer'],
            'message' => ['required', 'string', 'max:4000'],
        ]);

        BroadcastTelegramMessage::dispatch($data['message'], (int) $data['telegram_id']);

        return response()->json(['message' => __('admin.dm_queued')]);
    }
}
