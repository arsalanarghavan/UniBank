<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Bot */
class BotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'university_id' => $this->university_id,
            'platform' => $this->platform,
            'name' => $this->name,
            'username' => $this->username,
            'has_token' => filled($this->token),
            'webhook_secret' => $this->when($request->user()?->isAdmin(), $this->webhook_secret),
            'is_enabled' => $this->is_enabled,
            'ui_layout' => $this->ui_layout,
            'university' => new UniversityResource($this->whenLoaded('university')),
            'channels' => $this->whenLoaded('channels'),
            'settings' => $this->whenLoaded('settings', fn () => $this->settings->pluck('value', 'key')),
            'texts' => $this->whenLoaded('texts'),
            'required_channels' => $this->whenLoaded('requiredChannels'),
        ];
    }
}
