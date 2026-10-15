<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCourseRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'students' => ['nullable', 'integer', 'min:0'],
            'duration_hours' => ['nullable', 'integer', 'min:0'],
            'instructor_name' => ['nullable', 'string', 'max:255'],
            'instructor_role' => ['nullable', 'string', 'max:255'],
            'accent' => ['nullable', 'in:primary,secondary,tertiary'],
            'popular' => ['nullable', 'boolean'],
            'is_free' => ['nullable', 'boolean'],
            'published' => ['nullable', 'boolean'],
            'learn' => ['nullable', 'array'],
            'learn.*' => ['string', 'max:500'],
            'includes' => ['nullable', 'array'],
            'includes.*' => ['string', 'max:255'],
        ];
    }
}
