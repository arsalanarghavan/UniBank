<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExperienceAttachment extends Model
{
    protected $fillable = ['experience_id', 'disk', 'path', 'original_name', 'mime', 'size'];

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
