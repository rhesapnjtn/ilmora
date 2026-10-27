<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\CertificateMail;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LearningController extends Controller
{
    public function show(Request $request, string $slug, string $lesson): JsonResponse
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

        /** @var CourseLesson $current */
        $current = $lessons[$index];
        $user = $request->user();

        $isFree = (bool) $current->is_free;
        $hasAccess = $isFree || ($user !== null && ($user->isAdmin() || $user->hasActiveSubscription()));

        if (! $hasAccess) {
            return response()->json([
                'message' => 'Materi ini memerlukan langganan aktif. Silakan pilih paket langganan untuk melanjutkan.',
                'requires_subscription' => true,
                'course' => [
                    'slug' => $course->slug,
                    'title' => $course->title,
                ],
                'lesson' => [
                    'id' => $current->slug,
                    'title' => $current->title,
                    'free' => false,
                ],
            ], 403);
        }

        if ($user !== null) {
            $user->enrollments()->firstOrCreate([
                'course_id' => $course->id,
            ], [
                'enrolled_at' => now(),
            ]);
        }

        $completedLessonIds = $user !== null
            ? $user->completedLessons()
                ->where('course_id', $course->id)
                ->pluck('course_lessons.id')
                ->all()
            : [];

        $completedCount = count($completedLessonIds);
        $totalCount = $lessons->count();
        $realProgress = $totalCount > 0 ? (int) round(($completedCount / $totalCount) * 100) : 0;

        if ($user !== null && $totalCount > 0 && $completedCount === $totalCount) {
            $this->issueCertificate($user->id, $course->id);
        }

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
                'videoUrl' => $current->video_url,
                'isCompleted' => in_array($current->id, $completedLessonIds, true),
            ],
            'lessons' => $lessons->map(fn (CourseLesson $item): array => [
                'id' => $item->slug,
                'title' => $item->title,
                'duration' => $item->duration ?? '00:00',
                'module' => $item->module,
                'free' => (bool) $item->is_free,
                'completed' => in_array($item->id, $completedLessonIds, true),
            ])->all(),
            'prev' => $index > 0 ? $lessons[$index - 1]->slug : null,
            'next' => $index < $lessons->count() - 1 ? $lessons[$index + 1]->slug : null,
            'progress' => $realProgress,
            'completedCount' => $completedCount,
        ]);
    }

    public function toggleComplete(Request $request, string $slug, string $lesson): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $course = Course::query()
            ->published()
            ->where('slug', $slug)
            ->first();

        if ($course === null) {
            return response()->json(['message' => 'Kursus tidak ditemukan.'], 404);
        }

        $currentLesson = $course->lessons()->where('slug', $lesson)->first();

        if ($currentLesson === null) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        $isFree = (bool) $currentLesson->is_free;
        $hasAccess = $isFree || $user->isAdmin() || $user->hasActiveSubscription();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'Materi ini memerlukan langganan aktif.',
                'requires_subscription' => true,
            ], 403);
        }

        $existing = $user->lessonCompletions()
            ->where('course_lesson_id', $currentLesson->id)
            ->first();

        $isCompleted = false;

        if ($existing !== null) {
            $existing->delete();
        } else {
            $user->lessonCompletions()->create([
                'course_lesson_id' => $currentLesson->id,
                'completed_at' => now(),
            ]);
            $isCompleted = true;
        }

        $enrollment = $user->enrollments()->firstOrCreate([
            'course_id' => $course->id,
        ], [
            'enrolled_at' => now(),
        ]);

        $totalLessons = $course->lessons()->count();
        $completedCount = $user->completedLessons()
            ->where('course_id', $course->id)
            ->count();

        $progress = $totalLessons > 0 ? (int) round(($completedCount / $totalLessons) * 100) : 0;

        if ($progress === 100 && $enrollment->completed_at === null) {
            $enrollment->update(['completed_at' => now()]);
        } elseif ($progress < 100 && $enrollment->completed_at !== null) {
            $enrollment->update(['completed_at' => null]);
        }

        if ($progress === 100) {
            $this->issueCertificate($user->id, $course->id);
        }

        return response()->json([
            'completed' => $isCompleted,
            'progress' => $progress,
            'completedCount' => $completedCount,
            'totalLessons' => $totalLessons,
            'courseCompleted' => $progress === 100,
            'message' => $isCompleted ? 'Pelajaran ditandai sebagai selesai.' : 'Status selesai dibatalkan.',
        ]);
    }

    private function issueCertificate(int $userId, int $courseId): void
    {
        $exists = Certificate::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();

        if ($exists) {
            return;
        }

        do {
            $code = 'ILM-'.now()->year.'-'.strtoupper(Str::random(6));
        } while (Certificate::query()->where('code', $code)->exists());

        $certificate = Certificate::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'code' => $code,
            'issued_at' => now(),
        ]);

        $certificate->load(['user', 'course']);

        if ($certificate->user !== null) {
            Mail::to($certificate->user)->send(new CertificateMail($certificate));
        }
    }
}
