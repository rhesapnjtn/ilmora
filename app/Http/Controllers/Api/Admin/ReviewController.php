<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reviews = CourseReview::query()
            ->with(['user:id,name,email', 'course:id,title,slug'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'reviews' => $reviews->items(),
            'meta' => [
                'page' => $reviews->currentPage(),
                'total' => $reviews->total(),
                'lastPage' => $reviews->lastPage(),
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $review = CourseReview::query()->findOrFail($id);
        $review->delete();

        return response()->json(['message' => 'Ulasan dihapus.']);
    }
}
