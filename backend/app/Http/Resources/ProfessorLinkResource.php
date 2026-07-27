<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ProfessorLink */
class ProfessorLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'professor_id' => $this->professor_id,
            'type' => $this->type,
            'title' => $this->title,
            'url' => $this->url,
            'sort_order' => $this->sort_order,
        ];
    }
}
