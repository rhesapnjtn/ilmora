<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Course;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends StoreCourseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $course = $this->route('course');
        $courseId = $course instanceof Course ? $course->id : $course;

        return array_merge(parent::rules(), [
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('courses', 'slug')->ignore($courseId),
            ],
        ]);
    }
}
