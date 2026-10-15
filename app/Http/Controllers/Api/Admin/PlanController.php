<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Plan $plan): array => $this->present($plan))
            ->all();

        return response()->json(['plans' => $plans]);
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $plan = Plan::create($this->normalize($request->validated()));

        return response()->json([
            'message' => 'Paket berhasil dibuat.',
            'plan' => $this->present($plan),
        ], 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['plan' => $this->present($plan)]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($this->normalize($request->validated(), $plan));

        return response()->json([
            'message' => 'Paket berhasil diperbarui.',
            'plan' => $this->present($plan->fresh()),
        ]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $inUse = Subscription::query()
            ->where('plan', $plan->slug)
            ->orWhere('plan', $plan->name)
            ->exists();

        if ($inUse) {
            return response()->json([
                'message' => 'Paket tidak dapat dihapus karena masih digunakan oleh langganan pengguna.',
            ], 422);
        }

        $plan->delete();

        return response()->json(['message' => 'Paket berhasil dihapus.']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, ?Plan $plan = null): array
    {
        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['name'] ?? $plan?->name ?? '');

        foreach (['highlighted', 'is_active'] as $flag) {
            $data[$flag] = (bool) ($data[$flag] ?? $plan->{$flag} ?? false);
        }

        $data['sort_order'] = (int) ($data['sort_order'] ?? $plan?->sort_order ?? 0);

        if (array_key_exists('features', $data)) {
            $data['features'] = array_values(array_filter((array) $data['features'], fn ($item): bool => filled($item)));
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Plan $plan): array
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'monthly' => (int) $plan->monthly,
            'annual' => (int) $plan->annual,
            'highlighted' => (bool) $plan->highlighted,
            'features' => $plan->features ?? [],
            'isActive' => (bool) $plan->is_active,
            'sortOrder' => (int) $plan->sort_order,
            'createdAt' => $plan->created_at?->toIso8601String(),
        ];
    }
}
