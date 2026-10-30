<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $months = max(3, min(24, (int) $request->input('months', 6)));

        $successful = Payment::query()->where('status', 'success');
        $revenue = (float) $successful->clone()->sum('amount');
        $revenueThisMonth = (float) $successful->clone()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $monthsTrend = collect(range($months - 1, 0))->map(function (int $offset): array {
            $date = Carbon::now()->subMonths($offset);

            return [
                'label' => $date->translatedFormat('M'),
                'key' => $date->format('Y-m'),
                'revenue' => (float) Payment::query()
                    ->where('status', 'success')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount'),
                'users' => User::query()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        })->values();

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

        $totalEnrollments = (int) $topCourses->sum('enrollments');

        $paymentsByStatus = [
            'pending' => Payment::query()->where('status', 'pending')->count(),
            'success' => Payment::query()->where('status', 'success')->count(),
            'failed' => Payment::query()->where('status', 'failed')->count(),
            'refunded' => Payment::query()->where('status', 'refunded')->count(),
        ];
        $paymentsTotal = array_sum($paymentsByStatus);
        $successRate = $paymentsTotal > 0 ? round(($paymentsByStatus['success'] / $paymentsTotal) * 100, 1) : 0.0;

        return response()->json([
            'overview' => [
                'totalUsers' => User::query()->count(),
                'totalAdmins' => User::query()->where('role', 'admin')->count(),
                'totalCourses' => Course::query()->count(),
                'publishedCourses' => Course::query()->published()->count(),
                'totalSubscriptions' => Subscription::query()->count(),
                'activeSubscriptions' => Subscription::query()->active()->count(),
                'totalRevenue' => $revenue,
                'revenueThisMonth' => $revenueThisMonth,
                'pendingPayments' => $paymentsByStatus['pending'],
                'totalEnrollments' => $totalEnrollments,
                'successRate' => $successRate,
            ],
            'trend' => $monthsTrend,
            'topCourses' => $topCourses,
            'subscriptionsByStatus' => [
                'pending' => Subscription::query()->where('status', 'pending')->count(),
                'active' => Subscription::query()->where('status', 'active')->count(),
                'expired' => Subscription::query()->where('status', 'expired')->count(),
                'canceled' => Subscription::query()->where('status', 'canceled')->count(),
            ],
            'paymentsByStatus' => $paymentsByStatus,
        ]);
    }
}
