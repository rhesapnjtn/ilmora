<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseReviewController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $course = Course::query()->where('slug', $slug)->firstOrFail();

        $reviews = $course->reviews()->with('user:id,name,avatar')->latest()->get();

        $average = $reviews->isEmpty() ? (float) $course->rating : round($reviews->avg('rating'), 2);

        return response()->json([
            'reviews' => $reviews,
            'average' => $average,
            'count' => $reviews->count(),
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $course = Course::query()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = CourseReview::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'rating' => $validated['rating'],
                'body' => $validated['body'] ?? null,
            ],
        );

        return response()->json(['review' => $review, 'message' => 'Ulasan disimpan.'], 201);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $course = Course::query()->where('slug', $slug)->firstOrFail();

        CourseReview::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->delete();

        return response()->json(['message' => 'Ulasan dihapus.']);
    }
}
