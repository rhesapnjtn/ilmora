<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $activeCourses = [
            [
                'slug' => 'laravel-vue-3-full-stack',
                'title' => 'Laravel 10 + Vue 3 Full Stack Web Development',
                'category' => 'Pemrograman',
                'module' => 'Modul 12: Integrasi Inertia & RESTful API',
                'progress' => 40,
            ],
            [
                'slug' => 'ui-ux-design-prototyping',
                'title' => 'UI/UX Design: Design Thinking & Figma',
                'category' => 'Desain UI/UX',
                'module' => 'Modul 8: Prototyping & Usability Testing',
                'progress' => 60,
            ],
            [
                'slug' => 'digital-marketing-growth',
                'title' => 'Digital Marketing & Social Media Content',
                'category' => 'Marketing',
                'module' => 'Modul 3: Riset Audiens & Algoritma Konten',
                'progress' => 25,
            ],
        ];

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

        return response()->json([
            'user' => [
                'name' => $user?->name,
            ],
            'progress' => [
                'percent' => 65,
                'completed' => 3,
                'total' => 5,
            ],
            'streakDays' => 5,
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
                'courses' => 3,
                'completedLessons' => 24,
                'learningMinutes' => 1440,
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
