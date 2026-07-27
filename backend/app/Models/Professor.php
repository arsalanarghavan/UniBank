<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Professor extends Model
{
    protected $fillable = ['name', 'bio'];

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(ProfessorLink::class)->orderBy('sort_order');
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    public function faculties(): BelongsToMany
    {
        return $this->belongsToMany(Faculty::class)->withPivot('is_primary')->withTimestamps();
    }
}
