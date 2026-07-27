<?php

namespace App\Modules\Telegram\Bot;

use App\Models\Bot;
use App\Models\BotText;
use App\Models\Experience;
use App\Models\Professor;
use App\Models\User;
use App\Modules\Telegram\Services\TelegramPublisher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class OstadBankBot
{
    public function __construct(
        private TelegramPublisher $publisher,
        private ?Bot $botModel = null,
    ) {
    }

    public function forBot(Bot $bot): self
    {
        return new self($this->publisher, $bot);
    }

    public function register(Nutgram $bot): void
    {
        $bot->onCommand('start', function (Nutgram $bot) {
            $this->ensureUser($bot);
            if (! $this->passesForceSubscribe($bot)) {
                return;
            }
            $bot->sendMessage($this->text('welcome', __('telegram.welcome')), [
                'reply_markup' => $this->mainKeyboard(),
            ]);
        });

        $bot->onCommand('admin', function (Nutgram $bot) {
            $user = $this->ensureUser($bot);
            if (! $user->isAdmin()) {
                $bot->sendMessage(__('telegram.admin_only'));

                return;
            }
            $bot->sendMessage(__('telegram.admin_panel'), [
                'reply_markup' => InlineKeyboardMarkup::make()->addRow(
                    InlineKeyboardButton::make(__('telegram.pending'), callback_data: 'admin:pending'),
                    InlineKeyboardButton::make(__('telegram.stats'), callback_data: 'admin:stats'),
                ),
            ]);
        });

        $bot->onCallbackQueryData('admin:pending', function (Nutgram $bot) {
            $user = $this->ensureUser($bot);
            if (! $user->isAdmin()) {
                return;
            }
            $pending = Experience::query()->where('status', Experience::STATUS_PENDING)->latest()->limit(10)->get();
            if ($pending->isEmpty()) {
                $bot->answerCallbackQuery(text: __('telegram.no_pending'));

                return;
            }
            foreach ($pending as $exp) {
                $bot->sendMessage("#{$exp->id} — ".$exp->professor?->name, [
                    'reply_markup' => InlineKeyboardMarkup::make()->addRow(
                        InlineKeyboardButton::make('✅', callback_data: "approve:{$exp->id}"),
                        InlineKeyboardButton::make('❌', callback_data: "reject:{$exp->id}"),
                    ),
                ]);
            }
            $bot->answerCallbackQuery();
        });

        $bot->onCallbackQueryData('admin:stats', function (Nutgram $bot) {
            $user = $this->ensureUser($bot);
            if (! $user->isAdmin()) {
                return;
            }
            $text = sprintf(
                "Users: %d\nPending: %d\nApproved: %d\nRejected: %d",
                User::query()->count(),
                Experience::query()->where('status', 'pending')->count(),
                Experience::query()->where('status', 'approved')->count(),
                Experience::query()->where('status', 'rejected')->count(),
            );
            $bot->sendMessage($text);
            $bot->answerCallbackQuery();
        });

        $bot->onCallbackQueryData('approve:{id}', function (Nutgram $bot, string $id) {
            $user = $this->ensureUser($bot);
            if (! $user->isAdmin()) {
                return;
            }
            $exp = Experience::query()->find($id);
            if ($exp && $exp->status === Experience::STATUS_PENDING) {
                $exp->update(['status' => Experience::STATUS_APPROVED]);
                $this->publisher->publishExperience(
                    $exp->fresh(['field', 'major', 'course', 'professor', 'user', 'university']),
                    $this->botModel
                );
                $bot->answerCallbackQuery(text: __('moderation.approved'));
            }
        });

        $bot->onCallbackQueryData('reject:{id}', function (Nutgram $bot, string $id) {
            $user = $this->ensureUser($bot);
            if (! $user->isAdmin()) {
                return;
            }
            $exp = Experience::query()->find($id);
            if ($exp && $exp->status === Experience::STATUS_PENDING) {
                $exp->update(['status' => Experience::STATUS_REJECTED]);
                $bot->answerCallbackQuery(text: __('moderation.rejected'));
            }
        });

        $bot->onText(__('telegram.btn_my_experiences'), function (Nutgram $bot) {
            $user = $this->ensureUser($bot);
            $items = Experience::query()->where('user_id', $user->id)->latest()->limit(10)->get();
            if ($items->isEmpty()) {
                $bot->sendMessage(__('telegram.no_experiences'));

                return;
            }
            $lines = $items->map(fn ($e) => "#{$e->id} [{$e->status}] ".$e->professor?->name)->implode("\n");
            $bot->sendMessage($lines);
        });

        $bot->onText(__('telegram.btn_rules'), function (Nutgram $bot) {
            $bot->sendMessage($this->text('rules', __('settings.default_rules')));
        });

        $bot->onText(__('telegram.btn_ranking'), function (Nutgram $bot) {
            $top = Professor::query()
                ->withCount(['experiences as approved_count' => fn ($q) => $q->where('status', 'approved')])
                ->having('approved_count', '>=', 3)
                ->orderByDesc('approved_count')
                ->limit(10)
                ->get();
            if ($top->isEmpty()) {
                $bot->sendMessage(__('telegram.no_ranking'));

                return;
            }
            $lines = $top->values()->map(fn ($p, $i) => ($i + 1).'. '.$p->name.' ('.$p->approved_count.')')->implode("\n");
            $bot->sendMessage($lines);
        });

        $bot->onText(__('telegram.btn_search'), function (Nutgram $bot) {
            $bot->sendMessage(__('telegram.search_prompt'));
        });

        $bot->onText(__('telegram.btn_submit'), function (Nutgram $bot) {
            $bot->sendMessage(__('telegram.submit_via_web'));
        });
    }

    private function text(string $key, ?string $default = null): string
    {
        $botId = $this->botModel?->id;

        return BotText::getText($botId, $key, $default, 'fa');
    }

    private function ensureUser(Nutgram $bot): User
    {
        $tg = $bot->user();
        $platform = $this->botModel?->platform ?? Bot::PLATFORM_TELEGRAM;
        $column = $platform === Bot::PLATFORM_BALE ? 'bale_id' : 'telegram_id';

        $user = User::query()->where($column, $tg->id)->first();
        if ($user) {
            if ($this->botModel && ! $user->signup_university_id) {
                $user->update([
                    'signup_university_id' => $this->botModel->university_id,
                    'signup_platform' => $platform,
                ]);
            }

            return $user;
        }

        $prefix = $platform === Bot::PLATFORM_BALE ? 'bale' : 'tg';
        $email = "{$prefix}{$tg->id}@{$platform}.local";
        $user = User::query()->create([
            'name' => trim(($tg->first_name ?? '').' '.($tg->last_name ?? '')) ?: ('User '.$tg->id),
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            $column => $tg->id,
            'locale' => 'fa',
            'signup_university_id' => $this->botModel?->university_id,
            'signup_platform' => $platform,
        ]);
        $user->assignRole('student');

        return $user;
    }

    private function passesForceSubscribe(Nutgram $bot): bool
    {
        $force = $this->botModel
            ? $this->botModel->getSetting('force_subscribe', '0')
            : \App\Models\Setting::getValue('force_subscribe', '0');

        if ($force !== '1') {
            return true;
        }

        $channels = $this->botModel
            ? $this->botModel->requiredChannels()->get()
            : \App\Models\RequiredChannel::query()->whereNull('bot_id')->get();

        if ($channels->isEmpty()) {
            return true;
        }

        $missing = [];
        foreach ($channels as $channel) {
            try {
                $member = $bot->getChatMember($channel->channel_id, $bot->userId());
                $status = $member->status ?? 'left';
                if (in_array($status, ['left', 'kicked'], true)) {
                    $missing[] = $channel;
                }
            } catch (\Throwable) {
                $missing[] = $channel;
            }
        }

        if ($missing === []) {
            return true;
        }

        $links = collect($missing)->map(fn ($c) => $c->channel_link)->implode("\n");
        $bot->sendMessage(__('telegram.force_subscribe')."\n".$links);

        return false;
    }

    private function mainKeyboard(): ReplyKeyboardMarkup
    {
        $layout = $this->botModel?->ui_layout;
        if (is_array($layout) && ! empty($layout['main_menu']['rows'])) {
            $kb = ReplyKeyboardMarkup::make(resize_keyboard: true);
            foreach ($layout['main_menu']['rows'] as $row) {
                $buttons = [];
                foreach ($row as $label) {
                    if (is_string($label) && $label !== '') {
                        $buttons[] = KeyboardButton::make($label);
                    }
                }
                if ($buttons) {
                    $kb->addRow(...$buttons);
                }
            }

            return $kb;
        }

        return ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(KeyboardButton::make(__('telegram.btn_submit')), KeyboardButton::make(__('telegram.btn_my_experiences')))
            ->addRow(KeyboardButton::make(__('telegram.btn_search')), KeyboardButton::make(__('telegram.btn_ranking')))
            ->addRow(KeyboardButton::make(__('telegram.btn_rules')));
    }
}
