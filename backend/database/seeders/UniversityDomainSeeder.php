<?php

namespace Database\Seeders;

use App\Models\DegreeLevel;
use App\Models\Faculty;
use App\Models\University;
use App\Models\UniversityCategory;
use Illuminate\Database\Seeder;

class UniversityDomainSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'سراسری', 'name_en' => 'Public', 'slug' => 'public', 'sort_order' => 1],
            ['name' => 'آزاد', 'name_en' => 'Azad', 'slug' => 'azad', 'sort_order' => 2],
            ['name' => 'پیام‌نور', 'name_en' => 'Payame Noor', 'slug' => 'payame-noor', 'sort_order' => 3],
            ['name' => 'علمی‌کاربردی', 'name_en' => 'Applied Science', 'slug' => 'applied-science', 'sort_order' => 4],
            ['name' => 'غیرانتفاعی', 'name_en' => 'Non-profit', 'slug' => 'non-profit', 'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            UniversityCategory::query()->updateOrCreate(['slug' => $cat['slug']], $cat + ['is_active' => true]);
        }

        $degrees = [
            ['name' => 'کاردانی', 'name_en' => 'Associate', 'slug' => 'associate', 'sort_order' => 1],
            ['name' => 'کارشناسی', 'name_en' => 'Bachelor', 'slug' => 'bachelor', 'sort_order' => 2],
            ['name' => 'کارشناسی ارشد', 'name_en' => 'Master', 'slug' => 'master', 'sort_order' => 3],
            ['name' => 'دکتری', 'name_en' => 'PhD', 'slug' => 'phd', 'sort_order' => 4],
        ];

        foreach ($degrees as $degree) {
            DegreeLevel::query()->updateOrCreate(['slug' => $degree['slug']], $degree + ['is_active' => true]);
        }

        $public = UniversityCategory::query()->where('slug', 'public')->first();
        if ($public) {
            $uni = University::query()->updateOrCreate(
                ['slug' => 'default-university'],
                [
                    'university_category_id' => $public->id,
                    'name' => 'دانشگاه پیش‌فرض',
                    'is_active' => true,
                ]
            );

            Faculty::query()->updateOrCreate(
                ['university_id' => $uni->id, 'name' => 'دانشکده پیش‌فرض'],
                []
            );
        }

        $defaultFaculty = Faculty::query()->first();
        if ($defaultFaculty) {
            \App\Models\Field::query()->whereNull('faculty_id')->update(['faculty_id' => $defaultFaculty->id]);
        }

        $bachelor = DegreeLevel::query()->where('slug', 'bachelor')->first();
        if ($bachelor) {
            \App\Models\Course::query()->whereNull('degree_level_id')->update(['degree_level_id' => $bachelor->id]);
        }
    }
}
