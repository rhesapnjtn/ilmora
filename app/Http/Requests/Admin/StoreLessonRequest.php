<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('title') && ! $this->filled('slug')) {
            $this->merge([
                'slug' => Str::slug($this->string('title')->toString()),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $course = $this->route('course');
        $courseId = $course instanceof Course ? $course->id : $course;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('course_lessons', 'slug')->where('course_id', $courseId),
            ],
            'module' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'code' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_free' => ['nullable', 'boolean'],
            'materials' => ['nullable', 'array'],
            'materials.*.label' => ['required_with:materials', 'string', 'max:255'],
            'materials.*.size' => ['nullable', 'string', 'max:50'],
            'materials.*.type' => ['nullable', 'string', 'max:50'],
        ];
    }
}
