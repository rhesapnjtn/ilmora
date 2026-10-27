<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtendedFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_categories_endpoint_returns_list(): void
    {
        $this->getJson('/api/categories')->assertOk()->assertJsonStructure(['categories']);
    }

    public function test_admin_can_crud_categories(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test')->plainTextToken;

        $created = $this->withToken($token)->postJson('/api/admin/categories', [
            'name' => 'AI Engineering',
        ])->assertCreated()->json('category');

        $this->withToken($token)->getJson('/api/admin/categories')->assertOk();
        $this->withToken($token)->putJson("/api/admin/categories/{$created['id']}", ['name' => 'AI Eng'])->assertOk();
        $this->withToken($token)->deleteJson("/api/admin/categories/{$created['id']}")->assertOk();
    }

    public function test_lesson_note_can_be_saved_and_loaded(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->putJson('/api/courses/laravel-vue-3-full-stack/lessons/1-1/note', [
            'content' => 'Catatan saya',
        ])->assertOk();

        $this->withToken($token)->getJson('/api/courses/laravel-vue-3-full-stack/lessons/1-1/note')
            ->assertOk()
            ->assertJsonPath('note', 'Catatan saya');
    }

    public function test_wishlist_toggle(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/courses/laravel-vue-3-full-stack/wishlist')
            ->assertOk()->assertJsonPath('wishlisted', true);

        $this->withToken($token)->getJson('/api/wishlist')->assertOk();

        $this->withToken($token)->deleteJson('/api/courses/laravel-vue-3-full-stack/wishlist')
            ->assertOk()->assertJsonPath('wishlisted', false);
    }

    public function test_course_review_can_be_created(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/courses/laravel-vue-3-full-stack/reviews', [
            'rating' => 5,
            'body' => 'Mantap',
        ])->assertStatus(201);

        $this->getJson('/api/courses/laravel-vue-3-full-stack/reviews')
            ->assertOk()
            ->assertJsonStructure(['reviews', 'average', 'count']);
    }

    public function test_certificate_verify_404_for_unknown(): void
    {
        $this->getJson('/api/certificates/verify/unknown-code')->assertStatus(404);
    }

    public function test_certificate_issued_on_course_completion(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $course = Course::factory()->create(['slug' => 'mini-course', 'published' => true]);
        $lesson = CourseLesson::factory()->create(['course_id' => $course->id, 'slug' => 'l1', 'is_free' => true, 'order' => 1]);

        $this->withToken($token)->postJson('/api/courses/mini-course/lessons/l1/complete')
            ->assertOk()
            ->assertJsonPath('courseCompleted', true);

        $this->withToken($token)->getJson('/api/certificates')
            ->assertOk()
            ->assertJsonCount(1, 'certificates');
    }
}
