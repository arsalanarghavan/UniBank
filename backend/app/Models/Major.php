<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    protected $fillable = ['name', 'field_id'];

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
