<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    public function index(): JsonResponse
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Plan $plan): array => $this->presentPlan($plan))
            ->all();

        return response()->json([
            'plans' => $plans,
            'discountPercent' => 20,
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $plan = Plan::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->first();

        if ($plan === null) {
            return response()->json(['message' => 'Paket langganan tidak ditemukan.'], 404);
        }

        return response()->json([
            'plan' => $this->presentPlan($plan),
            'discountPercent' => 20,
        ]);
    }

    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $plan = Plan::query()
            ->where('slug', $request->string('plan')->toString())
            ->firstOrFail();

        $order = $this->subscriptions->createOrder(
            $request->user(),
            $plan,
            $request->string('billing_cycle')->toString(),
            $request->filled('payment_method') ? $request->string('payment_method')->toString() : null,
            $request->filled('notes') ? $request->string('notes')->toString() : null,
        );

        return response()->json([
            'message' => 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran.',
            'subscription' => $this->presentSubscription($order['subscription'], $order['payment']),
        ], 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $subscriptions = Subscription::query()
            ->where('user_id', $request->user()->id)
            ->with(['payments', 'planRecord'])
            ->latest('id')
            ->get()
            ->map(fn (Subscription $subscription): array => $this->presentSubscription(
                $subscription,
                $subscription->payments->sortByDesc('id')->first(),
            ))
            ->all();

        return response()->json(['subscriptions' => $subscriptions]);
    }

    public function order(Request $request, Subscription $subscription): JsonResponse
    {
        if ((int) $subscription->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $subscription->load(['payments', 'planRecord']);

        return response()->json([
            'subscription' => $this->presentSubscription(
                $subscription,
                $subscription->payments->sortByDesc('id')->first(),
            ),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentPlan(Plan $plan): array
    {
        return [
            'id' => $plan->slug,
            'name' => $plan->name,
            'monthly' => (int) $plan->monthly,
            'annual' => (int) $plan->annual,
            'highlighted' => (bool) $plan->highlighted,
            'features' => $plan->features ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentSubscription(Subscription $subscription, ?Payment $payment): array
    {
        return [
            'id' => $subscription->id,
            'plan' => $subscription->plan,
            'planSlug' => $subscription->planRecord?->slug,
            'billingCycle' => $subscription->billing_cycle,
            'status' => $subscription->status,
            'amount' => (float) $subscription->amount,
            'currency' => $subscription->currency,
            'startsAt' => $subscription->starts_at?->toDateString(),
            'expiresAt' => $subscription->expires_at?->toDateString(),
            'createdAt' => $subscription->created_at?->toIso8601String(),
            'payment' => $payment === null ? null : $this->presentPayment($payment),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'reference' => $payment->reference,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'paymentMethod' => $payment->payment_method,
            'notes' => $payment->notes,
            'createdAt' => $payment->created_at?->toIso8601String(),
        ];
    }
}
