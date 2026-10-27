<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseLesson>
 */
class CourseLessonFactory extends Factory
{
    protected $model = CourseLesson::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'course_id' => Course::factory(),
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'title' => $title,
            'module' => 'Modul '.fake()->numberBetween(1, 10),
            'duration' => sprintf('%02d:%02d', fake()->numberBetween(5, 30), fake()->numberBetween(0, 59)),
            'description' => fake()->paragraph(),
            'code' => null,
            'materials' => null,
            'video_url' => null,
            'order' => fake()->numberBetween(1, 50),
            'is_free' => false,
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_free' => true,
        ]);
    }
}
