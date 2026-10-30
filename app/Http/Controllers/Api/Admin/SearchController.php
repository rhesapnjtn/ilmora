<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->string('q')->toString();
        if (strlen($q) < 2) {
            return response()->json(['users' => [], 'courses' => []]);
        }

        $users = User::query()
            ->where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->limit(8)
            ->get(['id', 'name', 'email', 'role']);

        $courses = Course::query()
            ->where('title', 'like', "%{$q}%")
            ->limit(8)
            ->get(['id', 'title', 'slug', 'published']);

        return response()->json(['users' => $users, 'courses' => $courses]);
    }
}
