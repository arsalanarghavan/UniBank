<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BotText;
use App\Models\RequiredChannel;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'settings' => Setting::query()->orderBy('key')->get()->pluck('value', 'key'),
                'channels' => RequiredChannel::query()->orderBy('id')->get(),
                'bot_texts' => BotText::query()->orderBy('key')->get(),
            ],
        ]);
    }

    public function updateSetting(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string'],
        ]);
        Setting::setValue($data['key'], $data['value']);

        return response()->json(['message' => __('settings.updated')]);
    }

    public function storeChannel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bot_id' => ['nullable', 'exists:bots,id'],
            'platform' => ['nullable', 'string', 'max:20'],
            'channel_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('required_channels', 'channel_id')->where(
                    fn ($q) => $q->where('bot_id', $request->input('bot_id'))
                ),
            ],
            'channel_link' => ['required', 'url', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);
        $channel = RequiredChannel::query()->create($data);

        return response()->json(['message' => __('settings.channel_created'), 'data' => $channel], 201);
    }

    public function destroyChannel(RequiredChannel $channel): JsonResponse
    {
        $channel->delete();

        return response()->json(['message' => __('settings.channel_deleted')]);
    }

    public function updateBotText(Request $request, BotText $botText): JsonResponse
    {
        $data = $request->validate(['value' => ['required', 'string']]);
        $botText->update($data);
        $botId = $botText->bot_id ?? 'global';
        $locale = $botText->locale ?: 'fa';
        cache()->forget("bot_text.{$botId}.{$locale}.{$botText->key}");

        return response()->json(['message' => __('settings.bot_text_updated'), 'data' => $botText]);
    }

    public function rules(): JsonResponse
    {
        return response()->json([
            'data' => [
                'rules' => BotText::getText(null, 'rules', __('settings.default_rules')),
            ],
        ]);
    }
}
