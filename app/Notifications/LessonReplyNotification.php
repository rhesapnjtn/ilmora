<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\LessonDiscussion;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class LessonReplyNotification extends Notification
{
    public function __construct(
        private readonly LessonDiscussion $reply,
        private readonly LessonDiscussion $discussion,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->discussion->loadMissing('lesson.course');
        $this->reply->loadMissing('user');

        $lesson = $this->discussion->lesson;
        $course = $lesson?->course;

        return [
            'type' => 'lesson_reply',
            'reply_id' => $this->reply->id,
            'discussion_id' => $this->discussion->id,
            'lesson_title' => $lesson?->title,
            'course_slug' => $course?->slug,
            'lesson_slug' => $lesson?->slug,
            'author_name' => $this->reply->user?->name,
            'excerpt' => Str::limit($this->reply->body, 140),
            'url' => $course !== null && $lesson !== null
                ? '/belajar/'.$course->slug.'/'.$lesson->slug.'?tab=diskusi'
                : null,
        ];
    }
}
