<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\CourseLesson;
use App\Models\LessonDiscussion;
use App\Models\User;
use App\Notifications\LessonReplyNotification;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
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

    public function test_guests_cannot_access_notifications(): void
    {
        $this->getJson('/api/notifications')->assertStatus(401);
    }

    public function test_reply_notifies_the_discussion_owner(): void
    {
        Notification::fake();

        [$owner, $replier, $discussion] = $this->thread();

        $this->withToken($replier->createToken('test')->plainTextToken)
            ->postJson($this->discussionUrl(), [
                'body' => 'Coba jalankan php artisan migrate terlebih dahulu.',
                'parent_id' => $discussion->id,
            ])
            ->assertCreated();

        Notification::assertSentTo($owner, LessonReplyNotification::class);
        Notification::assertNotSentTo($replier, LessonReplyNotification::class);
    }

    public function test_replying_to_own_discussion_does_not_notify(): void
    {
        Notification::fake();

        [$owner, , $discussion] = $this->thread();

        $this->withToken($owner->createToken('test')->plainTextToken)
            ->postJson($this->discussionUrl(), [
                'body' => 'Tambahan dari penulis diskusi.',
                'parent_id' => $discussion->id,
            ])
            ->assertCreated();

        Notification::assertNotSentTo($owner, LessonReplyNotification::class);
    }

    public function test_user_can_list_and_mark_a_notification_as_read(): void
    {
        [$owner, , $discussion, $reply] = $this->thread();
        $owner->notify(new LessonReplyNotification($reply, $discussion));

        $token = $owner->createToken('test')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('unreadCount', 1)
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.data.type', 'lesson_reply');

        $id = $response->json('notifications.0.id');

        $this->withToken($token)
            ->postJson('/api/notifications/'.$id.'/read')
            ->assertOk()
            ->assertJsonPath('unreadCount', 0);

        $this->assertSame(0, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        [$owner, $replier, $discussion, $reply] = $this->thread();

        $second = LessonDiscussion::create([
            'lesson_id' => $discussion->lesson_id,
            'user_id' => $replier->id,
            'parent_id' => $discussion->id,
            'body' => 'Balasan kedua.',
        ]);

        $owner->notify(new LessonReplyNotification($reply, $discussion));
        $owner->notify(new LessonReplyNotification($second, $discussion));

        $this->assertSame(2, $owner->fresh()->unreadNotifications()->count());

        $this->withToken($owner->createToken('test')->plainTextToken)
            ->postJson('/api/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('unreadCount', 0);

        $this->assertSame(0, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_user_cannot_read_another_users_notification(): void
    {
        [$owner, $replier, $discussion, $reply] = $this->thread();
        $owner->notify(new LessonReplyNotification($reply, $discussion));

        $id = $owner->notifications()->first()->id;

        $this->withToken($replier->createToken('test')->plainTextToken)
            ->postJson('/api/notifications/'.$id.'/read')
            ->assertStatus(404);
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
}
