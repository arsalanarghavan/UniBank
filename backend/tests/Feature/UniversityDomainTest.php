<?php

namespace Tests\Feature;

use App\Models\DegreeLevel;
use App\Models\Faculty;
use App\Models\University;
use App\Models\UniversityCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_can_list_categories_universities_and_degrees(): void
    {
        $this->getJson('/api/v1/university-categories')->assertOk()->assertJsonStructure(['data']);
        $this->getJson('/api/v1/universities')->assertOk()->assertJsonStructure(['data']);
        $this->getJson('/api/v1/degree-levels')->assertOk()->assertJsonStructure(['data']);
        $this->getJson('/api/v1/faculties')->assertOk()->assertJsonStructure(['data']);
    }

    public function test_admin_can_create_university_faculty_and_scoped_field(): void
    {
        $admin = User::factory()->create(['email' => 'admin2@example.com']);
        $admin->syncRoles(['admin']);

        $category = UniversityCategory::query()->first();
        $this->assertNotNull($category);

        $uni = $this->actingAs($admin)->postJson('/api/v1/admin/universities', [
            'university_category_id' => $category->id,
            'name' => 'دانشگاه تست',
        ])->assertCreated()->json('data');

        $faculty = $this->actingAs($admin)->postJson('/api/v1/admin/faculties', [
            'university_id' => $uni['id'],
            'name' => 'مهندسی',
        ])->assertCreated()->json('data');

        $this->actingAs($admin)->postJson('/api/v1/admin/fields', [
            'faculty_id' => $faculty['id'],
            'name' => 'کامپیوتر',
        ])->assertCreated();

        $this->getJson('/api/v1/fields?faculty_id='.$faculty['id'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'کامپیوتر']);
    }

    public function test_admin_can_manage_bots_per_university(): void
    {
        $admin = User::factory()->create(['email' => 'botadmin@example.com']);
        $admin->syncRoles(['admin']);
        $university = University::query()->first();
        $this->assertNotNull($university);

        $bot = $this->actingAs($admin)->postJson('/api/v1/admin/bots', [
            'university_id' => $university->id,
            'platform' => 'telegram',
            'name' => 'Test Bot',
            'token' => '123:ABC',
        ])->assertCreated()->json('data');

        $this->actingAs($admin)->putJson("/api/v1/admin/bots/{$bot['id']}/layout", [
            'ui_layout' => ['main_menu' => ['rows' => [['ثبت تجربه'], ['قوانین']]]],
        ])->assertOk();

        $this->actingAs($admin)->postJson("/api/v1/admin/bots/{$bot['id']}/texts", [
            'key' => 'welcome',
            'locale' => 'fa',
            'value' => 'سلام',
        ])->assertOk();

        $this->actingAs($admin)->getJson("/api/v1/admin/bots/{$bot['id']}")
            ->assertOk()
            ->assertJsonPath('data.platform', 'telegram');
    }

    public function test_degree_levels_seeded(): void
    {
        $this->assertTrue(DegreeLevel::query()->where('slug', 'bachelor')->exists());
        $this->assertTrue(Faculty::query()->exists());
    }
}
