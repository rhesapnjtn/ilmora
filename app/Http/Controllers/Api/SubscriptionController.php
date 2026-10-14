<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DemoCatalog;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'plans' => DemoCatalog::plans(),
            'discountPercent' => 20,
        ]);
    }
}
