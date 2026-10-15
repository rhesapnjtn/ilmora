<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $subscriptions = Subscription::query()
            ->with('user:id,name,email')
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('user', function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'subscriptions' => collect($subscriptions->items())->map(fn (Subscription $subscription): array => [
                'id' => $subscription->id,
                'user' => [
                    'id' => $subscription->user?->id,
                    'name' => $subscription->user?->name,
                    'email' => $subscription->user?->email,
                ],
                'plan' => $subscription->plan,
                'status' => $subscription->status,
                'amount' => (float) $subscription->amount,
                'currency' => $subscription->currency,
                'startsAt' => $subscription->starts_at?->toDateString(),
                'expiresAt' => $subscription->expires_at?->toDateString(),
                'createdAt' => $subscription->created_at?->toIso8601String(),
            ])->all(),
            'summary' => [
                'total' => Subscription::query()->count(),
                'active' => Subscription::query()->where('status', 'active')->count(),
                'expired' => Subscription::query()->where('status', 'expired')->count(),
                'canceled' => Subscription::query()->where('status', 'canceled')->count(),
            ],
            'meta' => [
                'page' => $subscriptions->currentPage(),
                'perPage' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
                'lastPage' => $subscriptions->lastPage(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }
}
