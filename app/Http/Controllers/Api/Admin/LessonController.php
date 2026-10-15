<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonRequest;
use App\Http\Requests\Admin\UpdateLessonRequest;
use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class LessonController extends Controller
{
    public function store(StoreLessonRequest $request, Course $course): JsonResponse
    {
        $data = $this->normalize($request->validated(), $course);

        $lesson = $course->lessons()->create($data);

        return response()->json([
            'message' => 'Pelajaran berhasil ditambahkan.',
            'lesson' => $this->present($lesson),
        ], 201);
    }

    public function update(UpdateLessonRequest $request, Course $course, CourseLesson $lesson): JsonResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $data = $this->normalize($request->validated(), $course, $lesson);

        $lesson->update($data);

        return response()->json([
            'message' => 'Pelajaran berhasil diperbarui.',
            'lesson' => $this->present($lesson->fresh()),
        ]);
    }

    public function destroy(Course $course, CourseLesson $lesson): JsonResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $lesson->delete();

        return response()->json(['message' => 'Pelajaran berhasil dihapus.']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, Course $course, ?CourseLesson $lesson = null): array
    {
        $data['slug'] = filled($data['slug'] ?? null)
            ? $data['slug']
            : Str::slug($data['title'] ?? $lesson?->title ?? '');

        $data['order'] = $data['order'] ?? $lesson?->order ?? $course->lessons()->max('order') + 1;
        $data['is_free'] = (bool) ($data['is_free'] ?? $lesson?->is_free ?? false);

        if (array_key_exists('materials', $data)) {
            $data['materials'] = array_values((array) $data['materials']);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function present(CourseLesson $lesson): array
    {
        return [
            'id' => $lesson->id,
            'slug' => $lesson->slug,
            'title' => $lesson->title,
            'module' => $lesson->module,
            'duration' => $lesson->duration,
            'description' => $lesson->description,
            'code' => $lesson->code,
            'videoUrl' => $lesson->video_url,
            'order' => $lesson->order,
            'isFree' => (bool) $lesson->is_free,
            'materials' => $lesson->materials ?? [],
        ];
    }
}
