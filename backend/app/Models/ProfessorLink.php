<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessorLink extends Model
{
    public const TYPES = ['google_scholar', 'orcid', 'researchgate', 'website', 'book', 'other'];

    protected $fillable = ['professor_id', 'type', 'title', 'url', 'sort_order'];

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Professor::class);
    }
}
