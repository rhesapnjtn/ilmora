<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Plan $plan): array => [
                'id' => $plan->slug,
                'name' => $plan->name,
                'monthly' => (int) $plan->monthly,
                'annual' => (int) $plan->annual,
                'highlighted' => (bool) $plan->highlighted,
                'features' => $plan->features ?? [],
            ])
            ->all();

        return response()->json([
            'plans' => $plans,
            'discountPercent' => 20,
        ]);
    }
}
