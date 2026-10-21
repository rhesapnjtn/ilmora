<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDiscussionRequest;
use App\Http\Requests\UpdateDiscussionRequest;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonDiscussion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function index(Request $request, string $slug, string $lesson): JsonResponse
    {
        $lessonModel = $this->resolveLesson($slug, $lesson);

        if ($lessonModel === null) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        $discussions = $lessonModel->discussions()
            ->with('user')
            ->get()
            ->map(fn (LessonDiscussion $discussion): array => $this->present($discussion, $request->user()))
            ->all();

        return response()->json(['discussions' => $discussions]);
    }

    public function store(StoreDiscussionRequest $request, string $slug, string $lesson): JsonResponse
    {
        $lessonModel = $this->resolveLesson($slug, $lesson);

        if ($lessonModel === null) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        $discussion = $lessonModel->discussions()->create([
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->toString(),
        ]);

        return response()->json([
            'message' => 'Diskusi berhasil ditambahkan.',
            'discussion' => $this->present($discussion->load('user'), $request->user()),
        ], 201);
    }

    public function update(UpdateDiscussionRequest $request, string $slug, string $lesson, LessonDiscussion $discussion): JsonResponse
    {
        $lessonModel = $this->resolveLesson($slug, $lesson);

        if ($lessonModel === null || ! $this->belongsToLesson($discussion, $lessonModel)) {
            return response()->json(['message' => 'Diskusi tidak ditemukan.'], 404);
        }

        if (! $this->canModerate($request, $discussion)) {
            return response()->json(['message' => 'Anda tidak diizinkan mengubah diskusi ini.'], 403);
        }

        $discussion->update(['body' => $request->string('body')->toString()]);

        return response()->json([
            'message' => 'Diskusi berhasil diperbarui.',
            'discussion' => $this->present($discussion->fresh()->load('user'), $request->user()),
        ]);
    }

    public function destroy(Request $request, string $slug, string $lesson, LessonDiscussion $discussion): JsonResponse
    {
        $lessonModel = $this->resolveLesson($slug, $lesson);

        if ($lessonModel === null || ! $this->belongsToLesson($discussion, $lessonModel)) {
            return response()->json(['message' => 'Diskusi tidak ditemukan.'], 404);
        }

        if (! $this->canModerate($request, $discussion)) {
            return response()->json(['message' => 'Anda tidak diizinkan menghapus diskusi ini.'], 403);
        }

        $discussion->delete();

        return response()->json(['message' => 'Diskusi berhasil dihapus.']);
    }

    private function resolveLesson(string $slug, string $lesson): ?CourseLesson
    {
        $course = Course::query()
            ->published()
            ->where('slug', $slug)
            ->first();

        if ($course === null) {
            return null;
        }

        return $course->lessons()
            ->where('slug', $lesson)
            ->first();
    }

    private function belongsToLesson(LessonDiscussion $discussion, CourseLesson $lesson): bool
    {
        return (int) $discussion->lesson_id === (int) $lesson->id;
    }

    private function canModerate(Request $request, LessonDiscussion $discussion): bool
    {
        $user = $request->user();

        return $user !== null
            && ((int) $discussion->user_id === (int) $user->id || $user->isAdmin());
    }

    /**
     * @return array<string, mixed>
     */
    private function present(LessonDiscussion $discussion, ?User $viewer): array
    {
        $user = $discussion->user;
        $canModerate = $viewer !== null
            && ((int) $discussion->user_id === (int) $viewer->id || $viewer->isAdmin());

        return [
            'id' => $discussion->id,
            'body' => $discussion->body,
            'createdAt' => $discussion->created_at?->toIso8601String(),
            'updatedAt' => $discussion->updated_at?->toIso8601String(),
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'avatarUrl' => $user?->avatar_url,
                'role' => $user?->role,
            ],
            'canUpdate' => $canModerate,
            'canDelete' => $canModerate,
        ];
    }
}
