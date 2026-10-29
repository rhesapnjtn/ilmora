<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkin_awards_points_and_increments_streak(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/checkin')
            ->assertOk()
            ->assertJsonPath('alreadyCheckedIn', false)
            ->assertJsonPath('streak', 1);

        $this->actingAs($user)
            ->postJson('/api/checkin')
            ->assertOk()
            ->assertJsonPath('alreadyCheckedIn', true);

        $this->actingAs($user)
            ->getJson('/api/points')
            ->assertOk()
            ->assertJsonPath('currentStreak', 1);
    }

    public function test_points_balance_endpoint_requires_auth(): void
    {
        $this->getJson('/api/points')->assertUnauthorized();
        $this->postJson('/api/checkin')->assertUnauthorized();
    }

    public function test_completing_a_lesson_awards_points(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['published' => true]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => true,
            'slug' => 'intro',
            'order' => 1,
        ]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => true,
            'slug' => 'intro-2',
            'order' => 2,
        ]);

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->slug}/lessons/intro/complete")
            ->assertOk();

        $this->actingAs($user)
            ->getJson('/api/points')
            ->assertOk()
            ->assertJsonPath('balance', 2);
    }

    public function test_redeeming_a_course_deducts_points_and_grants_access(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create([
            'published' => true,
            'points_cost' => 5,
        ]);
        CourseLesson::factory()->create([
            'course_id' => $course->id,
            'is_free' => false,
            'slug' => 'premium-lesson',
            'order' => 1,
        ]);

        // give the user enough points via check-ins
        for ($i = 0; $i < 2; $i++) {
            $this->actingAs($user)->postJson('/api/checkin')->assertOk();
        }
        // backdate last checkin to allow a second day
        $user->forceFill(['last_checkin_at' => now()->subDay()->toDateString(), 'current_streak' => 0])->save();
        $this->actingAs($user)->postJson('/api/checkin')->assertOk();

        $balance = $this->actingAs($user)->getJson('/api/points')->json('balance');
        $this->assertGreaterThanOrEqual(5, $balance);

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->slug}/redeem")
            ->assertCreated();

        $this->actingAs($user)
            ->getJson("/api/courses/{$course->slug}/lessons/premium-lesson")
            ->assertOk();
    }

    public function test_redeem_twice_is_rejected(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create([
            'published' => true,
            'points_cost' => 1,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)->postJson('/api/checkin')->assertOk();
            $user->forceFill(['last_checkin_at' => now()->subDay()->toDateString(), 'current_streak' => 0])->save();
        }

        $this->actingAs($user)->postJson("/api/courses/{$course->slug}/redeem")->assertCreated();
        $this->actingAs($user)->postJson("/api/courses/{$course->slug}/redeem")->assertStatus(422);
    }

    public function test_redeem_fails_without_enough_points(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->create([
            'published' => true,
            'points_cost' => 1000,
        ]);

        $this->actingAs($user)
            ->postJson("/api/courses/{$course->slug}/redeem")
            ->assertStatus(422);
    }
}
