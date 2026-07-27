<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingAssignment extends Model
{
    protected $fillable = ['professor_id', 'university_id', 'course_id'];

    public function professor(): BelongsTo { return $this->belongsTo(Professor::class); }
    public function university(): BelongsTo { return $this->belongsTo(University::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}
