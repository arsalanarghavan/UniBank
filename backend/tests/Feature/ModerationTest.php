<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Experience;
use App\Models\Field;
use App\Models\Major;
use App\Models\Professor;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_approve_pending_experience(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->syncRoles(['admin']);

        $student = User::factory()->create(['email' => 's@example.com']);
        $student->assignRole('student');

        $field = Field::query()->create([
            'name' => 'CS',
            'faculty_id' => \App\Models\Faculty::query()->first()?->id,
        ]);
        $major = Major::query()->create(['name' => 'SE', 'field_id' => $field->id]);
        $course = Course::query()->create(['name' => 'Algo', 'major_id' => $major->id]);
        $professor = Professor::query()->create(['name' => 'Dr Test']);

        $experience = Experience::query()->create([
            'user_id' => $student->id,
            'field_id' => $field->id,
            'major_id' => $major->id,
            'course_id' => $course->id,
            'professor_id' => $professor->id,
            'teaching_style' => 'Clear',
            'conclusion' => 'Good',
            'attendance_required' => true,
            'teaching_rating' => 'good',
            'overall_rating' => 4,
            'status' => Experience::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->postJson("/api/v1/admin/moderation/{$experience->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_student_cannot_access_moderation(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)
            ->getJson('/api/v1/admin/moderation/pending')
            ->assertForbidden();
    }
}
