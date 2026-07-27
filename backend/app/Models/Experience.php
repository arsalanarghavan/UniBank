<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Experience extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const TEACHING_RATINGS = ['excellent', 'good', 'average', 'poor'];
    public const EXAM_DIFFICULTIES = ['easy', 'medium', 'hard'];
    public const TEACHING_TYPES = ['in_person', 'online', 'hybrid'];

    protected $fillable = [
        'user_id', 'university_id', 'faculty_id', 'field_id', 'major_id',
        'professor_id', 'course_id', 'degree_level_id',
        'teaching_style', 'teaching_type', 'notes', 'project', 'attendance_required',
        'attendance_details', 'exam', 'conclusion', 'contact_methods', 'status',
        'teaching_rating', 'exam_difficulty', 'overall_rating',
        'has_notes', 'has_project', 'has_exam',
        'admin_message_id', 'admin_chat_id', 'channel_message_id',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'attendance_required' => 'boolean',
            'has_notes' => 'boolean',
            'has_project' => 'boolean',
            'has_exam' => 'boolean',
            'overall_rating' => 'integer',
            'contact_methods' => 'array',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function university(): BelongsTo { return $this->belongsTo(University::class); }
    public function faculty(): BelongsTo { return $this->belongsTo(Faculty::class); }
    public function field(): BelongsTo { return $this->belongsTo(Field::class); }
    public function major(): BelongsTo { return $this->belongsTo(Major::class); }
    public function professor(): BelongsTo { return $this->belongsTo(Professor::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function degreeLevel(): BelongsTo { return $this->belongsTo(DegreeLevel::class); }
    public function attachments(): HasMany { return $this->hasMany(ExperienceAttachment::class); }

    public function teachingScore(): float
    {
        return match ($this->teaching_rating) {
            'excellent' => 5.0,
            'good' => 4.0,
            'average' => 3.0,
            'poor' => 1.0,
            default => 0.0,
        };
    }
}
