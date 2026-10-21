<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\CourseLesson;
use App\Models\LessonDiscussion;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscussionTest extends TestCase
{
    use RefreshDatabase;

    private const COURSE = 'laravel-vue-3-full-stack';

    private const LESSON = '2-1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CourseSeeder::class);
    }

    public function test_guests_cannot_access_discussions(): void
    {
        $this->getJson('/api/courses/'.self::COURSE.'/lessons/'.self::LESSON.'/discussions')
            ->assertStatus(401);
    }

    public function test_authenticated_user_can_list_discussions(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/courses/'.self::COURSE.'/lessons/'.self::LESSON.'/discussions')
            ->assertOk()
            ->assertJsonStructure(['discussions'])
            ->assertJsonCount(0, 'discussions');
    }

    public function test_authenticated_user_can_create_a_discussion(): void
    {
        $user = User::factory()->create();
        $lesson = CourseLesson::query()->where('slug', self::LESSON)->firstOrFail();

        $response = $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/courses/'.self::COURSE.'/lessons/'.self::LESSON.'/discussions', [
                'body' => 'Bagaimana cara mengatasi error saat migrasi?',
            ])
            ->assertCreated();

        $response->assertJsonStructure([
            'message',
            'discussion' => ['id', 'body', 'createdAt', 'user', 'canUpdate', 'canDelete'],
        ]);

        $this->assertDatabaseHas('lesson_discussions', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'body' => 'Bagaimana cara mengatasi error saat migrasi?',
        ]);
    }

    public function test_discussion_body_is_required_and_limited(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $url = '/api/courses/'.self::COURSE.'/lessons/'.self::LESSON.'/discussions';

        $this->withToken($token)->postJson($url, ['body' => '   '])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');

        $this->withToken($token)->postJson($url, ['body' => str_repeat('a', 5001)])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');
    }

    public function test_unknown_lesson_returns_404(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/courses/'.self::COURSE.'/lessons/tidak-ada/discussions')
            ->assertStatus(404);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson('/api/courses/'.self::COURSE.'/lessons/tidak-ada/discussions', ['body' => 'Halo'])
            ->assertStatus(404);
    }

    public function test_owner_can_update_and_delete_their_discussion(): void
    {
        $user = User::factory()->create();
        $lesson = CourseLesson::query()->where('slug', self::LESSON)->firstOrFail();

        $discussion = LessonDiscussion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'body' => 'Pertanyaan awal',
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->putJson('/api/courses/'.self::COURSE.'/lessons/'.self::LESSON."/discussions/{$discussion->id}", [
                'body' => 'Pertanyaan yang sudah diperbarui',
            ])
            ->assertOk()
            ->assertJsonPath('discussion.body', 'Pertanyaan yang sudah diperbarui');

        $this->assertDatabaseHas('lesson_discussions', [
            'id' => $discussion->id,
            'body' => 'Pertanyaan yang sudah diperbarui',
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->deleteJson('/api/courses/'.self::COURSE.'/lessons/'.self::LESSON."/discussions/{$discussion->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Diskusi berhasil dihapus.');

        $this->assertDatabaseMissing('lesson_discussions', ['id' => $discussion->id]);
    }

    public function test_user_cannot_update_or_delete_another_users_discussion(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $lesson = CourseLesson::query()->where('slug', self::LESSON)->firstOrFail();

        $discussion = LessonDiscussion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $owner->id,
            'body' => 'Diskusi milik orang lain',
        ]);

        $this->withToken($other->createToken('test')->plainTextToken)
            ->putJson('/api/courses/'.self::COURSE.'/lessons/'.self::LESSON."/discussions/{$discussion->id}", [
                'body' => 'Coba ubah',
            ])
            ->assertStatus(403);

        $this->withToken($other->createToken('test')->plainTextToken)
            ->deleteJson('/api/courses/'.self::COURSE.'/lessons/'.self::LESSON."/discussions/{$discussion->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('lesson_discussions', [
            'id' => $discussion->id,
            'body' => 'Diskusi milik orang lain',
        ]);
    }

    public function test_admin_can_moderate_another_users_discussion(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $lesson = CourseLesson::query()->where('slug', self::LESSON)->firstOrFail();

        $discussion = LessonDiscussion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $owner->id,
            'body' => 'Diskusi yang melanggar',
        ]);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->deleteJson('/api/courses/'.self::COURSE.'/lessons/'.self::LESSON."/discussions/{$discussion->id}")
            ->assertOk();

        $this->assertDatabaseMissing('lesson_discussions', ['id' => $discussion->id]);
    }

    public function test_discussion_from_another_lesson_is_not_accessible(): void
    {
        $user = User::factory()->create();
        $lesson = CourseLesson::query()->where('slug', self::LESSON)->firstOrFail();

        $discussion = LessonDiscussion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'body' => 'Diskusi pelajaran 2-1',
        ]);

        $this->withToken($user->createToken('test')->plainTextToken)
            ->putJson('/api/courses/'.self::COURSE."/lessons/3-1/discussions/{$discussion->id}", [
                'body' => 'Coba akses dari pelajaran lain',
            ])
            ->assertStatus(404);

        $this->assertDatabaseHas('lesson_discussions', ['id' => $discussion->id]);
    }

    public function test_discussions_are_listed_newest_first(): void
    {
        $user = User::factory()->create();
        $lesson = CourseLesson::query()->where('slug', self::LESSON)->firstOrFail();

        $first = LessonDiscussion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'body' => 'Diskusi pertama',
        ]);
        $first->forceFill(['created_at' => now()->subMinutes(10)])->save();

        $second = LessonDiscussion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'body' => 'Diskusi kedua',
        ]);
        $second->forceFill(['created_at' => now()])->save();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/courses/'.self::COURSE.'/lessons/'.self::LESSON.'/discussions')
            ->assertOk()
            ->assertJsonPath('discussions.0.id', $second->id)
            ->assertJsonPath('discussions.1.id', $first->id);
    }
}
