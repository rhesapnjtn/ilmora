<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $top = User::query()
            ->withSum('pointTransactions', 'points')
            ->orderByDesc('point_transactions_sum_points')
            ->limit(20)
            ->get(['id', 'name', 'avatar', 'current_streak']);

        $entries = $top->map(fn (User $user, int $index): array => [
            'rank' => $index + 1,
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar_url,
            'points' => (int) ($user->point_transactions_sum_points ?? 0),
            'streak' => (int) $user->current_streak,
        ])->values()->all();

        $user = $request->user();
        $rank = null;
        if ($user !== null) {
            $userPoints = (int) $user->pointTransactions()->sum('points');
            $rank = User::query()
                ->withSum('pointTransactions', 'points')
                ->where('point_transactions_sum_points', '>', $userPoints)
                ->count() + 1;
        }

        return response()->json([
            'entries' => $entries,
            'userRank' => $rank,
            'userPoints' => $user !== null ? (int) $user->pointTransactions()->sum('points') : null,
        ]);
    }
}
