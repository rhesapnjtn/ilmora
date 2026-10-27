<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'title' => $title,
            'summary' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['pemrograman', 'desain', 'marketing', 'data']),
            'category_name' => fake()->randomElement(['Pemrograman', 'Desain UI/UX', 'Marketing', 'Data Science']),
            'level' => fake()->randomElement(['Pemula', 'Menengah', 'Lanjutan']),
            'price' => fake()->randomElement([99000, 149000, 249000, 349000]),
            'rating' => fake()->randomFloat(1, 4.0, 5.0),
            'review_count' => fake()->numberBetween(10, 2000),
            'students' => fake()->numberBetween(100, 10000),
            'duration_hours' => fake()->numberBetween(4, 20),
            'instructor_name' => fake()->name(),
            'instructor_role' => fake()->jobTitle(),
            'accent' => fake()->randomElement(['primary', 'secondary', 'tertiary']),
            'popular' => false,
            'is_free' => false,
            'published' => true,
            'learn' => ['Menguasai konsep dasar', 'Membangun proyek nyata'],
            'includes' => ['Sertifikat penyelesaian', 'Akses selamanya'],
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published' => false,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_free' => true,
            'price' => 0,
        ]);
    }
}
