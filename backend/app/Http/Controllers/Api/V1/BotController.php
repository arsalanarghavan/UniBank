<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BotResource;
use App\Models\Bot;
use App\Models\BotChannel;
use App\Models\BotText;
use App\Models\RequiredChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BotController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Bot::query()->with(['university', 'channels', 'requiredChannels'])->orderBy('id');
        if ($uid = $request->integer('university_id')) {
            $q->where('university_id', $uid);
        }

        return response()->json(['data' => BotResource::collection($q->get())]);
    }

    public function show(Bot $bot): JsonResponse
    {
        $bot->load(['university', 'channels', 'settings', 'texts', 'requiredChannels']);

        return response()->json(['data' => new BotResource($bot)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'university_id' => ['required', 'exists:universities,id'],
            'platform' => ['required', Rule::in([Bot::PLATFORM_TELEGRAM, Bot::PLATFORM_BALE])],
            'name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'token' => ['nullable', 'string'],
            'is_enabled' => ['nullable', 'boolean'],
            'ui_layout' => ['nullable', 'array'],
        ]);
        $data['webhook_secret'] = Str::random(32);
        $data['is_enabled'] = $data['is_enabled'] ?? true;
        $bot = Bot::query()->create($data);

        return response()->json(['message' => __('bots.created'), 'data' => new BotResource($bot)], 201);
    }

    public function update(Request $request, Bot $bot): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'token' => ['nullable', 'string'],
            'is_enabled' => ['nullable', 'boolean'],
            'ui_layout' => ['nullable', 'array'],
        ]);
        $bot->update($data);

        return response()->json(['message' => __('bots.updated'), 'data' => new BotResource($bot->fresh())]);
    }

    public function destroy(Bot $bot): JsonResponse
    {
        $bot->delete();

        return response()->json(['message' => __('bots.deleted')]);
    }

    public function updateLayout(Request $request, Bot $bot): JsonResponse
    {
        $data = $request->validate(['ui_layout' => ['required', 'array']]);
        $bot->update(['ui_layout' => $data['ui_layout']]);

        return response()->json(['message' => __('bots.layout_updated'), 'data' => new BotResource($bot)]);
    }

    public function syncSettings(Request $request, Bot $bot): JsonResponse
    {
        $data = $request->validate(['settings' => ['required', 'array']]);
        foreach ($data['settings'] as $key => $value) {
            $bot->setSetting((string) $key, (string) $value);
        }

        return response()->json(['message' => __('bots.settings_updated'), 'data' => new BotResource($bot->load('settings'))]);
    }

    public function upsertText(Request $request, Bot $bot): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:5'],
            'value' => ['required', 'string'],
        ]);
        $locale = $data['locale'] ?? 'fa';
        $text = BotText::query()->updateOrCreate(
            ['bot_id' => $bot->id, 'key' => $data['key'], 'locale' => $locale],
            ['value' => $data['value']]
        );
        cache()->forget("bot_text.{$bot->id}.{$locale}.{$data['key']}");

        return response()->json(['message' => __('bots.text_updated'), 'data' => $text]);
    }

    public function storeChannel(Request $request, Bot $bot): JsonResponse
    {
        $data = $request->validate([
            'type' => ['nullable', 'string', 'max:30'],
            'channel_id' => ['required', 'string', 'max:255'],
            'channel_link' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);
        $data['type'] = $data['type'] ?? 'publish';
        $channel = $bot->channels()->create($data);

        return response()->json(['message' => __('bots.channel_created'), 'data' => $channel], 201);
    }

    public function destroyChannel(Bot $bot, BotChannel $channel): JsonResponse
    {
        abort_unless($channel->bot_id === $bot->id, 404);
        $channel->delete();

        return response()->json(['message' => __('bots.channel_deleted')]);
    }

    public function storeRequiredChannel(Request $request, Bot $bot): JsonResponse
    {
        $data = $request->validate([
            'channel_id' => ['required', 'string', 'max:255'],
            'channel_link' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:20'],
        ]);
        $data['platform'] = $data['platform'] ?? $bot->platform;
        $channel = $bot->requiredChannels()->create($data);

        return response()->json(['message' => __('bots.required_channel_created'), 'data' => $channel], 201);
    }

    public function destroyRequiredChannel(Bot $bot, RequiredChannel $channel): JsonResponse
    {
        abort_unless($channel->bot_id === $bot->id, 404);
        $channel->delete();

        return response()->json(['message' => __('bots.required_channel_deleted')]);
    }

    public function regenerateSecret(Bot $bot): JsonResponse
    {
        $bot->update(['webhook_secret' => Str::random(32)]);

        return response()->json(['message' => __('bots.secret_regenerated'), 'data' => new BotResource($bot)]);
    }
}
