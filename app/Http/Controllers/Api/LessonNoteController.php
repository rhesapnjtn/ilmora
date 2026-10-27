<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonNoteController extends Controller
{
    public function show(Request $request, string $slug, string $lesson): JsonResponse
    {
        $user = $request->user();
        $courseLesson = $this->resolveLesson($slug, $lesson);

        if ($courseLesson === null) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        $note = LessonNote::query()
            ->where('user_id', $user->id)
            ->where('course_lesson_id', $courseLesson->id)
            ->first();

        return response()->json([
            'note' => $note?->content,
        ]);
    }

    public function update(Request $request, string $slug, string $lesson): JsonResponse
    {
        $user = $request->user();
        $courseLesson = $this->resolveLesson($slug, $lesson);

        if ($courseLesson === null) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'content' => ['nullable', 'string'],
        ]);

        $note = LessonNote::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_lesson_id' => $courseLesson->id,
            ],
            [
                'content' => $validated['content'] ?? null,
            ],
        );

        return response()->json([
            'note' => $note->content,
            'message' => 'Catatan disimpan.',
        ]);
    }

    private function resolveLesson(string $slug, string $lesson): ?CourseLesson
    {
        $course = Course::query()->where('slug', $slug)->first();

        if ($course === null) {
            return null;
        }

        return $course->lessons()->where('slug', $lesson)->first();
    }
}
