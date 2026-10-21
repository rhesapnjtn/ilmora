<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $courses = Course::query()
            ->withCount('lessons')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('category_name', 'like', "%{$search}%");
                });
            })
            ->when($status === 'published', fn ($query) => $query->where('published', true))
            ->when($status === 'draft', fn ($query) => $query->where('published', false))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'courses' => collect($courses->items())->map(fn (Course $course): array => $this->presentSummary($course))->all(),
            'meta' => [
                'page' => $courses->currentPage(),
                'perPage' => $courses->perPage(),
                'total' => $courses->total(),
                'lastPage' => $courses->lastPage(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $data = $this->normalize($request->validated());

        $course = Course::create($data);

        return response()->json([
            'message' => 'Kursus berhasil dibuat.',
            'course' => $this->presentDetail($course->load('lessons')),
        ], 201);
    }

    public function show(Course $course): JsonResponse
    {
        return response()->json([
            'course' => $this->presentDetail($course->load('lessons')),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $data = $this->normalize($request->validated(), $course);

        $course->update($data);

        return response()->json([
            'message' => 'Kursus berhasil diperbarui.',
            'course' => $this->presentDetail($course->fresh()->load('lessons')),
        ]);
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json(['message' => 'Kursus berhasil dihapus.']);
    }

    public function togglePublish(Course $course): JsonResponse
    {
        $course->update(['published' => ! $course->published]);

        return response()->json([
            'message' => $course->published ? 'Kursus dipublikasikan.' : 'Kursus dijadikan draf.',
            'course' => $this->presentSummary($course->loadCount('lessons')),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, ?Course $course = null): array
    {
        $data['slug'] = filled($data['slug'] ?? null)
            ? $data['slug']
            : Str::slug($data['title'] ?? $course?->title ?? '');

        $data['category'] = $data['category'] ?? $course?->category ?? 'lainnya';
        $data['category_name'] = $data['category_name'] ?? $course?->category_name ?? 'Lainnya';
        $data['level'] = $data['level'] ?? $course?->level ?? 'Pemula';
        $data['accent'] = $data['accent'] ?? $course?->accent ?? 'primary';
        $data['thumbnail'] = $data['thumbnail'] ?? $course?->thumbnail ?? '/placeholder.svg?height=300&width=500';
        $data['rating'] = $data['rating'] ?? $course?->rating ?? 0;
        $data['review_count'] = $data['review_count'] ?? $course?->review_count ?? 0;
        $data['students'] = $data['students'] ?? $course?->students ?? 0;
        $data['duration_hours'] = $data['duration_hours'] ?? $course?->duration_hours ?? 0;

        foreach (['popular', 'is_free', 'published'] as $flag) {
            $data[$flag] = (bool) ($data[$flag] ?? $course?->{$flag} ?? false);
        }

        foreach (['learn', 'includes'] as $list) {
            if (array_key_exists($list, $data)) {
                $data[$list] = array_values(array_filter((array) $data[$list], fn ($item) => filled($item)));
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function presentSummary(Course $course): array
    {
        return [
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'category' => $course->category,
            'categoryName' => $course->category_name,
            'level' => $course->level,
            'price' => (float) $course->price,
            'rating' => (float) $course->rating,
            'students' => $course->students,
            'lessonsCount' => $course->lessons_count ?? $course->lessons->count(),
            'published' => (bool) $course->published,
            'popular' => (bool) $course->popular,
            'isFree' => (bool) $course->is_free,
            'updatedAt' => $course->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentDetail(Course $course): array
    {
        return [
            ...$this->presentSummary($course),
            'summary' => $course->summary,
            'description' => $course->description,
            'thumbnail' => $course->thumbnail,
            'introVideoUrl' => $course->intro_video_url,
            'reviewCount' => $course->review_count,
            'durationHours' => $course->duration_hours,
            'instructorName' => $course->instructor_name,
            'instructorRole' => $course->instructor_role,
            'accent' => $course->accent,
            'learn' => $course->learn ?? [],
            'includes' => $course->includes ?? [],
            'lessons' => $course->lessons->map(fn ($lesson): array => [
                'id' => $lesson->id,
                'slug' => $lesson->slug,
                'title' => $lesson->title,
                'module' => $lesson->module,
                'duration' => $lesson->duration,
                'description' => $lesson->description,
                'code' => $lesson->code,
                'videoUrl' => $lesson->video_url,
                'order' => $lesson->order,
                'isFree' => (bool) $lesson->is_free,
                'materials' => $lesson->materials ?? [],
            ])->all(),
        ];
    }
}
