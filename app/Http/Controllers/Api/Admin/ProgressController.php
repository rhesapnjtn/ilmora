<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ProgressController extends Controller
{
    public function index(): JsonResponse
    {
        $totalEnrollments = CourseEnrollment::query()->count();
        $completedEnrollments = CourseEnrollment::query()->whereNotNull('completed_at')->count();

        $topCourses = Course::query()
            ->withCount(['enrollments'])
            ->orderByDesc('enrollments_count')
            ->limit(5)
            ->get(['id', 'title'])
            ->map(fn (Course $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'enrollments' => $c->enrollments_count,
            ]);

        return response()->json([
            'totalUsers' => User::query()->where('role', 'user')->count(),
            'totalEnrollments' => $totalEnrollments,
            'completedEnrollments' => $completedEnrollments,
            'completionRate' => $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 1) : 0.0,
            'activeLearners' => CourseEnrollment::query()->whereNull('completed_at')->distinct('user_id')->count('user_id'),
            'topCourses' => $topCourses,
        ]);
    }
}
