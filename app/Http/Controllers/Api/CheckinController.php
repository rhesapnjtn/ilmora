<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function __construct(private readonly PointsService $points) {}

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $result = $this->points->checkin($user);

        return response()->json([
            'message' => $result['already_checked_in']
                ? 'Sudah check-in hari ini.'
                : 'Check-in berhasil! +'.$result['awarded'].' poin.',
            'awarded' => $result['awarded'],
            'streak' => $result['streak'],
            'alreadyCheckedIn' => $result['already_checked_in'],
            'balance' => $result['balance'],
        ]);
    }
}
