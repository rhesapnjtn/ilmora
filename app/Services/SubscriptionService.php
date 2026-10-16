<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionService
{
    /**
     * Create a pending subscription order together with its pending payment.
     * The subscription only becomes active once an administrator verifies the
     * payment, so no entitlement is granted at this point.
     *
     * @return array{subscription: Subscription, payment: Payment}
     */
    public function createOrder(
        User $user,
        Plan $plan,
        string $billingCycle,
        ?string $paymentMethod = null,
        ?string $notes = null,
    ): array {
        return DB::transaction(function () use ($user, $plan, $billingCycle, $paymentMethod, $notes): array {
            $amount = $this->priceFor($plan, $billingCycle);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan' => $plan->name,
                'billing_cycle' => $billingCycle,
                'status' => 'pending',
                'amount' => $amount,
                'currency' => 'IDR',
            ]);

            $payment = Payment::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'currency' => 'IDR',
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'reference' => $this->generateReference(),
                'notes' => $notes,
            ]);

            return [
                'subscription' => $subscription,
                'payment' => $payment,
            ];
        });
    }

    /**
     * Mark a pending payment as successful and activate its subscription for
     * the duration of the chosen billing cycle.
     */
    public function approve(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $payment->update(['status' => 'success']);

            $subscription = $payment->subscription;

            if ($subscription === null) {
                return;
            }

            $startsAt = now();
            $expiresAt = ($subscription->billing_cycle === 'annual'
                ? $startsAt->copy()->addYear()
                : $startsAt->copy()->addMonth());

            $subscription->update([
                'status' => 'active',
                'starts_at' => $startsAt->toDateString(),
                'expires_at' => $expiresAt->toDateString(),
            ]);
        });
    }

    /**
     * Mark a pending payment as failed and cancel its subscription.
     */
    public function reject(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $payment->update(['status' => 'failed']);

            $payment->subscription?->update(['status' => 'canceled']);
        });
    }

    /**
     * Cancel a subscription at the subscriber's request. A pending order is
     * canceled immediately — its pending payment is marked failed so it leaves
     * the admin verification queue — while an active subscription keeps its
     * benefits until `expires_at` and is simply flagged as not renewing.
     */
    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $reason): Subscription {
            if ($subscription->status === 'pending') {
                $subscription->payments()
                    ->where('status', 'pending')
                    ->update(['status' => 'failed']);

                $subscription->update([
                    'status' => 'canceled',
                    'cancel_at_period_end' => false,
                    'canceled_at' => now(),
                    'cancellation_reason' => $reason,
                ]);

                return $subscription;
            }

            $subscription->update([
                'cancel_at_period_end' => true,
                'canceled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $subscription;
        });
    }

    /**
     * Undo a scheduled cancellation so the subscription keeps renewing.
     */
    public function resume(Subscription $subscription): Subscription
    {
        $subscription->update([
            'cancel_at_period_end' => false,
            'canceled_at' => null,
            'cancellation_reason' => null,
        ]);

        return $subscription;
    }

    public function priceFor(Plan $plan, string $billingCycle): int
    {
        return $billingCycle === 'annual' ? (int) $plan->annual : (int) $plan->monthly;
    }

    private function generateReference(): string
    {
        do {
            $reference = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
