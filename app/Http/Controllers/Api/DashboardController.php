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

        return response()->json([
            'user' => [
                'name' => $request->user()?->name,
            ],
            'progress' => [
                'percent' => 65,
                'completed' => 3,
                'total' => 5,
            ],
            'streakDays' => 5,
            'subscription' => [
                'plan' => 'Premium',
                'status' => 'Aktif',
                'expiresAt' => '12 April 2026',
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
}
