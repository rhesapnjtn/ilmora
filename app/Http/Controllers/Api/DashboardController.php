<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeSubscription = $user?->subscriptions()
            ->where('status', 'active')
            ->latest('id')
            ->first();

        $pendingSubscription = $user?->subscriptions()
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        $subscription = $activeSubscription ?? $pendingSubscription;

        /** @var Collection<int, CourseEnrollment> $enrollments */
        $enrollments = $user?->enrollments()
            ->with(['course.lessons'])
            ->latest('enrolled_at')
            ->get()
            ?? collect();

        $completedLessonIds = $user?->lessonCompletions()->pluck('course_lesson_id')->all() ?? [];

        $activeCourses = $enrollments->map(function (CourseEnrollment $enrollment) use ($completedLessonIds): array {
            $course = $enrollment->course;
            $lessons = $course->lessons;
            $totalLessons = $lessons->count();

            $completedForCourse = $lessons->filter(
                fn ($l) => in_array($l->id, $completedLessonIds, true)
            )->count();

            $progress = $totalLessons > 0
                ? (int) round(($completedForCourse / $totalLessons) * 100)
                : 0;

            $currentLesson = $lessons->first(
                fn ($l) => ! in_array($l->id, $completedLessonIds, true)
            );

            return [
                'slug' => $course->slug,
                'title' => $course->title,
                'category' => $course->category_name,
                'module' => $currentLesson?->module ?? $lessons->last()?->module ?? '-',
                'progress' => $progress,
                'completedCount' => $completedForCourse,
                'totalLessons' => $totalLessons,
            ];
        })->values()->all();

        $totalCompletedLessons = count($completedLessonIds);

        $learningMinutes = 0;

        if ($totalCompletedLessons > 0) {
            $completedLessonRecords = $user?->lessonCompletions()
                ->join('course_lessons', 'course_lesson_completions.course_lesson_id', '=', 'course_lessons.id')
                ->select('course_lessons.duration')
                ->get();

            $learningMinutes = (int) round($completedLessonRecords
                ->sum(function ($row): int {
                    $parts = explode(':', (string) ($row->duration ?? '0:0'));
                    $minutes = (int) ($parts[0] ?? 0);
                    $seconds = (int) ($parts[1] ?? 0);

                    return $minutes * 60 + $seconds;
                }) / 60);
        }

        $completedCoursesCount = $enrollments->filter(
            fn (CourseEnrollment $e) => $e->completed_at !== null
        )->count();

        $overallProgress = 0;

        if ($enrollments->isNotEmpty()) {
            $totalLessonsAll = $enrollments->sum(
                fn (CourseEnrollment $e) => $e->course->lessons->count()
            );

            if ($totalLessonsAll > 0) {
                $overallProgress = (int) round(($totalCompletedLessons / $totalLessonsAll) * 100);
            }
        }

        return response()->json([
            'user' => [
                'name' => $user?->name,
            ],
            'progress' => [
                'percent' => $overallProgress,
                'completed' => $completedCoursesCount,
                'total' => $enrollments->count(),
            ],
            'streakDays' => 0,
            'subscription' => [
                'id' => $subscription?->id,
                'plan' => $subscription?->plan,
                'code' => $subscription?->status,
                'status' => $this->statusLabel($subscription?->status),
                'billingCycle' => $subscription?->billing_cycle,
                'expiresAt' => $subscription?->expires_at?->translatedFormat('d F Y'),
                'cancelAtPeriodEnd' => (bool) $subscription?->cancel_at_period_end,
                'hasActive' => $activeSubscription !== null,
            ],
            'stats' => [
                'courses' => $enrollments->count(),
                'completedLessons' => $totalCompletedLessons,
                'learningMinutes' => $learningMinutes,
            ],
            'activeCourses' => $activeCourses,
            'enrolledCourses' => $activeCourses,
        ]);
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'active' => 'Aktif',
            'pending' => 'Menunggu Pembayaran',
            'expired' => 'Kedaluwarsa',
            'canceled' => 'Dibatalkan',
            default => 'Belum Berlangganan',
        };
    }
}
