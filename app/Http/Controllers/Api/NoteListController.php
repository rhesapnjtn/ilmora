<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteListController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notes = $request->user()
            ?->lessonNotes()
            ->with(['lesson.course'])
            ->latest()
            ->get()
            ->map(fn ($note): array => [
                'id' => $note->id,
                'content' => $note->content,
                'updatedAt' => $note->updated_at?->toDateTimeString(),
                'lesson' => [
                    'slug' => $note->lesson?->slug,
                    'title' => $note->lesson?->title,
                ],
                'course' => [
                    'slug' => $note->lesson?->course?->slug,
                    'title' => $note->lesson?->course?->title,
                ],
            ])->values()->all() ?? [];

        return response()->json(['notes' => $notes]);
    }
}
