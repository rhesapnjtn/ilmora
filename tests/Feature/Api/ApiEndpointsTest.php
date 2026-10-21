<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\CourseLesson;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CourseSeeder::class);
    }

    public function test_courses_endpoint_is_public(): void
    {
        $this->getJson('/api/courses')
            ->assertOk()
            ->assertJsonStructure(['courses', 'categories', 'levels', 'meta' => ['page', 'total', 'lastPage'], 'filters']);
    }

    public function test_courses_endpoint_filters_by_category(): void
    {
        $this->getJson('/api/courses?category=desain')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('courses.0.category', 'desain');
    }

    public function test_course_detail_is_public(): void
    {
        $this->getJson('/api/courses/laravel-vue-3-full-stack')
            ->assertOk()
            ->assertJsonStructure(['course' => ['slug', 'title', 'curriculum', 'learn', 'includes']]);
    }

    public function test_unknown_course_detail_returns_404(): void
    {
        $this->getJson('/api/courses/not-a-real-course')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_course_detail_exposes_intro_video_url(): void
    {
        $this->getJson('/api/courses/laravel-vue-3-full-stack')
            ->assertOk()
            ->assertJsonPath('course.introVideoUrl', 'https://youtu.be/acxM3TvA4Qo?si=zCkJxp3kAFdBbiz5');
    }

    public function test_course_detail_intro_video_url_is_null_when_not_set(): void
    {
        $this->getJson('/api/courses/ui-ux-design-prototyping')
            ->assertOk()
            ->assertJsonPath('course.introVideoUrl', null);
    }

    public function test_lesson_requires_authentication(): void
    {
        $this->getJson('/api/courses/laravel-vue-3-full-stack/lessons/2-1')->assertStatus(401);
    }

    public function test_authenticated_user_can_view_lesson(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/courses/laravel-vue-3-full-stack/lessons/2-1')
            ->assertOk()
            ->assertJsonStructure(['course', 'lesson', 'lessons', 'prev', 'next', 'progress']);
    }

    public function test_lesson_exposes_video_url(): void
    {
        $user = User::factory()->create();
        $lesson = CourseLesson::query()->where('slug', '2-1')->firstOrFail();
        $lesson->update(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/courses/laravel-vue-3-full-stack/lessons/2-1')
            ->assertOk()
            ->assertJsonPath('lesson.videoUrl', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');
    }

    public function test_certificates_require_authentication(): void
    {
        $this->getJson('/api/certificates')->assertStatus(401);
    }

    public function test_authenticated_user_can_list_certificates(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/certificates')
            ->assertOk()
            ->assertJsonStructure(['certificates', 'recipient']);
    }

    public function test_authenticated_user_can_view_certificate(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/certificates/ilr-cert-2026-001')
            ->assertOk()
            ->assertJsonStructure(['certificate' => ['id', 'courseTitle', 'recipient']]);
    }

    public function test_plans_endpoint_is_public(): void
    {
        $this->seed(PlanSeeder::class);

        $this->getJson('/api/plans')
            ->assertOk()
            ->assertJsonStructure(['plans', 'discountPercent'])
            ->assertJsonPath('plans.0.name', 'Basic')
            ->assertJsonPath('plans.0.highlighted', false)
            ->assertJsonPath('plans.1.highlighted', true);
    }

    public function test_unknown_api_route_returns_json_404(): void
    {
        $this->getJson('/api/does-not-exist')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_unknown_api_route_without_json_accept_returns_json_404(): void
    {
        $this->get('/api/does-not-exist')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/dashboard')->assertStatus(401);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonStructure(['stats' => ['courses', 'completedLessons', 'learningMinutes'], 'enrolledCourses']);
    }
}
