<?php

namespace App\Jobs;

use App\Models\User;
use App\Modules\Telegram\Services\TelegramPublisher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BroadcastTelegramMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $message,
        public ?int $telegramId = null,
    ) {
    }

    public function handle(TelegramPublisher $publisher): void
    {
        if ($this->telegramId) {
            $publisher->sendMessage($this->telegramId, $this->message);

            return;
        }

        User::query()
            ->whereNotNull('telegram_id')
            ->where('is_active', true)
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($publisher) {
                foreach ($users as $user) {
                    $publisher->sendMessage($user->telegram_id, $this->message);
                }
            });
    }
}
