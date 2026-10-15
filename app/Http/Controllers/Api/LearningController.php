<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Http\JsonResponse;

class LearningController extends Controller
{
    public function show(string $slug, string $lesson): JsonResponse
    {
        $course = Course::query()
            ->published()
            ->with('lessons')
            ->where('slug', $slug)
            ->first();

        if ($course === null) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        $lessons = $course->lessons->values();
        $index = $lessons->search(fn (CourseLesson $item): bool => $item->slug === $lesson);

        if ($index === false) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        $current = $lessons[$index];

        return response()->json([
            'course' => [
                'slug' => $course->slug,
                'title' => $course->title,
                'instructor' => [
                    'name' => $course->instructor_name,
                    'role' => $course->instructor_role,
                ],
                'level' => $course->level,
                'lessonsCount' => $lessons->count(),
            ],
            'lesson' => [
                'id' => $current->slug,
                'title' => $current->title,
                'duration' => $current->duration ?? '00:00',
                'free' => (bool) $current->is_free,
                'module' => $current->module,
                'index' => $index + 1,
                'total' => $lessons->count(),
                'description' => $current->description,
                'code' => $current->code,
                'materials' => $current->materials ?? [],
            ],
            'lessons' => $lessons->map(fn (CourseLesson $item): array => [
                'id' => $item->slug,
                'title' => $item->title,
                'duration' => $item->duration ?? '00:00',
                'module' => $item->module,
            ])->all(),
            'prev' => $index > 0 ? $lessons[$index - 1]->slug : null,
            'next' => $index < $lessons->count() - 1 ? $lessons[$index + 1]->slug : null,
            'progress' => (int) $course->progress,
        ]);
    }
}
