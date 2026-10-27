<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function __construct(private readonly PointsService $points) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $transactions = $user->pointTransactions()
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(fn ($transaction): array => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'points' => (int) $transaction->points,
                'description' => $transaction->description,
                'createdAt' => $transaction->created_at?->toIso8601String(),
            ])
            ->all();

        return response()->json([
            'balance' => $this->points->balance($user),
            'currentStreak' => (int) $user->current_streak,
            'longestStreak' => (int) $user->longest_streak,
            'lastCheckinAt' => $user->last_checkin_at?->toDateString(),
            'transactions' => $transactions,
        ]);
    }
}
