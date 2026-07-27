<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotChannel extends Model
{
    protected $fillable = ['bot_id', 'type', 'channel_id', 'channel_link', 'title'];

    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }
}
