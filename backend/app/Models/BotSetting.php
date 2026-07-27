<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotSetting extends Model
{
    protected $fillable = ['bot_id', 'key', 'value'];

    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }
}
