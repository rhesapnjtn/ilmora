<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\DemoCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->lower()->toString();
        $category = $request->string('category')->toString();
        $level = $request->string('level')->toString();
        $price = $request->string('price')->toString();
        $rating = $request->float('rating');
        $perPage = 6;

        $courses = Course::query()
            ->published()
            ->withCount('lessons')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(summary) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(category_name) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($category !== '', fn (Builder $query) => $query->where('category', $category))
            ->when($level !== '', fn (Builder $query) => $query->where('level', $level))
            ->when($price === 'free', fn (Builder $query) => $query->where('is_free', true))
            ->when($price === 'premium', fn (Builder $query) => $query->where('is_free', false))
            ->when($rating > 0, fn (Builder $query) => $query->where('rating', '>=', $rating))
            ->orderByDesc('popular')
            ->orderByDesc('rating')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'courses' => collect($courses->items())->map(fn (Course $course): array => $this->presentCard($course))->all(),
            'categories' => DemoCatalog::categories(),
            'levels' => DemoCatalog::levels(),
            'meta' => [
                'page' => $courses->currentPage(),
                'perPage' => $courses->perPage(),
                'total' => $courses->total(),
                'lastPage' => $courses->lastPage(),
            ],
            'filters' => [
                'search' => $request->string('search')->toString(),
                'category' => $category,
                'level' => $level,
                'price' => $price,
                'rating' => $rating,
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $course = Course::query()
            ->published()
            ->with('lessons')
            ->where('slug', $slug)
            ->first();

        if ($course === null) {
            return response()->json(['message' => 'Kursus tidak ditemukan.'], 404);
        }

        return response()->json(['course' => $this->presentDetail($course)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentCard(Course $course): array
    {
        return [
            'slug' => $course->slug,
            'title' => $course->title,
            'summary' => $course->summary,
            'category' => $course->category,
            'categoryName' => $course->category_name,
            'level' => $course->level,
            'price' => (float) $course->price,
            'rating' => (float) $course->rating,
            'reviews' => $course->review_count,
            'students' => $course->students,
            'durationHours' => $course->duration_hours,
            'lessonsCount' => $course->lessons_count,
            'instructor' => [
                'name' => $course->instructor_name,
                'role' => $course->instructor_role,
            ],
            'accent' => $course->accent,
            'popular' => (bool) $course->popular,
            'free' => (bool) $course->is_free,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentDetail(Course $course): array
    {
        $curriculum = $course->lessons
            ->groupBy(fn ($lesson): string => $lesson->module ?: 'Kurikulum')
            ->map(fn ($lessons, string $module): array => [
                'title' => $module,
                'lessons' => $lessons->map(fn ($lesson): array => [
                    'id' => $lesson->slug,
                    'title' => $lesson->title,
                    'duration' => $lesson->duration ?? '00:00',
                    'free' => (bool) $lesson->is_free,
                ])->values()->all(),
            ])
            ->values()
            ->all();

        $includes = collect($course->includes ?? ['Sertifikat penyelesaian', 'Akses selamanya'])
            ->map(fn (string $label): array => ['label' => $label, 'icon' => 'award'])
            ->all();

        $data = $this->presentCard($course);
        $data['description'] = $course->description;
        $data['learn'] = $course->learn ?? [];
        $data['includes'] = $includes;
        $data['curriculum'] = $curriculum;
        $data['lessonsTotal'] = $course->lessons->count();

        return $data;
    }
}
