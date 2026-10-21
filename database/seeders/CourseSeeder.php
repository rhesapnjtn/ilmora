<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Support\DemoCatalog;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Seed the catalog with the demo course data so the public site and the
     * admin panel operate on persisted records.
     */
    public function run(): void
    {
        foreach (DemoCatalog::courses() as $item) {
            $detail = DemoCatalog::find($item['slug']) ?? $item;

            $course = Course::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'category' => $item['category'],
                    'category_name' => $item['categoryName'],
                    'level' => $item['level'],
                    'price' => $item['price'],
                    'thumbnail' => '/placeholder.svg?height=300&width=500',
                    'rating' => $item['rating'],
                    'review_count' => $item['reviews'],
                    'students' => $item['students'],
                    'duration_hours' => $item['durationHours'],
                    'instructor_name' => $item['instructor']['name'],
                    'instructor_role' => $item['instructor']['role'],
                    'accent' => $item['accent'],
                    'popular' => $item['popular'],
                    'intro_video_url' => $item['introVideo'] ?? null,
                    'is_free' => $item['free'],
                    'published' => true,
                    'learn' => $detail['learn'] ?? [],
                    'includes' => collect($detail['includes'] ?? [])
                        ->pluck('label')
                        ->all(),
                ],
            );

            $this->seedLessons($course, $detail['curriculum'] ?? []);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $curriculum
     */
    private function seedLessons(Course $course, array $curriculum): void
    {
        $order = 0;

        foreach ($curriculum as $module) {
            foreach ($module['lessons'] as $lesson) {
                $order++;

                $lessonData = DemoCatalog::lesson($course->slug, $lesson['id']);
                $content = $lessonData['lesson'] ?? [];

                CourseLesson::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'slug' => $lesson['id'],
                    ],
                    [
                        'title' => $lesson['title'],
                        'module' => $module['title'],
                        'duration' => $lesson['duration'],
                        'description' => $content['description'] ?? null,
                        'code' => $content['code'] ?? null,
                        'materials' => $content['materials'] ?? [],
                        'order' => $order,
                        'is_free' => (bool) $lesson['free'],
                    ],
                );
            }
        }
    }
}
