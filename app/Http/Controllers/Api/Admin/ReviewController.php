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
        $status = $request->string('status')->toString();
        $reviews = CourseReview::query()
            ->with(['user:id,name,email', 'course:id,title'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return response()->json(['reviews' => $reviews]);
    }

    public function updateStatus(Request $request, CourseReview $review): JsonResponse
    {
        $data = $request->validate(['status' => ['required', 'in:pending,approved,rejected']]);
        $review->update(['status' => $data['status']]);

        return response()->json(['review' => $review->fresh(['user', 'course'])]);
    }

    public function destroy(CourseReview $review): JsonResponse
    {
        $review->delete();

        return response()->json(['message' => 'Ulasan dihapus.']);
    }
}
