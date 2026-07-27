<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bot extends Model
{
    public const PLATFORM_TELEGRAM = 'telegram';
    public const PLATFORM_BALE = 'bale';

    protected $fillable = [
        'university_id', 'platform', 'name', 'username', 'token',
        'webhook_secret', 'is_enabled', 'ui_layout',
    ];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'ui_layout' => 'array',
        ];
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(BotChannel::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(BotSetting::class);
    }

    public function texts(): HasMany
    {
        return $this->hasMany(BotText::class);
    }

    public function requiredChannels(): HasMany
    {
        return $this->hasMany(RequiredChannel::class);
    }

    public function getSetting(string $key, ?string $default = null): ?string
    {
        return $this->settings()->where('key', $key)->value('value') ?? $default;
    }

    public function setSetting(string $key, string $value): void
    {
        $this->settings()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
