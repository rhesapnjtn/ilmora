<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\BadgeService;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RedeemController extends Controller
{
    public function __construct(
        private readonly PointsService $points,
        private readonly BadgeService $badges,
    ) {}

    public function store(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $course = Course::query()->published()->where('slug', $slug)->first();

        if ($course === null) {
            return response()->json(['message' => 'Kursus tidak ditemukan.'], 404);
        }

        try {
            $redemption = $this->points->redeemCourse($user, $course);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $newBadges = $this->badges->evaluate($user->fresh());

        return response()->json([
            'message' => 'Kursus berhasil ditukar dengan poin.',
            'redemption' => [
                'id' => $redemption->id,
                'courseSlug' => $course->slug,
                'pointsSpent' => $redemption->points_spent,
            ],
            'balance' => $this->points->balance($user),
            'newBadges' => $newBadges,
        ], 201);
    }
}
