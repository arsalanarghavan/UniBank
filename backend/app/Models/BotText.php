<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class BotText extends Model
{
    protected $fillable = ['bot_id', 'key', 'locale', 'value'];

    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }

    public static function getText(?int $botId, string $key, ?string $default = null, string $locale = 'fa'): string
    {
        $cacheKey = "bot_text.".($botId ?? 'global').".{$locale}.{$key}";

        return Cache::remember($cacheKey, 60, function () use ($botId, $key, $default, $locale) {
            $q = static::query()->where('key', $key)->where('locale', $locale);
            if ($botId) {
                $q->where('bot_id', $botId);
            } else {
                $q->whereNull('bot_id');
            }

            return $q->value('value') ?? ($default ?? $key);
        });
    }
}
