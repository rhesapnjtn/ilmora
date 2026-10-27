<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'plan' => 'Basic',
            'billing_cycle' => 'monthly',
            'status' => 'pending',
            'starts_at' => Carbon::today(),
            'expires_at' => Carbon::today()->addMonth(),
            'cancel_at_period_end' => false,
            'canceled_at' => null,
            'cancellation_reason' => null,
            'amount' => 99000,
            'currency' => 'IDR',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'active',
            'expires_at' => Carbon::today()->addMonth(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'active',
            'expires_at' => Carbon::today()->subDay(),
        ]);
    }
}
