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
use App\Notifications\LessonReplyNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscussionController extends Controller
{
    private const DEFAULT_PER_PAGE = 10;

    private const MAX_PER_PAGE = 50;

    public function index(Request $request, string $slug, string $lesson): JsonResponse
    {
        $lessonModel = $this->resolveLesson($slug, $lesson);

        if ($lessonModel === null) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        $perPage = (int) $request->integer('per_page', self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

        $paginator = $lessonModel->discussions()
            ->whereNull('parent_id')
            ->with('user', 'replies.user')
            ->paginate($perPage);

        return response()->json([
            'discussions' => collect($paginator->items())
                ->map(fn (LessonDiscussion $discussion): array => $this->presentDiscussion($discussion, $request->user()))
                ->all(),
            'meta' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
                'hasMore' => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function store(StoreDiscussionRequest $request, string $slug, string $lesson): JsonResponse
    {
        $lessonModel = $this->resolveLesson($slug, $lesson);

        if ($lessonModel === null) {
            return response()->json(['message' => 'Pelajaran tidak ditemukan.'], 404);
        }

        $parent = null;

        if ($request->filled('parent_id')) {
            $parent = $lessonModel->discussions()->whereKey((int) $request->integer('parent_id'))->first();

            if ($parent === null) {
                return response()->json(['message' => 'Diskusi utama tidak ditemukan.'], 404);
            }

            if ($parent->parent_id !== null) {
                return response()->json(['message' => 'Balasan hanya dapat ditujukan ke diskusi utama, bukan ke balasan lain.'], 422);
            }
        }

        $discussion = $lessonModel->discussions()->create([
            'user_id' => $request->user()->id,
            'body' => $request->string('body')->toString(),
            'parent_id' => $parent?->id,
        ]);

        if ($parent !== null && (int) $parent->user_id !== (int) $request->user()->id) {
            $parent->user?->notify(new LessonReplyNotification($discussion, $parent));
        }

        $payload = $parent === null
            ? $this->presentDiscussion($discussion->load('user', 'replies.user'), $request->user())
            : $this->presentReply($discussion->load('user'), $request->user());

        return response()->json([
            'message' => $parent === null ? 'Diskusi berhasil ditambahkan.' : 'Balasan berhasil dikirim.',
            'discussion' => $payload,
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
            'discussion' => $this->presentReply($discussion->fresh()->load('user'), $request->user()),
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

    public function accept(Request $request, string $slug, string $lesson, LessonDiscussion $discussion): JsonResponse
    {
        $lessonModel = $this->resolveLesson($slug, $lesson);

        if ($lessonModel === null || ! $this->belongsToLesson($discussion, $lessonModel)) {
            return response()->json(['message' => 'Diskusi tidak ditemukan.'], 404);
        }

        if ($discussion->parent_id === null) {
            return response()->json(['message' => 'Hanya balasan yang dapat ditandai sebagai jawaban terbaik.'], 422);
        }

        $parent = $discussion->parent()->first();

        if ($parent === null || ! $this->belongsToLesson($parent, $lessonModel)) {
            return response()->json(['message' => 'Diskusi tidak ditemukan.'], 404);
        }

        if (! $this->canModerate($request, $parent)) {
            return response()->json(['message' => 'Anda tidak diizinkan menandai jawaban pada diskusi ini.'], 403);
        }

        DB::transaction(function () use ($parent, $discussion): void {
            LessonDiscussion::query()
                ->where('parent_id', $parent->id)
                ->update(['is_answer' => false]);

            $discussion->update(['is_answer' => true]);
        });

        return response()->json([
            'message' => 'Jawaban terbaik berhasil ditandai.',
            'discussion' => $this->presentReply($discussion->fresh()->load('user'), $request->user()),
        ]);
    }

    public function unaccept(Request $request, string $slug, string $lesson, LessonDiscussion $discussion): JsonResponse
    {
        $lessonModel = $this->resolveLesson($slug, $lesson);

        if ($lessonModel === null || ! $this->belongsToLesson($discussion, $lessonModel)) {
            return response()->json(['message' => 'Diskusi tidak ditemukan.'], 404);
        }

        if ($discussion->parent_id === null) {
            return response()->json(['message' => 'Hanya balasan yang dapat ditandai sebagai jawaban terbaik.'], 422);
        }

        $parent = $discussion->parent()->first();

        if ($parent === null || ! $this->belongsToLesson($parent, $lessonModel)) {
            return response()->json(['message' => 'Diskusi tidak ditemukan.'], 404);
        }

        if (! $this->canModerate($request, $parent)) {
            return response()->json(['message' => 'Anda tidak diizinkan mengubah jawaban pada diskusi ini.'], 403);
        }

        $discussion->update(['is_answer' => false]);

        return response()->json([
            'message' => 'Tanda jawaban terbaik berhasil dibatalkan.',
            'discussion' => $this->presentReply($discussion->fresh()->load('user'), $request->user()),
        ]);
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
    private function presentDiscussion(LessonDiscussion $discussion, ?User $viewer): array
    {
        $hasAcceptedAnswer = $discussion->relationLoaded('replies')
            && $discussion->replies->contains(fn (LessonDiscussion $reply): bool => (bool) $reply->is_answer);

        return [
            ...$this->presentReply($discussion, $viewer),
            'hasAcceptedAnswer' => $hasAcceptedAnswer,
            'replies' => $discussion->relationLoaded('replies')
                ? $discussion->replies
                    ->map(fn (LessonDiscussion $reply): array => $this->presentReply($reply, $viewer))
                    ->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentReply(LessonDiscussion $discussion, ?User $viewer): array
    {
        $user = $discussion->user;
        $canModerate = $viewer !== null
            && ((int) $discussion->user_id === (int) $viewer->id || $viewer->isAdmin());

        return [
            'id' => $discussion->id,
            'body' => $discussion->body,
            'isAnswer' => (bool) $discussion->is_answer,
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
