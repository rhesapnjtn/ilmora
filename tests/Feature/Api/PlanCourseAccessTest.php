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

class PlanCourseAccessTest extends TestCase
{
    use RefreshDatabase;

    private function courseWithPremiumLesson(string $slug): Course
    {
        $course = Course::factory()->create(['slug' => $slug, 'published' => true]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'advanced-lesson',
            'order' => 1,
        ]);

        return $course;
    }

    private function subscribe(User $user, Plan $plan, array $courseIds = [], array $extra = []): Subscription
    {
        if ($courseIds !== []) {
            $plan->courses()->sync($courseIds);
        }

        return Subscription::factory()->create(array_merge([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'expires_at' => Carbon::now()->addMonth(),
        ], $extra));
    }

    public function test_basic_plan_grants_access_to_its_own_courses(): void
    {
        $basicCourse = $this->courseWithPremiumLesson('basic-course');
        $premiumCourse = $this->courseWithPremiumLesson('premium-course');

        $user = User::factory()->create();
        $plan = Plan::factory()->create(['slug' => 'basic']);
        $this->subscribe($user, $plan, [$basicCourse->id]);

        $this->actingAs($user)
            ->getJson("/api/courses/{$basicCourse->slug}/lessons/advanced-lesson")
            ->assertOk();

        $this->actingAs($user)
            ->getJson("/api/courses/{$premiumCourse->slug}/lessons/advanced-lesson")
            ->assertForbidden()
            ->assertJsonPath('requires_subscription', true)
            ->assertJsonPath('requires_upgrade', true);
    }

    public function test_user_without_subscription_is_told_to_subscribe_not_upgrade(): void
    {
        $course = $this->courseWithPremiumLesson('locked-course');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/advanced-lesson")
            ->assertForbidden()
            ->assertJsonPath('requires_subscription', true)
            ->assertJsonPath('requires_upgrade', false);
    }

    public function test_premium_plan_grants_access_to_every_assigned_course(): void
    {
        $courseA = $this->courseWithPremiumLesson('course-a');
        $courseB = $this->courseWithPremiumLesson('course-b');

        $user = User::factory()->create();
        $plan = Plan::factory()->create(['slug' => 'premium']);
        $this->subscribe($user, $plan, [$courseA->id, $courseB->id]);

        $this->actingAs($user)
            ->getJson("/api/courses/{$courseA->slug}/lessons/advanced-lesson")
            ->assertOk();

        $this->actingAs($user)
            ->getJson("/api/courses/{$courseB->slug}/lessons/advanced-lesson")
            ->assertOk();
    }

    public function test_plan_without_courses_grants_no_premium_access(): void
    {
        $course = $this->courseWithPremiumLesson('orphan-course');

        $user = User::factory()->create();
        $plan = Plan::factory()->create(['is_active' => true]);
        $this->subscribe($user, $plan, []);

        $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/advanced-lesson")
            ->assertForbidden()
            ->assertJsonPath('requires_subscription', true);
    }

    public function test_expired_subscription_does_not_grant_access_even_when_plan_has_the_course(): void
    {
        $course = $this->courseWithPremiumLesson('expired-course');

        $user = User::factory()->create();
        $plan = Plan::factory()->create(['is_active' => true]);
        $this->subscribe($user, $plan, [$course->id], [
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/advanced-lesson")
            ->assertForbidden()
            ->assertJsonPath('requires_subscription', true);
    }

    public function test_admin_can_access_any_premium_lesson_regardless_of_plan(): void
    {
        $course = $this->courseWithPremiumLesson('admin-course');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->getJson("/api/courses/{$course->slug}/lessons/advanced-lesson")
            ->assertOk();
    }

    public function test_lesson_completion_is_blocked_when_plan_does_not_cover_the_course(): void
    {
        $course = $this->courseWithPremiumLesson('blocked-course');

        $user = User::factory()->create();
        $plan = Plan::factory()->create(['is_active' => true]);
        $this->subscribe($user, $plan, []);

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->slug}/lessons/advanced-lesson/complete")
            ->assertForbidden();
    }

    public function test_public_plan_payload_lists_included_courses(): void
    {
        $course = Course::factory()->create(['slug' => 'listed-course', 'title' => 'Listed Course', 'level' => 'Pemula', 'published' => true]);
        $plan = Plan::factory()->create(['slug' => 'listed-plan', 'is_active' => true]);
        $plan->courses()->sync([$course->id]);

        $this->getJson("/api/plans/{$plan->slug}")
            ->assertOk()
            ->assertJsonPath('plan.courses.0.slug', 'listed-course')
            ->assertJsonPath('plan.courses.0.title', 'Listed Course')
            ->assertJsonPath('plan.courses.0.level', 'Pemula');
    }

    public function test_plan_without_courses_returns_an_empty_course_list(): void
    {
        $plan = Plan::factory()->create(['slug' => 'empty-plan', 'is_active' => true]);

        $this->getJson("/api/plans/{$plan->slug}")
            ->assertOk()
            ->assertJsonPath('plan.courses', []);
    }

    public function test_admin_can_assign_courses_to_a_plan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['published' => true]);
        $plan = Plan::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->putJson("/api/admin/plans/{$plan->id}", [
                'name' => $plan->name,
                'monthly' => $plan->monthly,
                'annual' => $plan->annual,
                'is_active' => true,
                'course_ids' => [$course->id],
            ])
            ->assertOk()
            ->assertJsonPath('plan.courseIds', [$course->id]);

        $updatedSlug = $this->getJson("/api/admin/plans/{$plan->id}")
            ->json('plan.slug');

        $this->getJson("/api/plans/{$updatedSlug}")
            ->assertOk()
            ->assertJsonPath('plan.courses.0.slug', $course->slug);
    }

    public function test_admin_can_create_a_plan_with_courses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['published' => true]);

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/plans', [
                'name' => 'Paket Uji Tiers',
                'monthly' => 100000,
                'annual' => 1000000,
                'is_active' => true,
                'course_ids' => [$course->id],
            ])
            ->assertCreated()
            ->assertJsonPath('plan.courseIds', [$course->id]);

        $createdSlug = $response->json('plan.slug');

        $this->getJson("/api/plans/{$createdSlug}")
            ->assertOk()
            ->assertJsonPath('plan.courses.0.slug', $course->slug);
    }
}
