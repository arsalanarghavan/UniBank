<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    protected $fillable = ['university_id', 'name'];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }

    public function professors(): BelongsToMany
    {
        return $this->belongsToMany(Professor::class)->withPivot('is_primary')->withTimestamps();
    }
}
