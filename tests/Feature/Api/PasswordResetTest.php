<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_request_a_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/password/forgot', ['email' => $user->email])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user): bool {
            $mail = $notification->toMail($user);
            $url = (string) ($mail->viewData['url'] ?? '');

            return $mail->view === 'emails.auth.reset-password'
                && str_contains($url, '/reset-sandi?token=')
                && str_contains($url, 'email=')
                && ($mail->viewData['name'] ?? null) === $user->name;
        });
    }

    public function test_forgot_password_always_returns_same_response(): void
    {
        Notification::fake();

        $unknownResponse = $this->postJson('/api/password/forgot', ['email' => 'tidak-ada@example.com']);

        $unknownResponse->assertOk();
        Notification::assertNothingSent();

        Notification::fake();
        $user = User::factory()->create();
        $knownResponse = $this->postJson('/api/password/forgot', ['email' => $user->email]);
        $knownResponse->assertOk();

        $this->assertSame(
            $knownResponse->json('message'),
            $unknownResponse->json('message'),
        );
    }

    public function test_user_can_reset_password_with_a_valid_token(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);

        $token = Password::createToken($user);

        $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertTrue(Hash::check('PasswordBaru123', $user->refresh()->password));

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_reset_password_rejects_an_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'token' => 'token-tidak-valid',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_reset_password_rejects_a_weak_password(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'lemah',
            'password_confirmation' => 'lemah',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }

    public function test_resetting_password_revokes_existing_sessions(): void
    {
        $user = User::factory()->create();
        $user->createToken('spa');

        $token = Password::createToken($user);

        $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertOk();

        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_forgot_password_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/password/forgot', ['email' => $user->email])->assertOk();
        }

        $this->postJson('/api/password/forgot', ['email' => $user->email])->assertStatus(429);
    }
}
