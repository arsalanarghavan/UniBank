<?php

namespace App\Modules\Telegram\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bot;
use App\Modules\Telegram\Bot\OstadBankBot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;

class WebhookController extends Controller
{
    public function handleBot(Request $request, Bot $bot, OstadBankBot $botHandler): JsonResponse
    {
        if (! $bot->is_enabled) {
            abort(503, __('bots.disabled'));
        }

        if (! $bot->token) {
            abort(503, __('bots.not_configured'));
        }

        if ($bot->webhook_secret) {
            $header = $request->header('X-Telegram-Bot-Api-Secret-Token')
                ?? $request->header('X-Bot-Webhook-Secret');
            if ($header !== $bot->webhook_secret) {
                abort(403);
            }
        }

        return $this->run($bot->token, $botHandler->forBot($bot), $bot->platform);
    }

    public function handleLegacy(Request $request, OstadBankBot $botHandler): JsonResponse
    {
        $token = config('services.telegram.bot_token');
        $secret = config('services.telegram.webhook_secret');

        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            abort(403);
        }

        if (! $token) {
            abort(503, 'Bot token not configured');
        }

        $legacyBot = Bot::query()
            ->where('platform', Bot::PLATFORM_TELEGRAM)
            ->where('is_enabled', true)
            ->orderBy('id')
            ->first();

        $handler = $legacyBot ? $botHandler->forBot($legacyBot) : $botHandler;

        return $this->run($token, $handler, Bot::PLATFORM_TELEGRAM);
    }

    private function run(string $token, OstadBankBot $handler, string $platform): JsonResponse
    {
        $config = null;
        if ($platform === Bot::PLATFORM_BALE) {
            $config = Configuration::fromArray([
                'api_url' => rtrim((string) config('services.bale.api_url', 'https://tapi.bale.ai'), '/'),
            ]);
        }

        $bot = new Nutgram($token, $config);
        $bot->setRunningMode(Webhook::class);
        $handler->register($bot);
        $bot->run();

        return response()->json(['ok' => true]);
    }
}
