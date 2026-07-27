<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        config(['sanctum.stateful' => ['localhost', 'localhost:3000', '127.0.0.1']]);
    }

    private function spaJson(string $method, string $uri, array $data = [])
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Referer' => 'http://localhost:3000',
            'Accept' => 'application/json',
        ])->{strtolower($method).'Json'}($uri, $data);
    }

    public function test_user_can_register_and_login(): void
    {
        $register = $this->spaJson('POST', '/api/v1/auth/register', [
            'name' => 'Student One',
            'email' => 'student@example.com',
            'password' => 'Password!123',
            'password_confirmation' => 'Password!123',
            'locale' => 'fa',
        ]);

        $register->assertCreated()
            ->assertJsonPath('data.email', 'student@example.com')
            ->assertJsonPath('data.roles.0', 'student');

        $this->spaJson('POST', '/api/v1/auth/logout')->assertOk();

        $login = $this->spaJson('POST', '/api/v1/auth/login', [
            'email' => 'student@example.com',
            'password' => 'Password!123',
        ]);

        $login->assertOk()->assertJsonPath('data.email', 'student@example.com');

        $this->spaJson('GET', '/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'student@example.com');
    }

    public function test_login_is_throttled_friendly_and_rejects_bad_password(): void
    {
        User::factory()->create([
            'email' => 'demo@example.com',
            'password' => 'Password!123',
        ])->assignRole('student');

        $this->spaJson('POST', '/api/v1/auth/login', [
            'email' => 'demo@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'Password!123',
            'is_active' => false,
        ]);
        $user->assignRole('student');

        $this->spaJson('POST', '/api/v1/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'Password!123',
        ])->assertStatus(422);
    }
}
