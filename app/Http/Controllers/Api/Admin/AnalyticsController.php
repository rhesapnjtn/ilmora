<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function index(): JsonResponse
    {
        $revenue = (float) Payment::query()->where('status', 'paid')->sum('amount');
        $revenueThisMonth = (float) Payment::query()
            ->where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $topCourses = Course::query()
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->limit(5)
            ->get(['id', 'title', 'slug'])
            ->map(fn (Course $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'enrollments' => $c->enrollments_count,
            ]);

        return response()->json([
            'overview' => [
                'totalUsers' => User::query()->count(),
                'totalAdmins' => User::query()->where('role', 'admin')->count(),
                'totalCourses' => Course::query()->count(),
                'publishedCourses' => Course::query()->where('is_published', true)->count(),
                'totalSubscriptions' => Subscription::query()->count(),
                'activeSubscriptions' => Subscription::query()->where('status', 'active')->count(),
                'totalRevenue' => $revenue,
                'revenueThisMonth' => $revenueThisMonth,
                'pendingPayments' => Payment::query()->where('status', 'pending')->count(),
            ],
            'topCourses' => $topCourses,
            'subscriptionsByStatus' => [
                'pending' => Subscription::query()->where('status', 'pending')->count(),
                'active' => Subscription::query()->where('status', 'active')->count(),
                'expired' => Subscription::query()->where('status', 'expired')->count(),
                'canceled' => Subscription::query()->where('status', 'canceled')->count(),
            ],
        ]);
    }
}
