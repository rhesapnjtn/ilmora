<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_create_a_subscription(): void
    {
        $plan = Plan::factory()->create();

        $this->postJson('/api/subscriptions', [
            'plan' => $plan->slug,
            'billing_cycle' => 'monthly',
        ])->assertStatus(401);
    }

    public function test_plan_detail_is_public_but_rejects_unknown_or_inactive_plans(): void
    {
        $plan = Plan::factory()->create(['slug' => 'basic']);
        $inactive = Plan::factory()->create(['is_active' => false]);

        $this->getJson("/api/plans/{$plan->slug}")
            ->assertOk()
            ->assertJsonPath('plan.id', 'basic');

        $this->getJson('/api/plans/does-not-exist')->assertStatus(404);
        $this->getJson("/api/plans/{$inactive->slug}")->assertStatus(404);
    }

    public function test_user_can_create_a_pending_subscription_order(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['monthly' => 149000, 'annual' => 1430000]);

        Sanctum::actingAs($user);

        $this->postJson('/api/subscriptions', [
            'plan' => $plan->slug,
            'billing_cycle' => 'monthly',
            'payment_method' => 'bank_transfer',
            'notes' => 'Transfer dari BCA',
        ])
            ->assertStatus(201)
            ->assertJsonPath('subscription.status', 'pending')
            ->assertJsonPath('subscription.billingCycle', 'monthly')
            ->assertJsonPath('subscription.amount', fn ($value): bool => (float) $value === 149000.0)
            ->assertJsonPath('subscription.payment.status', 'pending')
            ->assertJsonPath('subscription.payment.reference', fn ($value): bool => is_string($value) && $value !== '');

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'plan' => $plan->name,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'amount' => 149000,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);
    }

    public function test_annual_orders_use_the_annual_price(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['monthly' => 149000, 'annual' => 1430000]);

        Sanctum::actingAs($user);

        $this->postJson('/api/subscriptions', [
            'plan' => $plan->slug,
            'billing_cycle' => 'annual',
        ])
            ->assertStatus(201)
            ->assertJsonPath('subscription.amount', fn ($value): bool => (float) $value === 1430000.0);
    }

    public function test_subscription_validation_rejects_invalid_input(): void
    {
        $user = User::factory()->create();
        $inactive = Plan::factory()->create(['is_active' => false]);

        Sanctum::actingAs($user);

        $this->postJson('/api/subscriptions', ['plan' => 'does-not-exist', 'billing_cycle' => 'monthly'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('plan');

        $this->postJson('/api/subscriptions', ['plan' => $inactive->slug, 'billing_cycle' => 'monthly'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('plan');

        $this->postJson('/api/subscriptions', ['plan' => $inactive->slug, 'billing_cycle' => 'weekly'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('billing_cycle');
    }

    public function test_user_can_only_see_their_own_orders(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        [$subscription] = $this->createOrder($owner);

        Sanctum::actingAs($owner);
        $this->getJson("/api/subscriptions/{$subscription->id}")
            ->assertOk()
            ->assertJsonPath('subscription.id', $subscription->id);

        Sanctum::actingAs($other);
        $this->getJson("/api/subscriptions/{$subscription->id}")
            ->assertStatus(404);
    }

    public function test_user_can_list_their_own_subscriptions_and_payments(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->createOrder($owner);
        $this->createOrder($owner);
        $this->createOrder($other);

        Sanctum::actingAs($owner);

        $this->getJson('/api/subscriptions')
            ->assertOk()
            ->assertJsonCount(2, 'subscriptions');

        $this->getJson('/api/payments')
            ->assertOk()
            ->assertJsonCount(2, 'payments');
    }

    public function test_admin_can_approve_a_payment_and_activate_the_subscription(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['monthly' => 149000]);

        Sanctum::actingAs($user);
        $order = $this->postJson('/api/subscriptions', [
            'plan' => $plan->slug,
            'billing_cycle' => 'monthly',
        ])->assertStatus(201);

        $subscriptionId = $order->json('subscription.id');
        $paymentId = $order->json('subscription.payment.id');

        Sanctum::actingAs($admin);
        $this->postJson("/api/admin/payments/{$paymentId}/approve")->assertOk();

        $this->assertDatabaseHas('payments', ['id' => $paymentId, 'status' => 'success']);

        $subscription = Subscription::findOrFail($subscriptionId);
        $this->assertSame('active', $subscription->status);
        $this->assertNotNull($subscription->starts_at);
        $this->assertNotNull($subscription->expires_at);
    }

    public function test_admin_can_reject_a_payment_and_cancel_the_subscription(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        Sanctum::actingAs($user);
        $order = $this->postJson('/api/subscriptions', ['plan' => $plan->slug, 'billing_cycle' => 'monthly'])
            ->assertStatus(201);

        $subscriptionId = $order->json('subscription.id');
        $paymentId = $order->json('subscription.payment.id');

        Sanctum::actingAs($admin);
        $this->postJson("/api/admin/payments/{$paymentId}/reject")->assertOk();

        $this->assertDatabaseHas('payments', ['id' => $paymentId, 'status' => 'failed']);
        $this->assertSame('canceled', Subscription::findOrFail($subscriptionId)->status);
    }

    public function test_only_pending_payments_can_be_reviewed(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        Sanctum::actingAs($user);
        $order = $this->postJson('/api/subscriptions', ['plan' => $plan->slug, 'billing_cycle' => 'monthly'])
            ->assertStatus(201);

        $paymentId = $order->json('subscription.payment.id');

        Sanctum::actingAs($admin);
        $this->postJson("/api/admin/payments/{$paymentId}/approve")->assertOk();
        $this->postJson("/api/admin/payments/{$paymentId}/approve")->assertStatus(422);
    }

    public function test_regular_users_cannot_review_payments(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        Sanctum::actingAs($user);
        $order = $this->postJson('/api/subscriptions', ['plan' => $plan->slug, 'billing_cycle' => 'monthly'])
            ->assertStatus(201);

        $paymentId = $order->json('subscription.payment.id');

        $this->postJson("/api/admin/payments/{$paymentId}/approve")->assertStatus(403);

        $this->assertDatabaseHas('payments', ['id' => $paymentId, 'status' => 'pending']);
    }

    public function test_dashboard_reflects_subscription_state(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('subscription.code', null)
            ->assertJsonPath('subscription.hasActive', false);

        $order = $this->postJson('/api/subscriptions', ['plan' => $plan->slug, 'billing_cycle' => 'monthly'])
            ->assertStatus(201);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('subscription.code', 'pending')
            ->assertJsonPath('subscription.hasActive', false);

        Sanctum::actingAs($admin);
        $this->postJson("/api/admin/payments/{$order->json('subscription.payment.id')}/approve")->assertOk();

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('subscription.code', 'active')
            ->assertJsonPath('subscription.hasActive', true);
    }

    public function test_guests_cannot_cancel_a_subscription(): void
    {
        $user = User::factory()->create();
        $subscription = $this->createActive($user);

        $this->postJson("/api/subscriptions/{$subscription->id}/cancel")->assertStatus(401);
    }

    public function test_user_can_schedule_cancellation_of_an_active_subscription(): void
    {
        $user = User::factory()->create();
        $subscription = $this->createActive($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/subscriptions/{$subscription->id}/cancel", ['reason' => 'Terlalu mahal'])
            ->assertOk()
            ->assertJsonPath('subscription.status', 'active')
            ->assertJsonPath('subscription.cancelAtPeriodEnd', true)
            ->assertJsonPath('subscription.cancellationReason', 'Terlalu mahal');

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->cancel_at_period_end);
        $this->assertNotNull($subscription->canceled_at);
    }

    public function test_user_can_cancel_a_pending_order_immediately(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        Sanctum::actingAs($user);
        $order = $this->postJson('/api/subscriptions', ['plan' => $plan->slug, 'billing_cycle' => 'monthly'])
            ->assertStatus(201);

        $subscriptionId = $order->json('subscription.id');
        $paymentId = $order->json('subscription.payment.id');

        $this->postJson("/api/subscriptions/{$subscriptionId}/cancel")
            ->assertOk()
            ->assertJsonPath('subscription.status', 'canceled')
            ->assertJsonPath('subscription.cancelAtPeriodEnd', false);

        $this->assertDatabaseHas('subscriptions', ['id' => $subscriptionId, 'status' => 'canceled']);
        $this->assertDatabaseHas('payments', ['id' => $paymentId, 'status' => 'failed']);
    }

    public function test_user_can_resume_a_scheduled_cancellation(): void
    {
        $user = User::factory()->create();
        $subscription = $this->createActive($user, [
            'cancel_at_period_end' => true,
            'canceled_at' => now(),
            'cancellation_reason' => 'Berhenti sementara',
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/subscriptions/{$subscription->id}/resume")
            ->assertOk()
            ->assertJsonPath('subscription.status', 'active')
            ->assertJsonPath('subscription.cancelAtPeriodEnd', false)
            ->assertJsonPath('subscription.cancellationReason', null);

        $subscription->refresh();
        $this->assertFalse($subscription->cancel_at_period_end);
        $this->assertNull($subscription->canceled_at);
    }

    public function test_subscriptions_that_are_not_cancelable_are_rejected(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create();

        Sanctum::actingAs($user);
        $order = $this->postJson('/api/subscriptions', ['plan' => $plan->slug, 'billing_cycle' => 'monthly'])
            ->assertStatus(201);

        $subscriptionId = $order->json('subscription.id');

        $this->postJson("/api/subscriptions/{$subscriptionId}/cancel")->assertOk();
        $this->postJson("/api/subscriptions/{$subscriptionId}/cancel")->assertStatus(422);
    }

    public function test_user_cannot_resume_a_subscription_that_is_not_scheduled_to_cancel(): void
    {
        $user = User::factory()->create();
        $subscription = $this->createActive($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/subscriptions/{$subscription->id}/resume")->assertStatus(422);
    }

    public function test_user_cannot_cancel_another_users_subscription(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $subscription = $this->createActive($owner);

        Sanctum::actingAs($other);

        $this->postJson("/api/subscriptions/{$subscription->id}/cancel")->assertStatus(404);
        $this->postJson("/api/subscriptions/{$subscription->id}/resume")->assertStatus(404);

        $this->assertFalse($subscription->refresh()->cancel_at_period_end);
    }

    public function test_cancellation_reason_is_validated(): void
    {
        $user = User::factory()->create();
        $subscription = $this->createActive($user);

        Sanctum::actingAs($user);

        $this->postJson("/api/subscriptions/{$subscription->id}/cancel", ['reason' => str_repeat('a', 501)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('reason');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createActive(User $user, array $overrides = []): Subscription
    {
        $plan = Plan::factory()->create();

        return Subscription::create(array_merge([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'plan' => $plan->name,
            'billing_cycle' => 'monthly',
            'status' => 'active',
            'starts_at' => now()->subWeek()->toDateString(),
            'expires_at' => now()->addWeeks(3)->toDateString(),
            'amount' => $plan->monthly,
            'currency' => 'IDR',
        ], $overrides));
    }

    /**
     * @return array{0: Subscription, 1: Payment}
     */
    private function createOrder(User $user): array
    {
        $plan = Plan::factory()->create();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'plan' => $plan->name,
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'amount' => $plan->monthly,
            'currency' => 'IDR',
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => $plan->monthly,
            'currency' => 'IDR',
            'status' => 'pending',
        ]);

        return [$subscription, $payment];
    }
}
