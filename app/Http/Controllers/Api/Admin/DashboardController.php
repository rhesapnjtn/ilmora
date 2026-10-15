<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $successfulPayments = Payment::query()->where('status', 'success');

        return response()->json([
            'stats' => [
                'users' => User::query()->where('role', 'user')->count(),
                'admins' => User::query()->where('role', 'admin')->count(),
                'courses' => Course::query()->count(),
                'publishedCourses' => Course::query()->published()->count(),
                'subscriptions' => Subscription::query()->count(),
                'activeSubscriptions' => Subscription::query()->active()->count(),
                'payments' => Payment::query()->count(),
                'pendingPayments' => Payment::query()->where('status', 'pending')->count(),
                'revenue' => (float) $successfulPayments->sum('amount'),
            ],
            'recentUsers' => User::query()
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'role', 'created_at']),
            'recentPayments' => Payment::query()
                ->with(['user:id,name,email', 'course:id,title,slug'])
                ->latest()
                ->take(5)
                ->get(),
            'subscriptionBreakdown' => [
                'active' => Subscription::query()->where('status', 'active')->count(),
                'expired' => Subscription::query()->where('status', 'expired')->count(),
                'canceled' => Subscription::query()->where('status', 'canceled')->count(),
            ],
        ]);
    }
}
