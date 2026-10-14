<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_registration_rejects_weak_password(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'weakpassword',
            'password_confirmation' => 'weakpassword',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_normalizes_email_case(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Budi Santoso',
            'email' => 'Budi@Example.COM',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);

        $this->postJson('/api/login', [
            'email' => 'BUDI@example.com',
            'password' => 'Password123',
        ])->assertOk();
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'sari@example.com',
            'password' => 'Password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'sari@example.com',
            'password' => 'Password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'sari@example.com',
            'password' => 'Password123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'sari@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_guest_cannot_fetch_profile(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
    }

    public function test_guest_receives_json_401_without_json_accept_header(): void
    {
        $this->get('/api/user')
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertSame(0, $user->tokens()->count());

        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/user')->assertStatus(401);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create(['email' => 'lama@example.com']);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->putJson('/api/profile', [
                'name' => 'Nama Baru',
                'email' => 'baru@example.com',
            ])
            ->assertOk()
            ->assertJsonPath('user.name', 'Nama Baru');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'baru@example.com',
        ]);
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        User::factory()->create([
            'email' => 'sari@example.com',
            'password' => 'Password123',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => 'sari@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(422);
        }

        $this->postJson('/api/login', [
            'email' => 'sari@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_issued_token_expires_after_the_configured_lifetime(): void
    {
        User::factory()->create([
            'email' => 'sari@example.com',
            'password' => 'Password123',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'sari@example.com',
            'password' => 'Password123',
        ])->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/user')->assertOk();

        $this->travel(config('sanctum.expiration') + 1)->minutes();

        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/user')->assertStatus(401);
    }

    public function test_user_can_change_password_and_other_sessions_are_revoked(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        $current = $user->createToken('current');
        $other = $user->createToken('other');

        $this->withToken($current->plainTextToken)
            ->putJson('/api/password', [
                'current_password' => 'Password123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
        $this->assertSame(1, $user->tokens()->count());
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $current->accessToken->id]);

        $this->app['auth']->forgetGuards();
        $this->withToken($other->plainTextToken)->getJson('/api/user')->assertStatus(401);

        $this->app['auth']->forgetGuards();
        $this->withToken($current->plainTextToken)->getJson('/api/user')->assertOk();
    }

    public function test_change_password_requires_the_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->putJson('/api/password', [
                'current_password' => 'WrongPassword123',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('Password123', $user->fresh()->password));
    }

    public function test_user_can_logout_from_all_devices(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('a')->plainTextToken;
        $user->createToken('b');

        $this->withToken($token)->postJson('/api/logout-all')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }
}
