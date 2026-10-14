<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DemoCatalog;
use Illuminate\Http\JsonResponse;

class LearningController extends Controller
{
    public function show(string $slug, string $lesson): JsonResponse
    {
        $data = DemoCatalog::lesson($slug, $lesson);

        if ($data === null) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        return response()->json($data);
    }
}
