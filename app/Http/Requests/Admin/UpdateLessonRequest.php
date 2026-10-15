<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends StoreLessonRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $course = $this->route('course');
        $courseId = $course instanceof Course ? $course->id : $course;

        $lesson = $this->route('lesson');
        $lessonId = $lesson instanceof CourseLesson ? $lesson->id : $lesson;

        return array_merge(parent::rules(), [
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('course_lessons', 'slug')
                    ->where('course_id', $courseId)
                    ->ignore($lessonId),
            ],
        ]);
    }
}
