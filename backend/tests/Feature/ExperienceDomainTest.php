<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DegreeLevel;
use App\Models\Experience;
use App\Models\Faculty;
use App\Models\Field;
use App\Models\Major;
use App\Models\Professor;
use App\Models\University;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExperienceDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_student_can_submit_experience_with_university_scope(): void
    {
        Storage::fake('public');

        $student = User::factory()->create(['email' => 'exp@example.com']);
        $student->assignRole('student');

        $university = University::query()->first();
        $faculty = Faculty::query()->first();
        $degree = DegreeLevel::query()->where('slug', 'bachelor')->first();
        $field = Field::query()->create(['name' => 'IT', 'faculty_id' => $faculty->id]);
        $major = Major::query()->create(['name' => 'SE', 'field_id' => $field->id]);
        $course = Course::query()->create([
            'name' => 'Algo',
            'major_id' => $major->id,
            'degree_level_id' => $degree->id,
        ]);
        $professor = Professor::query()->create(['name' => 'Dr Domain']);

        $response = $this->actingAs($student)->postJson('/api/v1/experiences', [
            'university_id' => $university->id,
            'faculty_id' => $faculty->id,
            'field_id' => $field->id,
            'major_id' => $major->id,
            'course_id' => $course->id,
            'degree_level_id' => $degree->id,
            'professor_id' => $professor->id,
            'teaching_style' => 'Clear',
            'teaching_type' => 'hybrid',
            'conclusion' => 'Good course',
            'attendance_required' => true,
            'teaching_rating' => 'good',
            'overall_rating' => 4,
            'contact_methods' => ['telegram'],
        ]);

        $response->assertCreated()->assertJsonPath('data.status', 'pending');
        $id = $response->json('data.id');

        $this->actingAs($student)
            ->post("/api/v1/experiences/{$id}/attachments", [
                'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertCreated();

        $this->assertDatabaseHas('experiences', [
            'id' => $id,
            'university_id' => $university->id,
            'teaching_type' => 'hybrid',
        ]);
        $this->assertDatabaseCount('experience_attachments', 1);
    }
}
