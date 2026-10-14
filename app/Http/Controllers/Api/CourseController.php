<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DemoCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->lower()->toString();
        $category = $request->string('category')->toString();
        $level = $request->string('level')->toString();
        $price = $request->string('price')->toString();
        $rating = $request->float('rating');

        $courses = collect(DemoCatalog::courses())
            ->when($search !== '', function ($collection) use ($search) {
                return $collection->filter(function (array $course) use ($search): bool {
                    return str_contains(strtolower($course['title']), $search)
                        || str_contains(strtolower($course['summary']), $search)
                        || str_contains(strtolower($course['categoryName']), $search);
                });
            })
            ->when($category !== '', fn ($collection) => $collection->where('category', $category))
            ->when($level !== '', fn ($collection) => $collection->where('level', $level))
            ->when($price === 'free', fn ($collection) => $collection->where('free', true))
            ->when($price === 'premium', fn ($collection) => $collection->where('free', false))
            ->when($rating > 0, fn ($collection) => $collection->where('rating', '>=', $rating))
            ->values();

        $perPage = 6;
        $page = max(1, $request->integer('page', 1));
        $total = $courses->count();
        $items = $courses->forPage($page, $perPage)->values();

        return response()->json([
            'courses' => $items,
            'categories' => DemoCatalog::categories(),
            'levels' => DemoCatalog::levels(),
            'meta' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => max(1, (int) ceil($total / $perPage)),
            ],
            'filters' => [
                'search' => $request->string('search')->toString(),
                'category' => $category,
                'level' => $level,
                'price' => $price,
                'rating' => $rating,
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $course = DemoCatalog::find($slug);

        if ($course === null) {
            return response()->json(['message' => 'Kursus tidak ditemukan.'], 404);
        }

        $course['lessonsTotal'] = collect($course['curriculum'])
            ->sum(fn (array $module): int => count($module['lessons']));

        return response()->json(['course' => $course]);
    }
}
