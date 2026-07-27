<?php

namespace App\Modules\Telegram\Services;

use App\Models\Bot;
use App\Models\Experience;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;

class TelegramPublisher
{
    public function publishExperience(Experience $experience, ?Bot $botModel = null): void
    {
        $token = $botModel?->token ?: config('services.telegram.bot_token');
        $channelId = $botModel?->channels()->where('type', 'publish')->value('channel_id')
            ?: Setting::getValue('channel_id', config('services.telegram.channel_id'));

        if (! $token || ! $channelId) {
            Log::warning('Telegram publish skipped: missing token or channel_id');

            return;
        }

        $text = $this->formatExperience($experience);

        try {
            $config = null;
            if ($botModel?->platform === Bot::PLATFORM_BALE) {
                $config = Configuration::fromArray([
                    'api_url' => rtrim((string) config('services.bale.api_url', 'https://tapi.bale.ai'), '/'),
                ]);
            }

            $bot = new Nutgram($token, $config);
            $message = $bot->sendMessage($text, [
                'chat_id' => $channelId,
                'parse_mode' => 'HTML',
            ]);

            if ($message) {
                $experience->update(['channel_message_id' => $message->message_id]);
            }
        } catch (\Throwable $e) {
            Log::error('Telegram publish failed', ['error' => $e->getMessage()]);
        }
    }

    public function formatExperience(Experience $experience): string
    {
        $lines = [
            '<b>'.e($experience->professor?->name ?? '-').'</b>',
            e($experience->university?->name ?? '').' / '.e($experience->faculty?->name ?? ''),
            e($experience->field?->name).' / '.e($experience->major?->name).' / '.e($experience->course?->name),
            '',
            '<b>Teaching:</b> '.e($experience->teaching_style),
            '<b>Rating:</b> '.e((string) $experience->overall_rating).'/5 ('.$experience->teaching_rating.')',
            '<b>Conclusion:</b> '.e($experience->conclusion),
        ];

        return implode("\n", $lines);
    }

    public function sendMessage(int|string $chatId, string $text, ?Bot $botModel = null): void
    {
        $token = $botModel?->token ?: config('services.telegram.bot_token');
        if (! $token) {
            return;
        }

        try {
            $config = null;
            if ($botModel?->platform === Bot::PLATFORM_BALE) {
                $config = Configuration::fromArray([
                    'api_url' => rtrim((string) config('services.bale.api_url', 'https://tapi.bale.ai'), '/'),
                ]);
            }
            $bot = new Nutgram($token, $config);
            $bot->sendMessage($text, ['chat_id' => $chatId]);
        } catch (\Throwable $e) {
            Log::error('Telegram send failed', ['error' => $e->getMessage(), 'chat_id' => $chatId]);
        }
    }
}
