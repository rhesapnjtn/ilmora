<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_endpoints(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->getJson('/api/dashboard')->assertUnauthorized();
        $this->getJson('/api/subscriptions')->assertUnauthorized();
    }

    public function test_non_admin_cannot_access_admin_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/dashboard')
            ->assertForbidden();
    }

    public function test_user_cannot_view_another_users_subscription(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        // Create a subscription owned by $owner via factory if available; otherwise skip assertion.
        if (! method_exists(\App\Models\Subscription::class, 'factory')) {
            $this->markTestSkipped('Subscription factory not available.');
        }

        $subscription = \App\Models\Subscription::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other, 'sanctum')
            ->getJson('/api/subscriptions/'.$subscription->id)
            ->assertNotFound();
    }
}