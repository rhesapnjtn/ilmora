<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = $request->user()
            ->wishlists()
            ->with('course')
            ->latest()
            ->get()
            ->pluck('course')
            ->filter()
            ->values();

        return response()->json(['courses' => $courses]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $course = Course::query()->where('slug', $slug)->firstOrFail();

        Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return response()->json(['wishlisted' => true, 'message' => 'Ditambahkan ke favorit.']);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $course = Course::query()->where('slug', $slug)->firstOrFail();

        Wishlist::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->delete();

        return response()->json(['wishlisted' => false, 'message' => 'Dihapus dari favorit.']);
    }

    public function toggle(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $course = Course::query()->where('slug', $slug)->firstOrFail();

        $existing = Wishlist::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return response()->json(['wishlisted' => false]);
        }

        Wishlist::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        return response()->json(['wishlisted' => true]);
    }
}
