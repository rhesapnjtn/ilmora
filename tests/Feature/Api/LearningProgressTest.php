<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_rejected_from_lesson_route(): void
    {
        $course = Course::factory()->create(['published' => true]);
        $lesson = CourseLesson::factory()->create(['course_id' => $course->id, 'is_free' => false]);

        $this->getJson("/api/courses/{$course->slug}/lessons/{$lesson->slug}")
            ->assertUnauthorized();
    }

    public function test_free_lesson_is_accessible_without_subscription(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['published' => true]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => true,
            'slug' => 'intro',
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/intro")
            ->assertOk()
            ->assertJsonPath('lesson.free', true);
    }

    public function test_premium_lesson_returns_403_for_user_without_active_subscription(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['published' => true]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'premium-lesson',
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/premium-lesson")
            ->assertForbidden()
            ->assertJsonPath('requires_subscription', true);
    }

    public function test_premium_lesson_is_accessible_with_active_subscription(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['is_active' => true]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'expires_at' => Carbon::now()->addMonth(),
        ]);
        $course = Course::factory()->create(['published' => true]);
        $plan->courses()->attach($course->id);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'premium-lesson',
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/premium-lesson")
            ->assertOk()
            ->assertJsonPath('lesson.free', false);
    }

    public function test_expired_subscription_does_not_grant_premium_access(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['is_active' => true]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'expires_at' => Carbon::now()->subDay(),
        ]);
        $course = Course::factory()->create(['published' => true]);
        $plan->courses()->attach($course->id);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'premium-lesson',
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/premium-lesson")
            ->assertForbidden()
            ->assertJsonPath('requires_subscription', true);
    }

    public function test_admin_can_access_any_premium_lesson(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['published' => true]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'premium-lesson',
            'order' => 1,
        ]);

        $this->actingAs($admin)
            ->getJson("/api/courses/{$course->slug}/lessons/premium-lesson")
            ->assertOk();
    }

    public function test_accessing_a_lesson_auto_enrolls_the_user(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['published' => true]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => true,
            'slug' => 'intro',
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/intro")
            ->assertOk();

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_lesson_response_includes_completed_flags_and_real_progress(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['published' => true]);
        $lessonA = CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => true,
            'slug' => 'lesson-a',
            'order' => 1,
        ]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => true,
            'slug' => 'lesson-b',
            'order' => 2,
        ]);

        $user->lessonCompletions()->create([
            'course_lesson_id' => $lessonA->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/lesson-a")
            ->assertOk();

        $this->assertTrue($response->json('lesson.isCompleted'));
        $this->assertEquals(50, $response->json('progress'));
        $this->assertEquals(1, $response->json('completedCount'));
    }

    public function test_user_can_toggle_a_lesson_as_complete(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['is_active' => true]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'expires_at' => Carbon::now()->addMonth(),
        ]);
        $course = Course::factory()->create(['published' => true]);
        $plan->courses()->attach($course->id);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'only-lesson',
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->slug}/lessons/only-lesson/complete")
            ->assertOk()
            ->assertJsonPath('completed', true)
            ->assertJsonPath('progress', 100)
            ->assertJsonPath('courseCompleted', true);

        $this->assertDatabaseHas('course_lesson_completions', ['user_id' => $user->id]);
    }

    public function test_toggling_complete_twice_un_marks_the_lesson(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['is_active' => true]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'expires_at' => Carbon::now()->addMonth(),
        ]);
        $course = Course::factory()->create(['published' => true]);
        $plan->courses()->attach($course->id);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'only-lesson',
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->slug}/lessons/only-lesson/complete")
            ->assertJsonPath('completed', true);

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->slug}/lessons/only-lesson/complete")
            ->assertJsonPath('completed', false);

        $this->assertDatabaseMissing('course_lesson_completions', ['user_id' => $user->id]);
    }

    public function test_user_cannot_mark_premium_lesson_complete_without_subscription(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['published' => true]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'only-lesson',
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->slug}/lessons/only-lesson/complete")
            ->assertForbidden();
    }

    public function test_completing_all_lessons_marks_enrollment_as_completed(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create(['is_active' => true]);
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'expires_at' => Carbon::now()->addMonth(),
        ]);
        $course = Course::factory()->create(['published' => true]);
        $plan->courses()->attach($course->id);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'only-lesson',
            'order' => 1,
        ]);

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->slug}/lessons/only-lesson/complete")
            ->assertOk()
            ->assertJsonPath('courseCompleted', true);

        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();
        $this->assertNotNull($enrollment?->completed_at);
    }

    public function test_dashboard_returns_real_enrolled_courses_and_stats(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['published' => true]);
        $lesson = CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => true,
            'slug' => 'intro',
            'order' => 1,
        ]);

        $user->enrollments()->create(['course_id' => $course->id, 'enrolled_at' => now()]);
        $user->lessonCompletions()->create(['course_lesson_id' => $lesson->id, 'completed_at' => now()]);

        $response = $this->actingAs($user)
            ->getJson('/api/dashboard')
            ->assertOk();

        $this->assertCount(1, $response->json('activeCourses'));
        $this->assertEquals(100, $response->json('activeCourses.0.progress'));
        $this->assertEquals(1, $response->json('stats.completedLessons'));
    }

    public function test_dashboard_returns_empty_courses_for_new_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/dashboard')
            ->assertOk();

        $this->assertEmpty($response->json('activeCourses'));
        $this->assertEquals(0, $response->json('stats.courses'));
    }
}
