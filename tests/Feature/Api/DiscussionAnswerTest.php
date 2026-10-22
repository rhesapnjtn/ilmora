<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\CourseLesson;
use App\Models\LessonDiscussion;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DiscussionAnswerTest extends TestCase
{
    use RefreshDatabase;

    private const COURSE = 'laravel-vue-3-full-stack';

    private const LESSON = '2-1';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(CourseSeeder::class);
    }

    public function test_discussion_owner_can_mark_a_reply_as_best_answer(): void
    {
        [$owner, $replier, $discussion, $reply] = $this->thread();

        $this->withToken($owner->createToken('test')->plainTextToken)
            ->postJson($this->acceptUrl($reply))
            ->assertOk()
            ->assertJsonPath('discussion.isAnswer', true);

        $this->assertTrue($reply->fresh()->is_answer);

        $this->withToken($owner->createToken('test')->plainTextToken)
            ->getJson($this->discussionUrl())
            ->assertOk()
            ->assertJsonPath('discussions.0.hasAcceptedAnswer', true)
            ->assertJsonPath('discussions.0.replies.0.isAnswer', true);
    }

    public function test_only_one_reply_can_be_marked_as_best_answer(): void
    {
        [$owner, $replier, $discussion] = $this->thread();

        $first = LessonDiscussion::create([
            'lesson_id' => $discussion->lesson_id,
            'user_id' => $replier->id,
            'parent_id' => $discussion->id,
            'body' => 'Jawaban pertama',
        ]);

        $second = LessonDiscussion::create([
            'lesson_id' => $discussion->lesson_id,
            'user_id' => $replier->id,
            'parent_id' => $discussion->id,
            'body' => 'Jawaban kedua',
        ]);

        $token = $owner->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson($this->acceptUrl($first))->assertOk();
        $this->withToken($token)->postJson($this->acceptUrl($second))->assertOk();

        $this->assertFalse($first->fresh()->is_answer);
        $this->assertTrue($second->fresh()->is_answer);
    }

    public function test_reply_author_cannot_mark_their_own_reply_as_best_answer(): void
    {
        [, $replier, $discussion, $reply] = $this->thread();

        $this->withToken($replier->createToken('test')->plainTextToken)
            ->postJson($this->acceptUrl($reply))
            ->assertStatus(403);

        $this->assertFalse($reply->fresh()->is_answer);
    }

    public function test_admin_can_mark_a_reply_as_best_answer(): void
    {
        [, , , $reply] = $this->thread();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson($this->acceptUrl($reply))
            ->assertOk();

        $this->assertTrue($reply->fresh()->is_answer);
    }

    public function test_top_level_discussion_cannot_be_marked_as_best_answer(): void
    {
        [$owner, , $discussion] = $this->thread();

        $this->withToken($owner->createToken('test')->plainTextToken)
            ->postJson($this->acceptUrl($discussion))
            ->assertStatus(422);
    }

    public function test_discussion_owner_can_remove_the_best_answer_mark(): void
    {
        [$owner, , , $reply] = $this->thread();
        $token = $owner->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson($this->acceptUrl($reply))->assertOk();
        $this->assertTrue($reply->fresh()->is_answer);

        $this->withToken($token)->deleteJson($this->acceptUrl($reply))->assertOk();
        $this->assertFalse($reply->fresh()->is_answer);
    }

    /**
     * @return array{0: User, 1: User, 2: LessonDiscussion, 3: LessonDiscussion}
     */
    private function thread(): array
    {
        $owner = User::factory()->create();
        $replier = User::factory()->create();
        $lesson = CourseLesson::query()->where('slug', self::LESSON)->firstOrFail();

        $discussion = LessonDiscussion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $owner->id,
            'body' => 'Bagaimana cara kerja middleware?',
        ]);

        $reply = LessonDiscussion::create([
            'lesson_id' => $lesson->id,
            'user_id' => $replier->id,
            'parent_id' => $discussion->id,
            'body' => 'Middleware dijalankan sebelum request mencapai controller.',
        ]);

        return [$owner, $replier, $discussion, $reply];
    }

    private function discussionUrl(): string
    {
        return '/api/courses/'.self::COURSE.'/lessons/'.self::LESSON.'/discussions';
    }

    private function acceptUrl(LessonDiscussion $discussion): string
    {
        return $this->discussionUrl().'/'.$discussion->id.'/accept';
    }
}
