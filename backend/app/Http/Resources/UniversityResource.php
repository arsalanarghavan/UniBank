<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\University */
class UniversityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_path' => $this->logo_path,
            'is_active' => $this->is_active,
            'university_category_id' => $this->university_category_id,
            'category' => new UniversityCategoryResource($this->whenLoaded('category')),
            'faculties' => FacultyResource::collection($this->whenLoaded('faculties')),
            'bots' => BotResource::collection($this->whenLoaded('bots')),
        ];
    }
}
