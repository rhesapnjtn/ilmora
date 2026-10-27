<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Course;
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
        $data = $request->validated();
        $courseIds = $this->extractCourseIds($data);

        $plan = Plan::create($this->normalize($data));

        if ($courseIds !== null) {
            $plan->courses()->sync($courseIds);
        }

        return response()->json([
            'message' => 'Paket berhasil dibuat.',
            'plan' => $this->present($plan->fresh()),
        ], 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['plan' => $this->present($plan)]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $data = $request->validated();
        $hasCourseIds = array_key_exists('course_ids', $data);
        $courseIds = $this->extractCourseIds($data);

        $plan->update($this->normalize($data, $plan));

        if ($hasCourseIds) {
            $plan->courses()->sync($courseIds ?? []);
        }

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
     * @return list<int>|null
     */
    private function extractCourseIds(array &$data): ?array
    {
        if (! array_key_exists('course_ids', $data)) {
            return null;
        }

        $courseIds = $data['course_ids'] ?? [];
        unset($data['course_ids']);

        return array_values(array_map('intval', is_array($courseIds) ? $courseIds : []));
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
        $plan->loadMissing('courses');

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
            'courseIds' => $plan->courses->pluck('id')->map(fn ($id): int => (int) $id)->values()->all(),
            'courses' => $plan->courses
                ->sortBy('title')
                ->map(fn (Course $course): array => [
                    'id' => $course->id,
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'level' => $course->level,
                ])
                ->values()
                ->all(),
            'createdAt' => $plan->created_at?->toIso8601String(),
        ];
    }
}
