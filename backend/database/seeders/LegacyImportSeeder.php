<?php

namespace Database\Seeders;

use App\Models\BotText;
use App\Models\Course;
use App\Models\DegreeLevel;
use App\Models\Experience;
use App\Models\Faculty;
use App\Models\Field;
use App\Models\Major;
use App\Models\Professor;
use App\Models\RequiredChannel;
use App\Models\Setting;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class LegacyImportSeeder extends Seeder
{
    /**
     * Import from legacy MariaDB connection named "legacy".
     * Configure LEGACY_DB_* env vars and run: php artisan db:seed --class=LegacyImportSeeder
     */
    public function run(): void
    {
        if (! config('database.connections.legacy')) {
            $this->command?->warn('legacy connection not configured');

            return;
        }

        $this->call(UniversityDomainSeeder::class);

        $university = University::query()->first();
        $faculty = Faculty::query()->first();
        $degree = DegreeLevel::query()->where('slug', 'bachelor')->first()
            ?? DegreeLevel::query()->first();

        foreach (['student', 'admin', 'owner'] as $role) {
            Role::findOrCreate($role);
        }

        $teachingMap = [
            'عالی' => 'excellent',
            'خوب' => 'good',
            'متوسط' => 'average',
            'ضعیف' => 'poor',
        ];
        $examMap = [
            'آسان' => 'easy',
            'متوسط' => 'medium',
            'سخت' => 'hard',
        ];

        foreach (DB::connection('legacy')->table('users')->get() as $row) {
            $user = User::query()->updateOrCreate(
                ['telegram_id' => $row->user_id],
                [
                    'name' => $row->first_name ?: ('TG '.$row->user_id),
                    'email' => 'tg'.$row->user_id.'@telegram.local',
                    'password' => Hash::make(Str::random(32)),
                    'locale' => 'fa',
                    'signup_university_id' => $university?->id,
                    'signup_platform' => 'telegram',
                ]
            );
            $user->assignRole('student');
        }

        foreach (DB::connection('legacy')->table('admins')->get() as $row) {
            $user = User::query()->where('telegram_id', $row->user_id)->first();
            if ($user) {
                $user->syncRoles(['admin']);
            }
        }

        $fieldMap = [];
        foreach (DB::connection('legacy')->table('fields')->get() as $row) {
            $f = Field::query()->updateOrCreate(
                ['faculty_id' => $faculty?->id, 'name' => $row->name],
                ['faculty_id' => $faculty?->id]
            );
            $fieldMap[$row->id] = $f->id;
        }

        $majorMap = [];
        foreach (DB::connection('legacy')->table('majors')->get() as $row) {
            $m = Major::query()->updateOrCreate(
                ['field_id' => $fieldMap[$row->field_id] ?? null, 'name' => $row->name],
                ['field_id' => $fieldMap[$row->field_id] ?? Field::query()->first()?->id]
            );
            $majorMap[$row->id] = $m->id;
        }

        $courseMap = [];
        foreach (DB::connection('legacy')->table('courses')->get() as $row) {
            $c = Course::query()->updateOrCreate(
                ['major_id' => $majorMap[$row->major_id] ?? null, 'name' => $row->name],
                [
                    'major_id' => $majorMap[$row->major_id] ?? Major::query()->first()?->id,
                    'degree_level_id' => $degree?->id,
                ]
            );
            $courseMap[$row->id] = $c->id;
        }

        $profMap = [];
        foreach (DB::connection('legacy')->table('professors')->get() as $row) {
            $p = Professor::query()->updateOrCreate(['name' => $row->name]);
            if ($faculty) {
                $p->faculties()->syncWithoutDetaching([$faculty->id]);
            }
            $profMap[$row->id] = $p->id;
        }

        foreach (DB::connection('legacy')->table('experiences')->get() as $row) {
            $user = User::query()->where('telegram_id', $row->user_id)->first();
            if (! $user) {
                continue;
            }
            Experience::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'professor_id' => $profMap[$row->professor_id] ?? null,
                    'course_id' => $courseMap[$row->course_id] ?? null,
                    'created_at' => $row->created_at,
                ],
                [
                    'university_id' => $university?->id,
                    'faculty_id' => $faculty?->id,
                    'degree_level_id' => $degree?->id,
                    'field_id' => $fieldMap[$row->field_id] ?? null,
                    'major_id' => $majorMap[$row->major_id] ?? null,
                    'teaching_style' => $row->teaching_style,
                    'notes' => $row->notes,
                    'project' => $row->project,
                    'attendance_required' => $row->attendance_required,
                    'attendance_details' => $row->attendance_details,
                    'exam' => $row->exam,
                    'conclusion' => $row->conclusion,
                    'status' => $row->status,
                    'teaching_rating' => $teachingMap[$row->teaching_rating] ?? $row->teaching_rating,
                    'exam_difficulty' => $examMap[$row->exam_difficulty] ?? $row->exam_difficulty,
                    'overall_rating' => $row->overall_rating,
                    'has_notes' => $row->has_notes,
                    'has_project' => $row->has_project,
                    'has_exam' => $row->has_exam,
                    'channel_message_id' => $row->channel_message_id,
                ]
            );
        }

        foreach (DB::connection('legacy')->table('settings')->get() as $row) {
            Setting::setValue($row->key, (string) $row->value);
        }

        foreach (DB::connection('legacy')->table('required_channels')->get() as $row) {
            RequiredChannel::query()->updateOrCreate(
                ['bot_id' => null, 'channel_id' => $row->channel_id],
                ['channel_link' => $row->channel_link, 'platform' => 'telegram']
            );
        }

        foreach (DB::connection('legacy')->table('bot_texts')->get() as $row) {
            BotText::query()->updateOrCreate(
                ['bot_id' => null, 'key' => $row->key, 'locale' => 'fa'],
                ['value' => $row->value]
            );
        }
    }
}
